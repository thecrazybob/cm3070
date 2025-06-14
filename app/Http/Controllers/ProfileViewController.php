<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ProfileRetrievalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileViewController extends Controller
{
    protected $profileRetrievalService;

    public function __construct(ProfileRetrievalService $profileRetrievalService)
    {
        $this->profileRetrievalService = $profileRetrievalService;
    }

    public function show(Request $request, User $user): JsonResponse
    {
        $contextSlug = $request->query('context');

        $profileData = $this->profileRetrievalService->getProfile($user, $contextSlug);

        return response()->json($profileData);
    }
}
