<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;


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
        // Force HTTPS if we are using ngrok
        // Force HTTPS if we are using ngrok (handling both .app and .dev domains)
        if (
            (request()->hasHeader('X-Forwarded-Host') && (
                str_contains(request()->header('X-Forwarded-Host'), 'ngrok-free.app') || 
                str_contains(request()->header('X-Forwarded-Host'), 'ngrok-free.dev')
            )) ||
            (request()->hasHeader('X-Forwarded-Proto') && request()->header('X-Forwarded-Proto') === 'https')
        ) {
            URL::forceScheme('https');
        }
    }
}
