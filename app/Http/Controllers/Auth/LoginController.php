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
            return redirect()->route('attendance.qr');
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
            Auth::login($user);
            $request->session()->regenerate();

            // Platform admin → redirect to superadmin finance hub
            if ($user->role === UserRole::PLATFORM_ADMIN) {
                return redirect()->route('superadmin.finance.pending')->with('success', 'Welcome back, ' . $user->name . '!');
            }

            // Check if user is admin or super_admin, redirect accordingly
            if (in_array($user->role, [UserRole::SUPER_ADMIN, UserRole::ADMIN])) {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back, ' . $user->name . '!');
            }
            
            // General users can also access dashboard but with limited features
            if ($user->role === UserRole::GENERAL_USER) {
                return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome, ' . $user->name . '!');
            }

            // Regular staff logic
            // Check if they have biometric data enrolled
            $hasBiometric = \App\Models\BiometricData::where('user_id', $user->id)
                ->whereNotNull('facial_encoding')
                ->exists();

            if (!$hasBiometric) {
                return redirect()->route('biometric.enrollment')
                    ->with('warning', 'Please complete your facial recognition enrollment to continue.');
            }

            // Regular staff - redirect to attendance QR or dashboard
            return redirect()->intended(route('attendance.qr'))->with('success', 'Welcome, ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
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

