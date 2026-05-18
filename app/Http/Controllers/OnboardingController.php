<?php

namespace App\Http\Controllers;

use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    protected $provisioningService;

    public function __construct(TenantProvisioningService $provisioningService)
    {
        $this->provisioningService = $provisioningService;
    }

    public function showSignup()
    {
        return view('landing.signup');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'subdomain'    => 'required|alpha_dash|unique:tenants,subdomain|max:50',
            'email'        => 'required|email|max:255',
            'admin_name'   => 'required|string|max:255',
            'admin_email'  => 'required|email|unique:users,email',
            'password'     => 'required|min:8|confirmed',
        ]);

        $result = $this->provisioningService->provision($validated);

        // Auto-login the new administrator
        auth()->login($result['admin']);
        
        return redirect()->route('admin.dashboard')->with('success', "Welcome to your new workspace, {$result['tenant']->company_name}!");
    }
}
