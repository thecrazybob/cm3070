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
        
        return view('custom-dashboard', [
            'user' => $user,
            'contexts' => $user->contexts()->with('profileValues.attribute')->get(),
        ]);
    }
}