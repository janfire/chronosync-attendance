<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Enums\UserRole;
use App\Http\Responses\ApiResponse;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        // If already logged in, redirect based on role
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->role === UserRole::PLATFORM_ADMIN) {
                return redirect()->route('superadmin.finance.pending');
            }
            if (in_array($user->role, [UserRole::SUPER_ADMIN, UserRole::ADMIN, UserRole::GENERAL_USER])) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('staff.dashboard');
        }
        
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Use withoutTenantScope() so platform admins (tenant_id=null) can be found
        // regardless of which tenant subdomain the login page is accessed from.
        $user = User::withoutTenantScope()->where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Prevent cross-workspace logins
            $currentTenant = app()->bound('current_tenant') ? app('current_tenant') : null;
            if ($currentTenant && $user->role !== UserRole::PLATFORM_ADMIN && $user->tenant_id !== $currentTenant->id) {
                return back()->withErrors([
                    'email' => 'This account does not belong to this workspace.',
                ])->withInput($request->only('email'));
            }

            // Generate Email Approval Tokens
            $token = \Illuminate\Support\Str::random(64);
            $sessionId = \Illuminate\Support\Str::uuid()->toString();

            \Illuminate\Support\Facades\Cache::put("pending_login_{$token}", [
                'user_id' => $user->id,
                'session_id' => $sessionId,
                'status' => 'pending'
            ], now()->addMinutes(10));

            $deviceInfo = $request->header('User-Agent') ?? 'Unknown Device';
            $ipAddress = $request->header('X-Forwarded-For') ?? $request->ip();

            try {
                \Illuminate\Support\Facades\Mail::to($user->email)->queue(new \App\Mail\LoginApprovalMail($token, $deviceInfo, $ipAddress));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send login approval email: ' . $e->getMessage());
                return back()->withErrors(['email' => 'Failed to send login approval email. Please try again.']);
            }

            session([
                'login_session_id' => $sessionId,
                'pending_token' => $token
            ]);

            return redirect()->route('login.pending');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    public function showPending()
    {
        if (!session()->has('pending_token')) {
            return redirect()->route('login');
        }
        return view('auth.pending-approval');
    }

    public function checkApproval(Request $request)
    {
        $token = session('pending_token');
        $sessionId = session('login_session_id');

        if (!$token || !$sessionId) {
            return response()->json(['status' => 'expired']);
        }

        $cacheData = \Illuminate\Support\Facades\Cache::get("pending_login_{$token}");

        if (!$cacheData || $cacheData['session_id'] !== $sessionId) {
            return response()->json(['status' => 'expired']);
        }

        if ($cacheData['status'] === 'approved') {
            $user = User::withoutTenantScope()->find($cacheData['user_id']);
            if ($user) {
                Auth::login($user);
                $request->session()->regenerate();
                
                // Clear pending session data
                session()->forget(['login_session_id', 'pending_token']);
                \Illuminate\Support\Facades\Cache::forget("pending_login_{$token}");

                return response()->json([
                    'status' => 'approved',
                    'redirect_url' => $this->getRedirectUrlForUser($user)
                ]);
            }
        }

        return response()->json(['status' => 'pending']);
    }

    public function approveLogin($token)
    {
        $cacheData = \Illuminate\Support\Facades\Cache::get("pending_login_{$token}");

        if (!$cacheData) {
            return view('auth.approval-success', ['success' => false, 'message' => 'This link has expired or is invalid.']);
        }

        $cacheData['status'] = 'approved';
        \Illuminate\Support\Facades\Cache::put("pending_login_{$token}", $cacheData, now()->addMinutes(5));

        return view('auth.approval-success', ['success' => true, 'message' => 'Login Approved! You can safely close this tab and return to your original device.']);
    }

    private function getRedirectUrlForUser(User $user)
    {
        if ($user->role === UserRole::PLATFORM_ADMIN) {
            return route('superadmin.dashboard');
        }

        if (in_array($user->role, [UserRole::SUPER_ADMIN, UserRole::ADMIN, UserRole::GENERAL_USER])) {
            return route('admin.dashboard');
        }

        $hasBiometric = \App\Models\BiometricData::where('user_id', $user->id)
            ->whereNotNull('facial_encoding')
            ->exists();

        if (!$hasBiometric) {
            return route('biometric.enrollment');
        }

        return route('staff.dashboard');
    }

    /**
     * Kiosk flow: authenticate a previously-registered user so they can update biometrics.
     * Accepts either email or employee number as the identifier.
     */
    public function loginForBiometricUpdate(Request $request)
    {
        try {
            $request->validate([
                'identifier' => 'required|string|max:255',
                'password' => 'required|string|max:255',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }

        $identifier = trim((string)$request->input('identifier'));
        $password = (string)$request->input('password');

        $user = User::query()
            ->where('email', $identifier)
            ->orWhere('employee_number', $identifier)
            ->first();

        if (!$user) {
            return ApiResponse::error('Account not found. Please check your email or employee number.', 404);
        }

        if (!Hash::check($password, $user->password)) {
            return ApiResponse::error('Invalid credentials. Please try again.', 422);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return ApiResponse::success([
            'redirect_url' => route('biometric.enrollment'),
        ], 'Authenticated. Redirecting to biometric enrollment...');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}

