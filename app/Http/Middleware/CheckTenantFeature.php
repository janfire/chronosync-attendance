<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTenantFeature
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = \App\Models\Tenant::find(Auth::user()->tenant_id) ?? current_tenant();

        if (!$tenant || !$tenant->hasFeature($feature)) {
            return redirect()->route('billing.index')->with('error', "Your current plan does not include the '{$feature}' feature. Please upgrade to access this.");
        }

        return $next($request);
    }
}
