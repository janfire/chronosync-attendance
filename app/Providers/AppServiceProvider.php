<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Event;
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
        // Register Microsoft Azure Socialite Provider
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('azure', \SocialiteProviders\Azure\Provider::class);
        });

        // Force HTTPS in production or if behind a proxy
        if (
            config('app.env') === 'production' ||
            (request()->hasHeader('X-Forwarded-Host') && (
                str_contains(request()->header('X-Forwarded-Host'), 'ngrok-free.app') || 
                str_contains(request()->header('X-Forwarded-Host'), 'ngrok-free.dev')
            )) ||
            (request()->hasHeader('X-Forwarded-Proto') && request()->header('X-Forwarded-Proto') === 'https')
        ) {
            URL::forceScheme('https');
            request()->server->set('HTTPS', 'on');
        }
    }
}
