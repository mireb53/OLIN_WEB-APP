<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Mail\AdminVerificationCode;
use Illuminate\Support\Facades\Mail;

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

        // Send styled HTML email via Mailable
        Mail::to($user->email)->send(new AdminVerificationCode($code, $user));

        return response()->json(['success' => true]);
    }

    // Verify 2FA code before allowing sensitive actions
    public function verify2FACode(Request $request)
    {
        $inputCode = $request->input('code');
        $sessionCode = session('admin_2fa_code');
        $expires = session('admin_2fa_expires');

        if ($sessionCode && $expires && now()->lt($expires) && $inputCode == $sessionCode) {
            session()->forget(['admin_2fa_code', 'admin_2fa_expires']);
            session(['admin_2fa_verified' => true, 'admin_2fa_verified_at' => now()]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false, 'message' => 'Invalid or expired code.']);
    }
}
