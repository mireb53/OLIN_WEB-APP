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
            // Check if Google reports the email as verified (used only after first login)
            $isGoogleVerified = (bool) data_get($googleUser->user, 'email_verified');

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
                // Sync profile names from Google on first successful Google login or if current name looks auto-generated
                $googleName = $googleUser->getName() ?: data_get($googleUser->user, 'name');
                $givenName = data_get($googleUser->user, 'given_name');
                $familyName = data_get($googleUser->user, 'family_name');
                $currentName = trim((string) $user->name);
                $emailLocal = strstr($user->email, '@', true) ?: '';
                if ($googleName && ($currentName === '' || strcasecmp($currentName, $emailLocal) === 0 || empty($user->first_name) || empty($user->last_name))) {
                    $user->name = $googleName;
                    if ($givenName) $user->first_name = $givenName;
                    if ($familyName) $user->last_name = $familyName;
                    $user->save();
                    Log::info('Synced user name from Google for: ' . $user->email);
                }
                
                // Enforce policy: Admin roles must have DB password (login with Google may be allowed but password must exist)
                if (in_array($user->role, ['super_admin','school_admin']) && empty($user->password)) {
                    return redirect('/login')->with('error', 'Admin accounts must sign in with email and password. Please use password login.');
                }

                // Log the user in (don't set remember on social login to avoid remember_token issues)
                Auth::login($user);
                session()->regenerate();
                Log::info('User logged in via Google: ' . $user->email);

                // Require OTP on first login always (even if Google email is verified)
                if (is_null($user->last_login_at)) {
                    $verificationCode = random_int(100000, 999999);
                    $expiresAt = Carbon::now()->addMinutes(config('auth.verification.expire', 60));
                    $user->forceFill([
                        'email_verification_code' => $verificationCode,
                        'email_verification_code_expires_at' => $expiresAt,
                    ])->save();
                    try {
                        $user->notify(new VerifyEmailWithCode($verificationCode));
                    } catch (\Throwable $mailEx) {
                        // Do not fail login just because email sending failed (e.g., SMTP quota)
                        Log::error('Failed to send verification code email: ' . $mailEx->getMessage(), ['exception' => $mailEx]);
                        if (config('app.debug')) {
                            return redirect()->route('verification.notice')
                                ->with('status', 'Email sending is unavailable. Use this verification code: ' . $verificationCode)
                                ->with('dev_verification_code', $verificationCode);
                        }
                        return redirect()->route('verification.notice')
                            ->with('status', 'We could not send the email right now. Please try again in a few minutes or contact your administrator.');
                    }
                    return redirect()->route('verification.notice')
                        ->with('status', 'We sent a verification code to your email. Please enter it to continue.');
                }

                // After first login, if email is still not verified, consider Google's verified signal
                if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
                    if ($isGoogleVerified) {
                        if (method_exists($user, 'markEmailAsVerified')) {
                            $user->markEmailAsVerified();
                        } else {
                            $user->forceFill(['email_verified_at' => now()])->save();
                        }
                        Log::info('Email auto-verified from Google for: ' . $user->email);
                    } else {
                        $verificationCode = random_int(100000, 999999);
                        $expiresAt = Carbon::now()->addMinutes(config('auth.verification.expire', 60));
                        $user->forceFill([
                            'email_verification_code' => $verificationCode,
                            'email_verification_code_expires_at' => $expiresAt,
                        ])->save();
                        try {
                            $user->notify(new VerifyEmailWithCode($verificationCode));
                        } catch (\Throwable $mailEx) {
                            Log::error('Failed to send verification code email: ' . $mailEx->getMessage(), ['exception' => $mailEx]);
                            if (config('app.debug')) {
                                return redirect()->route('verification.notice')
                                    ->with('status', 'Email sending is unavailable. Use this verification code: ' . $verificationCode)
                                    ->with('dev_verification_code', $verificationCode);
                            }
                            return redirect()->route('verification.notice')
                                ->with('status', 'We could not send the email right now. Please try again in a few minutes or contact your administrator.');
                        }
                        return redirect()->route('verification.notice')
                            ->with('status', 'We sent a verification code to your email. Please enter it to continue.');
                    }
                }

            } else {
                // Public self-registration is disabled. Block creating new users via Google.
                Log::warning('Google login attempted for non-existing user when registration is disabled: ' . $googleUser->email);
                return redirect('/login')->with('error', 'Your account is not registered. Please contact your administrator.');
            }

            // Update last/previous login when completing login (outside verification flow)
            try {
                // Only set timestamps when the user is fully logged in (email verified or no verification required)
                if (!is_null($user->email_verified_at) || (method_exists($user, 'hasVerifiedEmail') && $user->hasVerifiedEmail())) {
                    $user->update([
                        'previous_login_at' => $user->last_login_at,
                        'last_login_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {
                // no-op on failure
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
