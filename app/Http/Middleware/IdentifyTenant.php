<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip for superadmin routes
        if ($request->is('superadmin*')) {
            return $next($request);
        }

        $host = $request->getHost();
        $parts = explode('.', $host);
        
        // If we're on a single-part host (like 'localhost' or an IP)
        // we can't easily determine tenant. 
        // For dev, let's assume if only 1 part, we check if a 'taxease' tenant exists as fallback
        // In prod, $parts[0] will be the subdomain.
        $subdomain = $parts[0];

        $tenant = Tenant::where('subdomain', $subdomain)->first();

        // Fallback for development if accessing directly via localhost:8000
        if (!$tenant && ($host === 'localhost' || $host === '127.0.0.1')) {
            $tenant = Tenant::where('subdomain', 'taxease')->first();
        }

        if (!$tenant) {
            // If it's a public route like /login, we might allow it without a tenant 
            // BUT a SaaS login should be tenant-aware.
            // For now, if no tenant, we just proceed (onboarding/landing page).
            return $next($request);
        }

        // Bind the tenant to the container
        app()->instance('current_tenant', $tenant);

        return $next($request);
    }
}
