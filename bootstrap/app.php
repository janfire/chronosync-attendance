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
        $middleware->trustProxies(at: '*');
        $middleware->alias([
            'admin'          => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'tenant'         => \App\Http\Middleware\IdentifyTenant::class,
            'subscription'   => \App\Http\Middleware\CheckSubscription::class,
            'platform_admin' => \App\Http\Middleware\EnsureUserIsPlatformAdmin::class,
            'platform_role'  => \App\Http\Middleware\EnsurePlatformRole::class,
            'feature'        => \App\Http\Middleware\CheckTenantFeature::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\CheckMaintenanceMode::class,
        ]);
        $middleware->redirectGuestsTo(fn () => route('login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e) {
            if (app()->bound('request')) {
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() < 500) {
                    return;
                }
                try {
                    $ref = 'ERR-' . strtoupper(substr(uniqid(), -6));
                    request()->merge(['error_reference_code' => $ref]);
                    
                    \App\Models\SystemErrorLog::create([
                        'reference_code' => $ref,
                        'tenant_id' => app()->bound('current_tenant') ? app('current_tenant')->id : null,
                        'user_id' => auth()->id(),
                        'url' => request()->fullUrl(),
                        'method' => request()->method(),
                        'message' => $e->getMessage() ?: get_class($e),
                        'stack_trace' => $e->getTraceAsString(),
                        'status' => 'new'
                    ]);
                } catch (\Exception $loggingException) {}
            }
        });

        $exceptions->render(function (\Throwable $e, $request) {
            if ($e instanceof \Illuminate\Session\TokenMismatchException) {
                return redirect()->route('login')->with('error', 'Your session has expired. Please login again.');
            }

            if (!config('app.debug') && (!$e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface || $e->getStatusCode() >= 500)) {
                $ref = request('error_reference_code', 'ERR-UNKNOWN');
                
                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'message' => 'Internal Server Error. Our technicians have been notified.',
                        'reference' => $ref
                    ], 500);
                }
                
                return response()->view('errors.500', ['reference_code' => $ref], 500);
            }
        });
    })->create();

