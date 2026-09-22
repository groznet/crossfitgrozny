<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Keyed by phone (not just IP) so someone can't spam a victim's
        // phone with OTP codes by rotating IP addresses.
        RateLimiter::for('otp-send', function (Request $request) {
            $phone = preg_replace('/\D/', '', (string) $request->input('phone'));
            $key = $phone !== '' ? "phone:{$phone}" : 'ip:'.$request->ip();

            return Limit::perMinute(3)->by($key);
        });
    }
}
