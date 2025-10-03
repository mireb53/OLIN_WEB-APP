<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Mail\AdminVerificationCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class TwoFactorController extends Controller
{
    // Request 2FA code for sensitive actions
    public function request2FACode(Request $request)
    {
        $user = Auth::user();
        $code = rand(100000, 999999);

        // Store code with expiration (5 min + 5 sec)
        session([
            'admin_2fa_code' => $code,
            'admin_2fa_expires' => now()->addMinutes(5)->addSeconds(5),
        ]);

        // Also persist to user record so admins can retrieve/verify via DB if needed
        if ($user) {
            $user->forceFill([
                'email_verification_code' => (string) $code,
                'email_verification_code_expires_at' => now()->addMinutes(5)->addSeconds(5),
            ])->save();
        }

        // Send styled HTML email via Mailable
        Mail::to($user->email)->send(new AdminVerificationCode($code, $user));

        return response()->json(['success' => true]);
    }

    // Verify 2FA code before allowing sensitive actions
    public function verify2FACode(Request $request)
    {
        $inputCode = (string) $request->input('code');
        $sessionCode = (string) session('admin_2fa_code');
        $sessionExpires = session('admin_2fa_expires');
        $user = Auth::user();

        $sessionValid = $sessionCode && $sessionExpires && now()->lt($sessionExpires) && hash_equals($sessionCode, $inputCode);
        $dbValid = false;
        if ($user) {
            $dbCode = (string) ($user->email_verification_code ?? '');
            $dbExpires = $user->email_verification_code_expires_at;
            $dbValid = $dbCode !== '' && $dbExpires && now()->lt($dbExpires) && hash_equals($dbCode, $inputCode);
        }

        if ($sessionValid || $dbValid) {
            // Clear session codes
            session()->forget(['admin_2fa_code', 'admin_2fa_expires']);
            session(['admin_2fa_verified' => true, 'admin_2fa_verified_at' => now()]);
            // Clear DB codes to prevent reuse
            if ($user) {
                $user->forceFill([
                    'email_verification_code' => null,
                    'email_verification_code_expires_at' => null,
                ])->save();
            }
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid or expired code.']);
    }
}
