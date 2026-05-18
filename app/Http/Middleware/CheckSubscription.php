<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!app()->bound('current_tenant')) {
            return $next($request);
        }

        $tenant = app('current_tenant');

        // Allow access to billing and logout routes even if expired
        if ($request->is('billing*') || $request->is('logout')) {
            return $next($request);
        }

        if (!$tenant->canAccess()) {
            return response()->view('errors.subscription-expired', [
                'tenant' => $tenant
            ], 403);
        }

        return $next($request);
    }
}
