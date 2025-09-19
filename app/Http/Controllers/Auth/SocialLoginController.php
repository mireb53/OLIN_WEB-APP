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
                
                // Update last login timestamp
                $user->update(['last_login_at' => now()]);
                
                Auth::login($user, true); // Log in the user, "true" for remember me
                Log::info('User logged in via Google: ' . $user->email);

            } else {
                // New user - create a new account
                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(24)), // Generate a random password for social users
                    'role' => 'instructor', // Default role for new social sign-ups
                    'email_verified_at' => now(), // Assume email is verified by Google
                    'status' => 'active', // Set default status for new users
                    'last_login_at' => now(), // Set login timestamp
                ]);
                Auth::login($user, true);
                Log::info('New user registered and logged in via Google: ' . $user->email);
            }

            // Redirect to the intended dashboard after login/registration
            if ($user->role === 'admin') {
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
