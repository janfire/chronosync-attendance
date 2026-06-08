<?php

namespace App\Http\Controllers;

use App\Services\TenantProvisioningService;
use Illuminate\Http\Request;
use App\Models\Tenant;
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

    /**
     * AJAX: Check subdomain availability and suggest alternatives.
     */
    public function checkSubdomain(Request $request)
    {
        $company = (string) $request->input('company_name', '');

        $slug = preg_replace('/[^a-z0-9]+/','-', strtolower($company));
        $slug = trim($slug, '-');
        $slug = substr($slug, 0, 50);
        if ($slug === '') {
            $slug = 'workspace';
        }

        // Find an available candidate (append -1, -2, ... if needed)
        for ($i = 0; $i < 100; $i++) {
            $candidate = $slug . ($i > 0 ? "-{$i}" : '');
            $exists = Tenant::where('subdomain', $candidate)->exists();
            if (! $exists) {
                return response()->json([
                    'available' => true,
                    'subdomain' => $candidate,
                ]);
            }
        }

        return response()->json([
            'errors' => ['subdomain' => ['No available subdomain found for this company name.']],
        ], 422);
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'subdomain'    => 'required|alpha_dash|unique:tenants,subdomain|max:50',
            'email'        => 'required|email|unique:users,email|max:255',
            'admin_name'   => 'required|string|max:255',
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

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'workspace_url' => $workspaceUrl,
                'redirect_url' => $redirectUrl,
                'subdomain' => $subdomain,
                'company_name' => $result['tenant']->company_name,
            ]);
        }

        return redirect()->to($redirectUrl)->with('success', "Welcome to your new workspace, {$result['tenant']->company_name}!");
    }
}
