<?php

namespace App\Http\Controllers;

use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\WorkspaceWelcomeMail;

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
        
        // Construct the full URL with the new subdomain
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME);
        $subdomain = $result['tenant']->subdomain;
        
        $workspaceUrl = "{$scheme}://{$subdomain}.{$appHost}";
        $dashboardPath = route('admin.dashboard', [], false);
        $redirectUrl = "{$workspaceUrl}{$dashboardPath}";

        // Send Workspace Created notification with Login URL & User Guide info
        try {
            Mail::to($result['admin']->email)->send(new WorkspaceWelcomeMail(
                $result['tenant']->company_name,
                $result['admin']->name,
                $workspaceUrl,
                $result['admin']->email
            ));
        } catch (\Exception $e) {
            // Log the error but don't crash the onboarding flow
            logger()->error('Failed to send workspace welcome email: ' . $e->getMessage());
        }

        return redirect()->to($redirectUrl)->with('success', "Welcome to your new workspace, {$result['tenant']->company_name}!");
    }
}
