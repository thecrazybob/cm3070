<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class ApiDemoController extends Controller
{
    /**
     * Show the API demo page
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Ensure user has an API token for dashboard API calls
        if (! $request->session()->has('api_token')) {
            $token = $user->createToken('web-session-token')->plainTextToken;
            $request->session()->put('api_token', $token);
        }

        return view('api-demo', [
            'user' => $user,
        ]);
    }
}
