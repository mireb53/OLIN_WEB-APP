<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str; // <--- ADD THIS LINE
use App\Notifications\VerifyEmailWithCode;
use Carbon\Carbon;

class SocialLoginController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleGoogleCallback()
    {
        try {
            // Get user data from Google
            $googleUser = Socialite::driver('google')->user();

            // Find user by Google ID or email
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
                // User exists
                // If user exists but doesn't have google_id (e.g., they registered with email/password)
                // Link their Google account to their existing account
                if (empty($user->google_id)) {
                    $user->google_id = $googleUser->id;
                    $user->save();
                    Log::info('Google account linked to existing user: ' . $user->email);
                }
                
                // Log the user in
                Auth::login($user, true);
                Log::info('User logged in via Google: ' . $user->email);

                // If not verified, send OTP and redirect to verification page
                if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
                    $verificationCode = random_int(100000, 999999);
                    $expiresAt = Carbon::now()->addMinutes(config('auth.verification.expire', 60));
                    $user->forceFill([
                        'email_verification_code' => $verificationCode,
                        'email_verification_code_expires_at' => $expiresAt,
                    ])->save();
                    $user->notify(new VerifyEmailWithCode($verificationCode));
                    return redirect()->route('verification.notice')
                        ->with('status', 'We sent a verification code to your email. Please enter it to continue.');
                }

            } else {
                // Public self-registration is disabled. Block creating new users via Google.
                Log::warning('Google login attempted for non-existing user when registration is disabled: ' . $googleUser->email);
                return redirect('/login')->with('error', 'Your account is not registered. Please contact your administrator.');
            }

            // Redirect to the intended dashboard after login/registration
            if ($user->role === 'admin' || $user->role === 'super_admin' || $user->role === 'school_admin') {
                return redirect()->intended('/admin/dashboard');
            } elseif ($user->role === 'instructor') {
                return redirect()->intended('/instructor/dashboard');
            } else {
                return redirect()->intended('/dashboard'); // Generic user dashboard (e.g., student dashboard)
            }

        } catch (\Exception $e) {
            Log::error('Google login failed: ' . $e->getMessage(), ['exception' => $e]);
            return redirect('/login')->with('error', 'Google sign-in failed. Please try again.');
        }
    }
}
