<?php

use App\Http\Controllers\ProfileViewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/view/profile/{user}', [ProfileViewController::class, 'show']);
