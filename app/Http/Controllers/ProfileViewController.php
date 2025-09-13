<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ProfileRetrievalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

final class ProfileViewController extends Controller
{
    public function __construct(private readonly ProfileRetrievalService $profileRetrievalService) {}

    /**
     * Retrieve a user's profile with context-aware access control
     *
     * This endpoint demonstrates the core feature of the Identity Management API:
     * dynamic context-based profile retrieval with requester-aware access control.
     * The API returns different profile information based on:
     * - Who is making the request (owner, authenticated user, or public)
     * - What context is being requested
     * - The visibility settings of profile attributes
     *
     * @param  Request  $request  The HTTP request containing optional context parameter
     * @param  User  $user  The user whose profile is being requested
     * @return JsonResponse Returns profile data based on access permissions
     *
     * @response 200 {
     *   "full_name": "Arda Yılmaz",
     *   "email": "arda@university.com",
     *   "student_id": "12345678"
     * }
     * @response 403 {"message": "You do not have permission to view this profile."}
     * @response 404 {"message": "Context not found."}
     */
    public function show(Request $request, User $user): JsonResponse
    {
        // Handle optional authentication manually
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = mb_substr($authHeader, 7);
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken && $accessToken->tokenable instanceof \Illuminate\Contracts\Auth\Authenticatable) {
                Auth::setUser($accessToken->tokenable);
            }
        }

        $contextSlug = $request->query('context');

        $profileData = $this->profileRetrievalService->getProfile($user, $contextSlug);

        return response()->json($profileData);
    }
}
