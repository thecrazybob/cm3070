<?php

use App\Http\Controllers\ApiDemoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GDPRController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');
Route::get('/api-demo', [ApiDemoController::class, 'index'])->middleware(['auth', 'verified'])->name('api-demo');
Route::get('/gdpr-controls', [GDPRController::class, 'index'])->middleware(['auth', 'verified'])->name('gdpr-controls');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
