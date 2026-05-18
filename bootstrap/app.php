<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\TokenMismatchException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'          => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'tenant'         => \App\Http\Middleware\IdentifyTenant::class,
            'subscription'   => \App\Http\Middleware\CheckSubscription::class,
            'platform_admin' => \App\Http\Middleware\EnsureUserIsPlatformAdmin::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TokenMismatchException $e, $request) {
            return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
        });
    })->create();

