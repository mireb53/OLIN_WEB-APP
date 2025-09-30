<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str; // Import Str facade
use Carbon\Carbon; // Import Carbon for time manipulation
use App\Notifications\VerifyEmailWithCode; // Import your custom notification


class RegisterController extends Controller
{
    // Method to show the instructor signup form
    public function showInstructorRegistrationForm()
    {
        return view('auth.instructor_signup');
    }

    // Method to handle the instructor registration form submission
    public function registerInstructor(Request $request)
    {
        // 1. Validate the incoming request data
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Generate a 6-digit numeric verification code
        $verificationCode = random_int(100000, 999999);
        // Set expiry time (e.g., 60 minutes from now)
        $expiresAt = Carbon::now()->addMinutes(60);

        // 2. Create a new user in the database
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'instructor',
            'status' => 'active', // Set default status
            'email_verification_code' => $verificationCode, // Save the code
            'email_verification_code_expires_at' => $expiresAt, // Save expiry
            'last_login_at' => now(), // Set initial login timestamp
        ]);

        // 3. Log the user in immediately after registration (optional, but common)
        Auth::login($user);

        // 4. Send the email verification notification with the code
        $user->notify(new VerifyEmailWithCode($verificationCode));

        // 5. Notify all admins about the new instructor registration
    NotificationService::notifyAdminNewRegistration($user);

        // 6. Redirect the user to the email verification notice page
        return redirect()->route('verification.notice')->with('status', 'A verification code has been sent to your email address.');
    }
}