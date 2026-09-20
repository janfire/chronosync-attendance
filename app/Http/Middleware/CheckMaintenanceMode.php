<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\GlobalSetting;
use Illuminate\Support\Facades\Auth;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (GlobalSetting::get('maintenance_mode', false)) {
            // Allow platform admins to bypass maintenance mode
            if (Auth::check() && Auth::user()->isPlatformAdmin()) {
                return $next($request);
            }

            // Exclude superadmin login and paths from maintenance mode
            if ($request->is('login') || $request->is('logout') || $request->is('superadmin*')) {
                return $next($request);
            }
            
            // Allow impersonated sessions to bypass
            if (session()->has('impersonated_by')) {
                return $next($request);
            }

            $message = GlobalSetting::get('maintenance_message', 'The system is currently undergoing scheduled maintenance. Please check back later.');
            
            // Abort with 503 so a custom error page can be shown
            abort(503, $message);
        }

        return $next($request);
    }
}
