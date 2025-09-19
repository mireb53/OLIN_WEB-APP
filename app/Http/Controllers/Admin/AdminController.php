<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\Course;
use App\Models\Assessment;
use App\Models\SubmittedAssessment;
use Carbon\Carbon;

class AdminController extends Controller
{
    /**
     * ======================
     *  Two-Factor Authentication
     * ======================
     */

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

        // Send code via email
        Mail::raw("Your admin verification code is: $code", function ($message) use ($user) {
            $message->to($user->email)
                    ->from(config('mail.from.address'), config('mail.from.name'))
                    ->subject('Admin Verification Code');
        });

        return response()->json(['success' => true]);
    }

    // Verify 2FA code before allowing sensitive actions
    public function verify2FACode(Request $request)
    {
        $inputCode = $request->input('code');
        $storedCode = session('admin_2fa_code');
        $expiresAt = session('admin_2fa_expires');

        if (!$storedCode || !$expiresAt || now()->isAfter($expiresAt)) {
            return response()->json(['success' => false, 'message' => 'Code expired or invalid']);
        }

        if ($inputCode == $storedCode) {
            session()->forget(['admin_2fa_code', 'admin_2fa_expires']);
            session(['admin_2fa_verified' => true, 'admin_2fa_verified_at' => now()]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Invalid code']);
    }

    /**
     * ======================
     *  Admin Account Management
     * ======================
     */

    public function account()
    {
        $admin = Auth::user();
        return view('admin.admin_account', compact('admin'));
    }

    public function settings()
    {
        $settings = [
            'system_name' => 'OLIN Learning Management System',
            'version' => '1.0.0',
            'last_updated' => Carbon::now()->subDays(7),
            'maintenance_mode' => false,
            'registration_enabled' => true,
            'email_verification_required' => true,
        ];

        return view('admin.settings', compact('settings'));
    }

    /**
     * ======================
     *  Reports & Logs
     * ======================
     */

    public function reportsLogs()
    {
        $logs = [
            [
                'id' => 1,
                'type' => 'System',
                'level' => 'info',
                'message' => 'User login successful',
                'timestamp' => Carbon::now()->subMinutes(15),
                'user_id' => 5,
            ],
            [
                'id' => 2,
                'type' => 'Security',
                'level' => 'warning',
                'message' => 'Failed login attempt',
                'timestamp' => Carbon::now()->subHours(2),
                'user_id' => null,
            ],
        ];

        return view('admin.reports_logs', compact('logs'));
    }

    /**
     * ======================
     *  Help & Support
     * ======================
     */

    public function help()
    {
        $helpTopics = [
            'Getting Started' => [
                'System Overview',
                'User Management',
                'Course Management',
                'Assessment Creation',
            ],
            'User Management' => [
                'Adding Users',
                'Managing Roles',
                'User Permissions',
                'Account Settings',
            ],
            'Course Management' => [
                'Creating Courses',
                'Managing Content',
                'Student Enrollment',
                'Progress Tracking',
            ],
            'Reports & Analytics' => [
                'Generating Reports',
                'System Logs',
                'Performance Metrics',
                'Data Export',
            ],
        ];

        return view('admin.help', compact('helpTopics'));
    }

    // Course Management functionality moved to CourseManagementController
}
