<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ProfileRetrievalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;

class ProfileViewController extends Controller
{
    protected $profileRetrievalService;

    public function __construct(ProfileRetrievalService $profileRetrievalService)
    {
        $this->profileRetrievalService = $profileRetrievalService;
    }

    public function show(Request $request, User $user): JsonResponse
    {
        // Handle optional authentication manually
        $authHeader = $request->header('Authorization');
        if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                Auth::setUser($accessToken->tokenable);
            }
        }

        $contextSlug = $request->query('context');

        $profileData = $this->profileRetrievalService->getProfile($user, $contextSlug);

        return response()->json($profileData);
    }
}
