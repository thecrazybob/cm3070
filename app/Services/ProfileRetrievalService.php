<?php

namespace App\Services;

use App\Models\AccessLog;
use App\Models\Context;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileRetrievalService
{
    /**
     * Retrieves a user's profile based on the given context and requester.
     *
     * @param  User  $user  The user whose profile is being requested.
     * @param  string|null  $contextSlug  The slug of the context to retrieve.
     * @return array The filtered profile data or an error response.
     */
    public function getProfile(User $user, ?string $contextSlug): array
    {
        $requester = Auth::user();

        $context = $this->findContext($user, $contextSlug);

        if (! $context) {
            // Return a graceful error response instead of throwing an exception
            $this->logAccessAttempt($user, $contextSlug, $requester, 'context_not_found');

            return [
                'error' => true,
                'message' => $contextSlug
                    ? "The requested context '{$contextSlug}' does not exist for this user or is not accessible."
                    : 'This user has no default context configured.',
                'available_contexts' => $this->getAvailableContexts($user, $requester),
                'requested_context' => $contextSlug,
                'user_id' => $user->id,
            ];
        }

        $isOwner = $requester && $requester->id === $user->id;

        $profileValues = $this->getProfileValues($user, $context, $isOwner);

        $this->logAccess($user, $context, $requester, $profileValues);

        return $this->formatProfileData($profileValues);
    }

    private function findContext(User $user, ?string $contextSlug): ?Context
    {
        if ($contextSlug) {
            return $user->contexts()->where('slug', $contextSlug)->first();
        }

        return $user->contexts()->where('is_default', true)->first();
    }

    private function getProfileValues(User $user, Context $context, bool $isOwner)
    {
        $query = DB::table('context_profile_values')
            ->join('profile_attributes', 'context_profile_values.attribute_id', '=', 'profile_attributes.id')
            ->where('context_profile_values.user_id', $user->id)
            ->where('context_profile_values.context_id', $context->id)
            ->select('profile_attributes.key_name', 'context_profile_values.value', 'context_profile_values.visibility');

        if ($isOwner) {
            return $query->get();
        }

        // Check access rules for authenticated users
        $requester = Auth::user();
        if ($requester) {
            // Check if this is a private/university context - if so, only owner can access
            if (in_array($context->slug, ['university', 'formal']) ||
                $context->profileValues()->where('visibility', 'private')->exists()) {
                // For private contexts, return empty result instead of throwing exception
                return collect([]);
            }

            // For other contexts, authenticated users can see public attributes only
            // (In a full implementation, this would check the access_rules table for specific permissions)
            return $query->where('visibility', 'public')->get();
        }

        // Unauthenticated/public access - only public attributes
        return $query->where('visibility', 'public')->get();
    }

    private function formatProfileData($profileValues): array
    {
        $profile = [];
        foreach ($profileValues as $value) {
            $profile[$value->key_name] = $value->value;
        }

        return $profile;
    }

    private function logAccess(User $user, Context $context, ?User $requester, $profileValues): void
    {
        AccessLog::create([
            'user_id' => $user->id,
            'accessor_type' => $requester ? 'user' : 'anonymous',
            'accessor_id' => $requester ? $requester->id : null,
            'context_requested' => $context->slug,
            'attributes_returned' => $profileValues->pluck('key_name'),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'response_code' => 200,
        ]);
    }

    private function logAccessAttempt(User $user, ?string $contextSlug, ?User $requester, string $result): void
    {
        AccessLog::create([
            'user_id' => $user->id,
            'accessor_type' => $requester ? 'user' : 'anonymous',
            'accessor_id' => $requester ? $requester->id : null,
            'context_requested' => $contextSlug,
            'attributes_returned' => json_encode(['error' => $result]),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'response_code' => 404,
        ]);
    }

    private function getAvailableContexts(User $user, ?User $requester): array
    {
        $isOwner = $requester && $requester->id === $user->id;

        if ($isOwner) {
            // Owner can see all their contexts
            return $user->contexts()
                ->where('is_active', true)
                ->pluck('slug')
                ->toArray();
        }

        // Non-owners can only see public contexts (simplified logic)
        return $user->contexts()
            ->where('is_active', true)
            ->whereHas('profileValues', function ($query) {
                $query->where('visibility', 'public');
            })
            ->pluck('slug')
            ->toArray();
    }
}
