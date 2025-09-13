<?php

declare(strict_types=1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContextController;
use App\Http\Controllers\GDPRController;
use App\Http\Controllers\ProfileViewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Authentication routes with rate limiting
Route::middleware(['throttle:auth'])->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Protected authentication routes (supports both session and sanctum)
Route::middleware(['auth:sanctum,web'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Context Management
    Route::get('/contexts', [ContextController::class, 'index']);
    Route::post('/contexts', [ContextController::class, 'store']);
    Route::get('/contexts/{contextId}', [ContextController::class, 'show'])->whereNumber('contextId');
    Route::put('/contexts/{contextId}', [ContextController::class, 'update'])->whereNumber('contextId');
    Route::delete('/contexts/{contextId}', [ContextController::class, 'destroy'])->whereNumber('contextId');
    Route::post('/contexts/{contextId}/set-default', [ContextController::class, 'setDefault'])->whereNumber('contextId');

    // Context Attributes Management
    Route::get('/contexts/{contextId}/attributes', [ContextController::class, 'getAttributes'])->whereNumber('contextId');
    Route::post('/contexts/{contextId}/attributes', [ContextController::class, 'storeAttribute'])->whereNumber('contextId');
    Route::put('/contexts/{contextId}/attributes/{attributeId}', [ContextController::class, 'updateAttribute'])->whereNumber('contextId')->whereNumber('attributeId');
    Route::delete('/contexts/{contextId}/attributes/{attributeId}', [ContextController::class, 'destroyAttribute'])->whereNumber('contextId')->whereNumber('attributeId');

    // GDPR Compliance with specific rate limiting
    Route::middleware(['throttle:gdpr'])->group(function () {
        Route::get('/export-data', [GDPRController::class, 'exportData']);
        Route::delete('/delete-account', [GDPRController::class, 'deleteAccount']);
    });

    Route::get('/audit-log', [GDPRController::class, 'getAuditLog']);
    Route::get('/gdpr-info', [GDPRController::class, 'getGDPRInfo']);
});

// Profile viewing (supports both authenticated and unauthenticated access)
Route::get('/view/profile/{user}', [ProfileViewController::class, 'show']);
