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
        
        $subdomain = $parts[0];
        $tenant = null;

        // 1. Standard host-based resolution (production & standard local DNS)
        if (count($parts) > 1 && $host !== 'zou-attendance.test') {
            $tenant = Tenant::where('subdomain', $subdomain)->first();
        }

        // 2. Fallback for easy local development / testing using ?tenant=subdomain or session
        if (!$tenant) {
            $devSubdomain = $request->query('tenant') ?: $request->input('tenant') ?: session('dev_tenant_subdomain');
            
            if ($devSubdomain) {
                $tenant = Tenant::where('subdomain', $devSubdomain)->first();
                if ($tenant && $request->hasSession()) {
                    session(['dev_tenant_subdomain' => $devSubdomain]);
                }
            }
        }

        // 3. Last fallback: default to first tenant if on local development (localhost / base domain)
        if (!$tenant && ($host === 'localhost' || $host === '127.0.0.1' || $host === 'zou-attendance.test')) {
            $tenant = Tenant::orderBy('id')->first();
        }

        if (!$tenant) {
            abort(404, 'Workspace not found. Please check the URL.');
        }

        // Bind the tenant to the container
        app()->instance('current_tenant', $tenant);

        return $next($request);
    }
}
