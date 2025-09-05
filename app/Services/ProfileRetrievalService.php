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
            // Check if requester has any special access rules (simplified version)
            $hasSpecialAccess = $this->checkSpecialAccess($user, $context, $requester);
            
            if ($hasSpecialAccess) {
                // If user has special access, show public and protected attributes
                return $query->whereIn('visibility', ['public', 'protected'])->get();
            }
            
            // Check if this is a private context
            if ($this->isPrivateContext($context)) {
                // For private contexts, return empty result for non-owners
                return collect([]);
            }

            // For authenticated users without special access, show public and protected
            return $query->whereIn('visibility', ['public', 'protected'])->get();
        }

        // Unauthenticated/public access - only public attributes
        return $query->where('visibility', 'public')->get();
    }
    
    /**
     * Check if requester has special access permissions
     */
    private function checkSpecialAccess(User $user, Context $context, User $requester): bool
    {
        // Check if users share an organization or domain
        if ($this->sharesSameDomain($user, $requester)) {
            return true;
        }
        
        // Future: Check access_rules table for specific permissions
        // Currently simplified to domain-based access
        return false;
    }
    
    /**
     * Check if two users share the same email domain
     */
    private function sharesSameDomain(User $user, User $requester): bool
    {
        $userDomain = substr($user->email, strpos($user->email, '@') + 1);
        $requesterDomain = substr($requester->email, strpos($requester->email, '@') + 1);
        
        // Check if both are from same educational/organizational domain
        $educationalDomains = ['ac.uk', 'edu', 'edu.au', 'edu.in'];
        foreach ($educationalDomains as $eduDomain) {
            if (str_ends_with($userDomain, $eduDomain) && $userDomain === $requesterDomain) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if context is marked as private
     */
    private function isPrivateContext(Context $context): bool
    {
        // Specific private contexts
        $privateContexts = ['private', 'confidential', 'internal'];
        if (in_array($context->slug, $privateContexts)) {
            return true;
        }
        
        // Check if all attributes in context are private
        return $context->profileValues()
            ->where('visibility', '!=', 'private')
            ->doesntExist();
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
