<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon; // Import Carbon

class VerificationController extends Controller
{
    /**
     * Handle the incoming request to verify the email with a code.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'verification_code' => ['required', 'numeric', 'digits:6'],
        ]);

        $user = Auth::user();

        // Check if the provided code matches and is not expired
        if ($user->email_verification_code === $request->verification_code &&
            $user->email_verification_code_expires_at &&
            Carbon::now()->lt($user->email_verification_code_expires_at)) {

            $user->markEmailAsVerified();
            $user->forceFill([
                'email_verification_code' => null,
                'email_verification_code_expires_at' => null,
            ])->save();

            // Update previous/last login timestamps now that verification is successful
            try {
                $user->update([
                    'previous_login_at' => $user->last_login_at,
                    'last_login_at' => now(),
                ]);
            } catch (\Throwable $e) {
                $user->update(['last_login_at' => now()]);
            }

            // Redirect based on role
            if ($user->role === 'admin' || $user->role === 'super_admin' || $user->role === 'school_admin') {
                return redirect()->intended('/admin/dashboard')->with('status', 'Your email has been verified!');
            } elseif ($user->role === 'instructor') {
                return redirect()->intended('/instructor/dashboard')->with('status', 'Your email has been verified!');
            }

            return redirect('/dashboard')->with('status', 'Your email has been verified!');
        }

        throw ValidationException::withMessages([
            'verification_code' => ['The provided verification code is invalid or has expired.'],
        ]);
    }
}