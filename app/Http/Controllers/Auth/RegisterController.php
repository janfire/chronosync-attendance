<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), User::rules());

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Store registration data in session instead of creating user immediately
        // Note: Don't hash password here - User model will handle it via 'hashed' cast
        session([
            'pending_registration' => [
                'name' => $request->name,
                'email' => $request->email,
                'employee_number' => $request->employee_number,
                'password' => $request->password, // Store plain password, model will hash it
                'role' => 'staff',
            ]
        ]);

        // Redirect to biometric enrollment
        return redirect()->route('biometric.enrollment')
            ->with('success', 'Please complete your biometric enrollment to finish registration.');
    }
}