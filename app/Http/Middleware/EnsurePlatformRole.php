<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePlatformRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // If the user has the master platform_admin role, they can access everything.
        if ($user->hasPlatformRole(\App\Enums\UserRole::PLATFORM_ADMIN)) {
            return $next($request);
        }

        // Check against provided sub-roles
        foreach ($roles as $role) {
            if ($user->hasPlatformRole($role)) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized. Insufficient platform privileges.');
    }
}
