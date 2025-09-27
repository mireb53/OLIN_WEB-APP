<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

    // 2FA moved to TwoFactorController

    /**
     * ======================
     *  Admin Account Management
     * ======================
     */

    // Admin account page moved to AdminAccountController

    // Settings now handled by SettingsController

    /**
     * ======================
     *  Reports & Logs
     * ======================
     */

    // Reports now handled by ReportLogController

    /**
     * ======================
     *  Help & Support
     * ======================
     */

    // Help pages removed from layout; legacy actions removed

    // Course Management functionality moved to CourseManagementController
}
