<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('register')->with('error', 'Google Authentication failed. Please try again.');
        }

        // Extract the user's email from the Google response payload
        $email = $googleUser->getEmail();

        if (!$email) {
            return redirect()->route('register')->with('error', 'Could not retrieve email from Google. Please try again.');
        }

        // Check if the user exists in our database
        $user = User::where('email', $email)->first();

        if (!$user) {
            // Determine if Student or Staff based on email pattern
            // Student emails look like p239319n@zou.ac.zw (1 letter, digits, optional letter)
            $isStudent = preg_match('/^[a-zA-Z]\d{4,}[a-zA-Z]?@/i', $email);

            if ($isStudent) {
                // Auto-create student
                $studentPin = explode('@', $email)[0];
                $user = User::create([
                    'name' => $googleUser->getName() ?? 'Zou Student',
                    'email' => $email,
                    'employee_number' => strtoupper($studentPin),
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
                    'email_verified_at' => now(),
                    'role' => 'staff' // Defaulting to staff role as per schema for Zou students
                ]);
            } else {
                // It's a staff member, we need their real employee ID
                session([
                    'google_registration' => [
                        'name' => $googleUser->getName(),
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
        if (!session()->has('google_registration')) {
            return redirect()->route('register');
        }
        return view('auth.staff-prompt');
    }

    /**
     * Complete Staff Registration with Employee ID
     */
    public function completeStaffPrompt(Request $request)
    {
        $request->validate([
            'employee_number' => 'required|string|max:50|unique:users,employee_number'
        ]);

        $googleData = session('google_registration');
        if (!$googleData) {
            return redirect()->route('register');
        }

        // Create the staff user
        $user = User::create([
            'name' => $googleData['name'] ?? 'Zou Staff',
            'email' => $googleData['email'],
            'employee_number' => $request->employee_number,
            'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)),
            'email_verified_at' => now(),
            'role' => 'staff'
        ]);

        session()->forget('google_registration');

        Auth::login($user);
        return redirect()->route('biometric.enrollment');
    }
}
