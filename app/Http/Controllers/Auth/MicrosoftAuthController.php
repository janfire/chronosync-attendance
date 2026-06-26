<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class MicrosoftAuthController extends Controller
{
    /**
     * Redirect the user to the Microsoft Azure AD authentication page.
     */
    public function redirect()
    {
        return Socialite::driver('azure')->redirect();
    }

    /**
     * Obtain the user information from Microsoft Azure AD.
     */
    public function callback()
    {
        try {
            $azureUser = Socialite::driver('azure')->user();
        } catch (\Exception $e) {
            return redirect()->route('register')->with('error', 'Microsoft Authentication failed. Please try again.');
        }

        // Extract the user's email from the Azure AD response payload
        $email = $azureUser->getEmail() ?? $azureUser->user['userPrincipalName'] ?? null;

        if (!$email) {
            return redirect()->route('register')->with('error', 'Could not retrieve email from Microsoft. Please contact IT.');
        }

        // Check if the user exists in our database (Zou Client)
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Determine if Student or Staff based on email pattern
            // Student emails look like p239319n@zou.ac.zw (1 letter, digits, optional letter)
            $isStudent = preg_match('/^[a-zA-Z]\d{4,}[a-zA-Z]?@/i', $email);

            if ($isStudent) {
                // Auto-create student
                $studentPin = explode('@', $email)[0];
                $user = User::create([
                    'name' => $azureUser->getName() ?? 'Zou Student',
                    'email' => $email,
                    'employee_number' => strtoupper($studentPin),
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
                    'email_verified_at' => now(),
                    'role' => 'staff' // Defaulting to staff role as per schema
                ]);
            } else {
                // It's a staff member, we need their real employee ID
                session([
                    'azure_registration' => [
                        'name' => $azureUser->getName(),
                        'email' => $email,
                    ]
                ]);
                return redirect()->route('auth.staff.prompt');
            }
        }

        // Log them in securely
        Auth::login($user);
        
        // Redirect to biometric enrollment
        return redirect()->route('biometric.enrollment');
    }

    /**
     * Show the prompt for Staff Employee ID
     */
    public function showStaffPrompt()
    {
        if (!session()->has('azure_registration')) {
            return redirect()->route('register');
        }
        return view('auth.staff-prompt');
    }

    /**
     * Complete Staff Registration with Employee ID
     */
    public function completeStaffPrompt(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'employee_number' => 'required|string|max:50|unique:users,employee_number'
        ]);

        $azureData = session('azure_registration');
        if (!$azureData) {
            return redirect()->route('register');
        }

        // Create the staff user
        $user = User::create([
            'name' => $azureData['name'] ?? 'Zou Staff',
            'email' => $azureData['email'],
            'employee_number' => $request->employee_number,
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
            'email_verified_at' => now(),
            'role' => 'staff'
        ]);

        session()->forget('azure_registration');

        Auth::login($user);
        return redirect()->route('biometric.enrollment');
    }
}
