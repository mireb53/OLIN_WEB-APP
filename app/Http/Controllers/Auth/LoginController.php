<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Notifications\VerifyEmailWithCode;
use Carbon\Carbon;

class LoginController extends Controller
{
    // Shows the single login form
    public function showLoginForm()
    {
        return view('auth.login'); // This loads resources/views/auth/login.blade.php
    }

    // Handles the login form submission
    public function login(Request $request)
    {
        // Validate the input (email and password must be present)
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt to log the user in
        if (Auth::attempt($credentials)) {
            // Regenerate session to prevent session fixation attacks
            $request->session()->regenerate();

            $user = Auth::user(); // Get the currently logged-in user

            // If email is not verified, generate/send a 6-digit verification code and redirect
            if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
                $verificationCode = random_int(100000, 999999);
                $expiresAt = Carbon::now()->addMinutes(config('auth.verification.expire', 60));

                $user->forceFill([
                    'email_verification_code' => $verificationCode,
                    'email_verification_code_expires_at' => $expiresAt,
                ])->save();

                // Notify via email
                $user->notify(new VerifyEmailWithCode($verificationCode));

                return redirect()->route('verification.notice')
                    ->with('status', 'We sent a verification code to your email. Please enter it to continue.');
            }

            // Email already verified: update last login and proceed
            $user->update(['last_login_at' => now()]);

            if (in_array($user->role, ['admin','super_admin','school_admin'])) {
                return redirect()->intended('/admin/dashboard'); 
            } elseif ($user->role === 'instructor') {
                return redirect()->intended('/instructor/dashboard'); 
            }
            // Default redirect for other roles (e.g., 'student')
            return redirect()->intended('/dashboard'); // Generic user dashboard
        }

        // If authentication fails, redirect back with an error message
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Handles user logout
    public function logout(Request $request)
    {
        Auth::logout(); // Log out the user

        $request->session()->invalidate(); // Invalidate the current session
        $request->session()->regenerateToken(); // Regenerate CSRF token for future requests

        return redirect('/login'); // Redirect to login page after logout
    }
}