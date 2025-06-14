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
     * @param User $user The user whose profile is being requested.
     * @param string|null $contextSlug The slug of the context to retrieve.
     * @return array The filtered profile data.
     */
    public function getProfile(User $user, ?string $contextSlug): array
    {
        $requester = Auth::user();

        $context = $this->findContext($user, $contextSlug);

        if (!$context) {
            abort(Response::HTTP_NOT_FOUND, 'Context not found.');
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

        // For now, only public access is handled besides ownership.
        // Later, this is where we would check the access_rules table for the requester.
        $requester = Auth::user();
        if ($requester) {
            // Authenticated user trying to access someone else's profile.
            // For the prototype, this is forbidden unless a specific rule exists.
            // We will implement rule checking later.
            abort(Response::HTTP_FORBIDDEN, 'You do not have permission to view this profile.');
        }

        // Unauthenticated/public access
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
            'response_code' => http_response_code(),
        ]);
    }
}
