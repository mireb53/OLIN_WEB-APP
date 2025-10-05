<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; 
use App\Notifications\VerifyEmailWithCode;
use Carbon\Carbon;
use App\Models\FailedLogin;

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
        // Validate inputs
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['nullable', 'string'],
        ]);

        // Determine user and role to enforce rules
        $user = \App\Models\User::where('email', $request->email)->first();

        // If user is admin (school_admin/super_admin), password is mandatory
        if ($user && in_array($user->role, ['super_admin','school_admin'])) {
            if (empty($request->password)) {
                // Log missing password (no auth attempt will be made)
                $this->logFailedLogin($request, $user, 'missing_password');
            }
            $request->validate(['password' => ['required', 'string']]);
        }

        // Attempt to log the user in if password is provided
        $credentials = ['email' => $request->email, 'password' => (string) $request->password];
        if (!empty($request->password) && Auth::attempt($credentials)) {
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

            // Email already verified: update previous and last login and proceed
            try {
                $user->update([
                    'previous_login_at' => $user->last_login_at,
                    'last_login_at' => now(),
                ]);
            } catch (\Throwable $e) {
                // Fallback if column missing, keep previous behavior
                $user->update(['last_login_at' => now()]);
            }

            if (in_array($user->role, ['admin','super_admin','school_admin'])) {
                return redirect()->intended('/admin/dashboard'); 
            } elseif ($user->role === 'instructor') {
                return redirect()->intended('/instructor/dashboard'); 
            }
            // Default redirect for other roles (e.g., 'student')
            return redirect()->intended('/dashboard'); // Generic user dashboard
        }

        // If authentication fails
        if ($user && empty($request->password)) {
            // No password provided
            if (in_array($user->role, ['super_admin','school_admin'])) {
                // log failed attempt: missing password for admin
                $this->logFailedLogin($request, $user, 'missing_password');
                return back()->withErrors([
                    'email' => 'Admins must sign in with email and password.',
                ])->onlyInput('email');
            } else {
                // log failed attempt: missing password for non-admin
                $this->logFailedLogin($request, $user, 'missing_password');
                return back()->withErrors([
                    'email' => 'Please enter your password to sign in here, or use "Sign in with Google".',
                ])->onlyInput('email');
            }
        }

        // Note: do not log here to avoid duplicate entries; the Auth Failed event listener will record invalid credentials.
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Handles user logout
    public function logout(Request $request)
    {
        // Clear any school context on logout
        $request->session()->forget('active_school');

        Auth::logout(); // Log out the user

        $request->session()->invalidate(); // Invalidate the current session
        $request->session()->regenerateToken(); // Regenerate CSRF token for future requests

        return redirect('/login'); // Redirect to login page after logout
    }

    /**
     * Record a failed login attempt with metadata
     */
    protected function logFailedLogin(Request $request, ?\App\Models\User $user, string $reason): void
    {
        try {
            FailedLogin::create([
                'user_id' => $user?->id,
                'email' => $request->input('email'),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string)$request->header('User-Agent'), 0, 255),
                'reason' => $reason,
            ]);
        } catch (\Throwable $e) {
            // Do not disrupt the login flow
        }
    }
}