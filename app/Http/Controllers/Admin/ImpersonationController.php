<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    /**
     * Impersonate a tenant's primary admin.
     */
    public function impersonate(Tenant $tenant, Request $request)
    {
        // Must be a platform admin or support to do this.
        if (!Auth::user()->isPlatformAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Find the primary admin for this tenant
        // We use withoutTenantScope() because we are currently in the platform domain
        $tenantAdmin = User::withoutTenantScope()
            ->where('tenant_id', $tenant->id)
            ->where(function($query) {
                $query->where('role', 'admin')
                      ->orWhere('role', 'App\Enums\UserRole::ADMIN');
            })
            ->orderBy('id', 'asc')
            ->first();

        if (!$tenantAdmin) {
            return back()->with('error', 'This tenant has no admin users to impersonate.');
        }

        // Store the original platform admin ID in the session
        session(['impersonated_by' => Auth::id()]);

        // Login as the tenant admin
        Auth::login($tenantAdmin);

        // Redirect to the tenant's actual subdomain with a success message
        $scheme = request()->getScheme();
        $baseHost = parse_url(config('app.url'), PHP_URL_HOST);
        
        if (str_contains(request()->getHost(), 'localhost') || str_contains(request()->getHost(), '127.0.0.1')) {
            // Local fallback logic using session
            session(['dev_tenant_subdomain' => $tenant->subdomain]);
            return redirect()->route('admin.dashboard')
                ->with('success', "You are now impersonating {$tenant->company_name}.");
        }

        $tenantUrl = "{$scheme}://{$tenant->subdomain}.{$baseHost}" . route('admin.dashboard', [], false);
        return redirect()->away($tenantUrl);
    }

    /**
     * Leave impersonation and return to the platform admin account.
     */
    public function leave(Request $request)
    {
        if (!session()->has('impersonated_by')) {
            return redirect('/');
        }

        $originalUserId = session('impersonated_by');
        $originalUser = User::withoutTenantScope()->find($originalUserId);

        if ($originalUser) {
            Auth::login($originalUser);
            session()->forget('impersonated_by');
            
            // Revert fallback dev tenant if it was set
            session()->forget('dev_tenant_subdomain');
            
            return redirect()->route('superadmin.tenants')
                ->with('success', 'Successfully returned to your platform admin account.');
        }

        session()->forget('impersonated_by');
        Auth::logout();
        return redirect()->route('login');
    }
}
