<?php

namespace App\Providers;

use App\Models\Context;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });

        // Configure rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        
        // Rate limiting for authentication endpoints
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
        
        // Rate limiting for GDPR data requests
        RateLimiter::for('gdpr', function (Request $request) {
            return Limit::perHour(5)->by($request->user()?->id ?: $request->ip());
        });

        // Custom route model binding for Context
        Route::bind('context', function ($value) {
            // If we're in an authenticated API context, only return contexts owned by the user
            if (auth()->check()) {
                $context = Context::where('id', $value)
                    ->where('user_id', auth()->id())
                    ->first();

                // Return null if not found, let the controller handle the 404
                return $context ?: null;
            }

            // For unauthenticated requests, return the context normally
            return Context::find($value);
        });
    }
}
