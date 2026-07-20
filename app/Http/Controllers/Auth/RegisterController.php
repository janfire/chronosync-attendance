<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Mail\RegistrationOtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // --- UX ENHANCEMENT: Auto-format the National ID ---
        if ($request->has('employee_number')) {
            // Strip everything except letters and numbers
            $cleanId = preg_replace('/[^A-Za-z0-9]/', '', $request->employee_number);
            
            // If it matches 2 digits, 6-7 digits, 1 letter, 2 digits
            if (preg_match('/^(\d{2})(\d{6,7})([A-Za-z])(\d{2})$/', $cleanId, $matches)) {
                $formattedId = $matches[1] . '-' . $matches[2] . ' ' . strtoupper($matches[3]) . ' ' . $matches[4];
                $request->merge(['employee_number' => $formattedId]);
            }
        }

        $rules = User::rules();
        
        // PREVENT USER ENUMERATION: Remove 'unique' checks from the initial form.
        // We will only check if the account exists AFTER they prove they own the email via OTP.
        if (is_array($rules['email'])) {
            $rules['email'] = array_filter($rules['email'], fn($rule) => !($rule instanceof \Illuminate\Validation\Rules\Unique));
        } elseif (is_string($rules['email'])) {
            $rules['email'] = str_replace('|unique:users', '', $rules['email']);
        }
        
        // Specifically enforce National ID format without the unique check
        $rules['employee_number'] = ['required', 'string', 'regex:/^\d{2}-\d{6,7}\s?[A-Za-z]\s?\d{2}$/'];
        
        $isGuest = $request->has('is_guest') && $request->is_guest == '1';
        if ($isGuest) {
            $rules['stay_duration'] = ['required', 'in:1_day,3_days,1_week,1_month'];
        }
        
        $messages = [
            'employee_number.regex' => 'The ID Number must be a valid National ID format (e.g. 12-345678 A 12).'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $role = $isGuest ? 'guest' : 'staff';
        $expiresAt = null;

        if ($isGuest) {
            $expiresAt = match($request->stay_duration) {
                '1_day' => now()->addDay(),
                '3_days' => now()->addDays(3),
                '1_week' => now()->addWeek(),
                '1_month' => now()->addMonth(),
                default => now()->addDay(),
            };
        }

        // Store unverified registration data in session
        session([
            'unverified_registration' => [
                'name' => $request->name,
                'email' => $request->email,
                'employee_number' => $request->employee_number,
                'password' => $request->password,
                'role' => $role,
                'expires_at' => $expiresAt,
            ]
        ]);

        $this->sendOtp($request->email);

        return redirect()->route('register.otp')
            ->with('success', 'Please check your email for the verification code.');
    }

    public function showVerifyOtpForm()
    {
        if (!session()->has('unverified_registration')) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $storedOtp = session('registration_otp');
        $expiresAt = session('registration_otp_expires_at');

        if (!$storedOtp || !$expiresAt || now()->greaterThan($expiresAt)) {
            return redirect()->back()->withErrors(['otp' => 'Verification code has expired. Please request a new one.']);
        }

        if ($request->otp !== $storedOtp) {
            return redirect()->back()->withErrors(['otp' => 'Invalid verification code. Please try again.']);
        }

        // OTP is correct! The user has proven they own this email.
        // NOW it is safe to check if they already have an account without risking user enumeration.
        $unverifiedData = session('unverified_registration');
        $userExists = clone User::where('email', $unverifiedData['email'])
                          ->orWhere('employee_number', $unverifiedData['employee_number']);
                          
        // Check without soft-deleted
        if ($userExists->exists()) {
            session()->forget(['unverified_registration', 'registration_otp', 'registration_otp_expires_at']);
            return redirect()->route('login') // Assuming a 'login' route exists
                ->with('error', 'An account with this email or ID already exists. Please log in.');
        }

        // Code is correct and user is new, promote to pending_registration
        session(['pending_registration' => $unverifiedData]);
        
        // Clean up OTP session data
        session()->forget(['unverified_registration', 'registration_otp', 'registration_otp_expires_at']);

        // Redirect to biometric enrollment
        return redirect()->route('biometric.enrollment')
            ->with('success', 'Email verified successfully! Please complete your biometric enrollment.');
    }

    public function resendOtp()
    {
        if (!session()->has('unverified_registration')) {
            return redirect()->route('register');
        }

        $email = session('unverified_registration')['email'];
        $this->sendOtp($email);

        return redirect()->back()->with('success', 'A new verification code has been sent to your email.');
    }

    private function sendOtp(string $email)
    {
        $otp = (string) random_int(100000, 999999);

        session([
            'registration_otp' => $otp,
            'registration_otp_expires_at' => now()->addMinutes(10),
        ]);

        // LOG THE OTP FOR LOCAL TESTING
        \Illuminate\Support\Facades\Log::info("====== EMAIL OTP INTERCEPTED ======");
        \Illuminate\Support\Facades\Log::info("OTP for {$email} is: {$otp}");
        \Illuminate\Support\Facades\Log::info("===================================");

        try {
            Mail::to($email)->send(new RegistrationOtpMail($otp));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to send OTP email: ' . $e->getMessage());
            // Fail silently so the user can still land on the OTP page and press resend if needed
        }
    }
}