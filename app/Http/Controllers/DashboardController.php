<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the dashboard for authenticated users
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        
        // Ensure user has an API token for dashboard API calls
        if (!$request->session()->has('api_token')) {
            $token = $user->createToken('web-session-token')->plainTextToken;
            $request->session()->put('api_token', $token);
        }

        // Get paginated contexts
        $contexts = $user->contexts()
            ->withCount('profileValues')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('custom-dashboard', [
            'user' => $user,
            'contexts' => $contexts->items(),
            'contextsPagination' => [
                'current_page' => $contexts->currentPage(),
                'last_page' => $contexts->lastPage(),
                'per_page' => $contexts->perPage(),
                'total' => $contexts->total(),
                'from' => $contexts->firstItem(),
                'to' => $contexts->lastItem(),
            ],
        ]);
    }
}
