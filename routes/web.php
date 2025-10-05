<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\CourseManagementController;
use App\Http\Controllers\Instructor\InstructorController;
use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Instructor\MaterialController;
use App\Http\Controllers\Instructor\AssessmentController;
use App\Http\Controllers\Instructor\TopicController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Admin\AdminNotificationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\VerificationController; // NEW: Import the new controller
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\NotificationController as AdminUINotificationController;
use App\Http\Controllers\Admin\AdminReportsController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');

Route::post('/login', [LoginController::class, 'login']);

// Password reset routes
Route::get('/forgot-password', [PasswordResetController::class, 'requestForm'])->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');

// Public registration disabled: only admins can create users
Route::get('/instructor/register', function () {
    return redirect()->route('login')->with('error', 'Public registration is disabled. Please contact your administrator.');
})->name('instructor.register.get');

// Keep route name but block public access unless protected elsewhere (e.g., admin panel)
Route::post('/instructor/register', function () {
    abort(403, 'Public registration is disabled.');
})->name('instructor.register.post');

Route::get('/auth/google/redirect', [SocialLoginController::class, 'redirectToGoogle'])->name('socialite.google.redirect');
Route::get('/auth/google/callback', [SocialLoginController::class, 'handleGoogleCallback']);

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// NEW: Route to handle code verification
Route::post('/email/verify', [VerificationController::class, 'verifyCode'])->middleware(['auth', 'throttle:6,1'])->name('verification.verify.code');

Route::post('/email/verification-notification', function (Request $request) {
    // Re-generate a new code and send it
    $verificationCode = random_int(100000, 999999);
    $expiresAt = Carbon\Carbon::now()->addMinutes(60); // Set expiry
    $request->user()->update([
        'email_verification_code' => $verificationCode,
        'email_verification_code_expires_at' => $expiresAt,
    ]);
    $request->user()->notify(new App\Notifications\VerifyEmailWithCode($verificationCode));
    return back()->with('status', 'A new verification code has been sent to your email address.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');


// --- Authenticated Routes (requires login) ---

// Handle logout (should be POST for security)
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Generic dashboard route (for any logged-in user, redirects based on role)
Route::middleware(['auth:web', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $user = Auth::user();
        switch ($user->role) {
            case 'super_admin':
            case 'school_admin':
                return redirect()->route('admin.dashboard');
            case 'instructor':
                return redirect()->route('instructor.dashboard');
            case 'student':
                // Placeholder: implement student dashboard route when available
                return redirect()->route('welcome');
            default:
                return redirect()->route('welcome')->with('error', 'Role not recognized');
        }
    })->name('dashboard');
});

// Admin specific routes (requires authentication AND admin role AND verification)
Route::middleware(['auth:web', 'role:super_admin,school_admin', 'verified'])->group(function () {
    // Admin Dashboard routes
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/dashboard/data', [AdminDashboardController::class, 'getDashboardData'])->name('admin.dashboard.data');
    Route::get('/admin/dashboard/chart-data', [AdminDashboardController::class, 'getChartData'])->name('admin.dashboard.chart-data');
    
    // Announcement routes
    Route::post('/admin/announcements', [AdminDashboardController::class, 'storeAnnouncement'])->name('admin.announcements.store');
    
    // User Management routes
    Route::get('/admin/user-management', [UserManagementController::class, 'index'])->name('admin.user_management');
    Route::get('/admin/users', [UserManagementController::class, 'index'])->name('admin.users.index');
    Route::get('/admin/users/search', [UserManagementController::class, 'search'])->name('admin.users.search');
    Route::post('/admin/users/bulk-import', [UserManagementController::class, 'bulkImport'])->name('admin.users.bulk-import');
    Route::get('/admin/users/create', [UserManagementController::class, 'create'])->name('admin.users.create');
    Route::post('/admin/users', [UserManagementController::class, 'store'])->name('admin.users.store');
    Route::get('/admin/users/{user}', [UserManagementController::class, 'show'])->name('admin.users.show');
    Route::get('/admin/users/{user}/edit', [UserManagementController::class, 'edit'])->name('admin.users.edit');
    Route::get('/admin/users/{user}/permissions', [UserManagementController::class, 'permissions'])->name('admin.users.permissions');
    Route::put('/admin/users/{user}', [UserManagementController::class, 'update'])->name('admin.users.update');
    Route::post('/admin/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('admin.users.reset-password');
    Route::delete('/admin/users/{user}', [UserManagementController::class, 'destroy'])->name('admin.users.destroy');

    // User Export/Import routes (mirror course management)
    Route::get('/admin/user-management/export', [UserManagementController::class, 'export'])->name('admin.userManagement.export');
    Route::get('/admin/user-management/export-excel', [UserManagementController::class, 'exportExcel'])->name('admin.userManagement.exportExcel');
    Route::post('/admin/user-management/import-json', [UserManagementController::class, 'importJson'])->name('admin.userManagement.import');

    // Reports & Logs routes (new AdminReportsController)
    // Main page
    Route::get('/admin/reports-logs', [AdminReportsController::class, 'index'])->name('admin.reports_logs');
    // Alias path used by the filter modal redirect
    Route::get('/admin/reports', [AdminReportsController::class, 'index']);
    // Chart data (AJAX) - new canonical name
    Route::post('/admin/reports/chart-data', [AdminReportsController::class, 'chartData'])->name('admin.reports.chartData');
    // System logs (AJAX) endpoint
    Route::post('/admin/reports/system-logs', [AdminReportsController::class, 'systemLogs'])->name('admin.reports.systemLogs');
    // Legacy alias some code may still call
    Route::match(['get','post'], '/admin/reports/data', [AdminReportsController::class, 'chartData'])->name('admin.reports.data');
    // Instructor lookup (AJAX) used by filter modal
    Route::post('/admin/reports/lookup-instructor', [AdminReportsController::class, 'lookupInstructor'])->name('admin.reports.lookupInstructor');
    // Accept GET as well for flexibility
    Route::get('/admin/reports/lookup-instructor', [AdminReportsController::class, 'lookupInstructor']);
    // Exports (deprecated placeholders to keep route names working)
    Route::get('/admin/reports/export', function () {
        return redirect()->route('admin.reports_logs')->with('error', 'Export is not available in this version.');
    })->name('admin.reports.export');
    Route::get('/admin/logs/export', function () {
        return redirect()->route('admin.reports_logs')->with('error', 'Logs export is not available in this version.');
    })->name('admin.logs.export');

    // Admin Account/Profile route (profile page only)
    Route::get('/admin/account', [AdminAccountController::class, 'index'])->name('admin.account');
    Route::put('/admin/account', [AdminAccountController::class, 'update'])->name('admin.account.update');
    Route::post('/admin/account/change-password', [AdminAccountController::class, 'changePassword'])->name('admin.account.changePassword');
    Route::post('/admin/account/upload-image', [AdminAccountController::class, 'uploadProfileImage'])->name('admin.account.uploadImage');
    Route::delete('/admin/account/image', [AdminAccountController::class, 'deleteProfileImage'])->name('admin.account.deleteImage');

    // Admin Settings routes (system & school settings)
    Route::get('/admin/settings', [SettingsController::class, 'edit'])->name('admin.settings');
    Route::post('/admin/settings/update', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::post('/admin/settings/select-school', [SettingsController::class, 'selectSchool'])->name('admin.settings.select-school');
    Route::post('/admin/settings/create-school', [SettingsController::class, 'createSchool'])->name('admin.settings.create-school');

    // Email Templates (Super Admin only)
    Route::get('/admin/email-templates', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'index'])->name('admin.email-templates.index');
    Route::get('/admin/email-templates/create', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'create'])->name('admin.email-templates.create');
    Route::post('/admin/email-templates', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'store'])->name('admin.email-templates.store');
    Route::get('/admin/email-templates/{emailTemplate}/edit', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'edit'])->name('admin.email-templates.edit');
    Route::put('/admin/email-templates/{emailTemplate}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'update'])->name('admin.email-templates.update');
    Route::delete('/admin/email-templates/{emailTemplate}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'destroy'])->name('admin.email-templates.destroy');
    
    // Course Management routes
    Route::get('/admin/course-management', [CourseManagementController::class, 'index'])->name('admin.course_management');
    // Legacy alias name with a distinct URI to avoid duplicate route collision
    Route::get('/admin/courseManagement', function () {
        return redirect()->route('admin.course_management');
    })->name('admin.courseManagement');
    // Legacy alias: some views expect admin.courseManagement.details
    Route::get('/admin/course-management/{course}/details-page', [CourseManagementController::class, 'showDetails'])->name('admin.courseManagement.details');
    Route::get('/admin/courses/search', [CourseManagementController::class, 'search'])->name('admin.courses.search');
    Route::get('/admin/courses', [CourseManagementController::class, 'index'])->name('admin.courses.index');
    Route::get('/admin/courses/create', [CourseManagementController::class, 'create'])->name('admin.courses.create');
    Route::post('/admin/courses', [CourseManagementController::class, 'store'])->name('admin.courses.store');
    Route::get('/admin/courses/find-instructor', [CourseManagementController::class, 'findInstructor'])->name('admin.courses.findInstructor');
    // Accept POST as well for fetch() calls that send JSON bodies
    Route::post('/admin/courses/find-instructor', [CourseManagementController::class, 'findInstructor']);
    Route::get('/admin/courses/{course}', [CourseManagementController::class, 'show'])->name('admin.courses.show');
    Route::get('/admin/courses/{course}/details', [CourseManagementController::class, 'showDetails'])->name('admin.courses.details');
    Route::get('/admin/courses/{course}/edit', [CourseManagementController::class, 'edit'])->name('admin.courses.edit');
    Route::put('/admin/courses/{course}', [CourseManagementController::class, 'update'])->name('admin.courses.update');
    Route::delete('/admin/courses/{course}', [CourseManagementController::class, 'destroy'])->name('admin.courses.destroy');

    // Export/Import routes
    Route::get('/admin/course-management/export', [CourseManagementController::class, 'export'])->name('admin.courseManagement.export');
    Route::get('/admin/course-management/export-excel', [CourseManagementController::class, 'exportExcel'])->name('admin.courseManagement.exportExcel');
    Route::post('/admin/course-management/import', [CourseManagementController::class, 'import'])->name('admin.courseManagement.import');

    // More legacy aliases mapped to the same controller actions
    Route::get('/admin/course-management/{course}', [CourseManagementController::class, 'show'])->name('admin.courseManagement.show');
    Route::get('/admin/course-management/{course}/edit', [CourseManagementController::class, 'edit'])->name('admin.courseManagement.edit');
    Route::put('/admin/course-management/{course}', [CourseManagementController::class, 'update'])->name('admin.courseManagement.update');
    Route::delete('/admin/course-management/{course}', [CourseManagementController::class, 'destroy'])->name('admin.courseManagement.delete');
    // Course-specific OTP endpoints
    Route::post('/admin/course-management/{course}/request-otp', [CourseManagementController::class, 'requestCourseOtp'])->name('admin.courseManagement.request-otp');
    Route::post('/admin/course-management/{course}/verify-otp', [CourseManagementController::class, 'verifyCourseOtp'])->name('admin.courseManagement.verify-otp');
    Route::get('/admin/course-management/find-instructor', [CourseManagementController::class, 'findInstructor'])->name('admin.courseManagement.findInstructor');
    Route::post('/admin/course-management/find-instructor', [CourseManagementController::class, 'findInstructor']);
    Route::post('/admin/course-management', [CourseManagementController::class, 'store'])->name('admin.courseManagement.store');

    // Admin materials view (stream inline if possible)
    Route::get('/admin/materials/{material}/view', [CourseManagementController::class, 'viewMaterial'])->name('admin.materials.view');

    // Admin assessments endpoints used by course-details modal
    Route::get('/admin/assessments/{assessment}/details', [CourseManagementController::class, 'assessmentDetails'])->name('admin.assessments.details');
    Route::get('/admin/assessments/{assessment}/file', function($assessmentId){
        // Reuse the material viewer if assessments attach a file via assessment_file_path
        $assessment = \App\Models\Assessment::findOrFail($assessmentId);
        if (!$assessment->assessment_file_path || !\Storage::disk('public')->exists($assessment->assessment_file_path)) {
            abort(404, 'File not found');
        }
        $path = $assessment->assessment_file_path;
        $mime = \Storage::disk('public')->mimeType($path);
        $stream = \Storage::disk('public')->readStream($path);
        $disposition = in_array($mime, ['application/pdf','image/png','image/jpeg','image/gif','text/plain']) ? 'inline' : 'attachment';
        return response()->stream(function() use ($stream) { fpassthru($stream); }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition.'; filename="'.basename($path).'"'
        ]);
    })->name('admin.assessments.file');

    // Help routes removed (no longer in layout)
    
    // Admin 2FA routes (for admin account management)
    Route::post('/admin/request-2fa', [TwoFactorController::class, 'request2FACode'])->name('admin.request-2fa');
    Route::post('/admin/verify-2fa', [TwoFactorController::class, 'verify2FACode'])->name('admin.verify-2fa');
    // Legacy aliases used by some blades/scripts
    Route::post('/admin/request2fa', [TwoFactorController::class, 'request2FACode'])->name('admin.request2fa');
    Route::post('/admin/verify2fa', [TwoFactorController::class, 'verify2FACode'])->name('admin.verify2fa');

    // Backward compatibility for older blade/js expecting admin.users.* route names
    Route::post('/admin/users/request-2fa', [TwoFactorController::class, 'request2FACode'])->name('admin.users.request-2fa');
    Route::post('/admin/users/verify-2fa', [TwoFactorController::class, 'verify2FACode'])->name('admin.users.verify-2fa');

    // Admin notification routes
    Route::get('/admin/notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications');
    Route::post('/admin/notifications/{id}/mark-as-read', [AdminNotificationController::class, 'markAsRead'])->name('admin.notifications.markAsRead');
    Route::post('/admin/notifications/mark-all-as-read', [AdminNotificationController::class, 'markAllAsRead'])->name('admin.notifications.markAllAsRead');
    // UI-friendly Notifications (per plan)
    Route::get('/notifications', [AdminUINotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/all', [AdminUINotificationController::class, 'all'])->name('notifications.all');
    Route::post('/notifications/mark/{id}', [AdminUINotificationController::class, 'markAsRead'])->name('notifications.mark');
    
});



// In routes/web.php
Route::middleware(['auth:web', 'role:instructor', 'verified'])->group(function () {
    Route::get('/instructor/dashboard', [InstructorController::class, 'index'])->name('instructor.dashboard');
    Route::get('/instructor/myCourse', [InstructorController::class, 'show'])->name('instructor.myCourse');
    Route::get('/instructor/myCourse/{course}', [InstructorController::class, 'showCourseDetails'])->name('instructor.courseDetails');
    Route::get('/instructor/myCourse/{course}/enrollee', [InstructorController::class, 'showCourseEnrollee'])->name('instructor.courseEnrollee');
    Route::post('/instructor/courses/{course}/add-students', [InstructorController::class, 'addStudents'])->name('instructor.addStudents');
    Route::delete('/instructor/courses/{course}/students/{student}', [InstructorController::class, 'removeStudent'])->name('instructor.removeStudent');
    
    Route::get('/instructor/studentManagement', [InstructorController::class, 'showStudentManagement'])->name('instructor.studenManagement');
    Route::get('/instructor/studentProgress', [InstructorController::class, 'showStudentProgress'])->name('instructor.studentProgress');
    Route::get('/instructor/studentDetails/{student?}', [InstructorController::class, 'showStudentDetails'])->name('instructor.studentDetails');
    Route::get('/instructor/submission/{submission}/details', [InstructorController::class, 'getSubmissionDetails'])->name('instructor.submissionDetails');
    Route::post('/instructor/submission/{submission}/grade', [InstructorController::class, 'updateGrade'])->name('instructor.updateGrade');
    Route::get('/instructor/submission/{submission}/download', [InstructorController::class, 'downloadSubmission'])->name('instructor.downloadSubmission');
    Route::post('/instructor/submitted-question/{submittedQuestion}/grade', [InstructorController::class, 'updateQuestionGrade'])->name('instructor.updateQuestionGrade');
    Route::post('/instructor/submitted-question/{submittedQuestion}/points', [InstructorController::class, 'updateQuestionPoints'])->name('instructor.updateQuestionPoints');
    Route::post('/instructor/assign-section/{student}', [InstructorController::class, 'assignSection'])->name('instructor.assignSection');
    Route::post('/instructor/course/{course}/section/create', [InstructorController::class, 'createSection'])->name('instructor.createSection');
    Route::post('/instructor/course/{course}/bulk-assign-section', [InstructorController::class, 'bulkAssignSection'])->name('instructor.bulkAssignSection');
    Route::post('/instructor/bulk-remove-students', [InstructorController::class, 'bulkRemoveStudents'])->name('instructor.bulkRemoveStudents');
    Route::delete('/instructor/section/{section}', [InstructorController::class, 'deleteSection'])->name('instructor.deleteSection');

    Route::get('/instructor/profile', [InstructorController::class, 'showProfile'])->name('instructor.showProfile');
    Route::put('/instructor/profile', [InstructorController::class, 'updateProfile'])->name('instructor.updateProfile');
    Route::post('/instructor/profile/upload-image', [InstructorController::class, 'uploadProfileImage'])->name('instructor.uploadProfileImage');
    Route::delete('/instructor/profile/image', [InstructorController::class, 'deleteProfileImage'])->name('instructor.deleteProfileImage');
    Route::post('/instructor/profile/change-password', [InstructorController::class, 'changePassword'])->name('instructor.changePassword');
    Route::get('/instructor/notifications', [InstructorController::class, 'getNotifications'])->name('instructor.notifications');
    Route::post('/instructor/notifications/mark-read', [InstructorController::class, 'markNotificationAsRead'])->name('instructor.markNotificationAsRead');
    Route::post('/instructor/notifications/mark-all-read', [InstructorController::class, 'markAllNotificationsAsRead'])->name('instructor.markAllNotificationsAsRead');
    
    Route::get('/course', [CourseController::class, 'create'])->name('course.create');
    Route::post('/course/createCourse', [CourseController::class, 'store'])->name('course.store');
    Route::get('/course/{course}', [CourseController::class, 'show'])->name('courses.show');
    Route::put('/course/{course}', [CourseController::class, 'update'])->name('course.update'); 

    Route::post('/topics', [TopicController::class, 'store'])->name('topics.store');
    Route::patch('/topics/{topic}', [TopicController::class, 'update'])->name('topics.update');

    Route::get('/course/{course}/materials', [MaterialController::class, 'create'])->name('materials.create');
    Route::post('/course/{course}/materials', [MaterialController::class, 'store'])->name('materials.store');
    Route::get('/materials/{material}', [MaterialController::class, 'show'])->name('materials.show');
    Route::get('/materials/{material}/edit', [MaterialController::class, 'edit'])->name('materials.edit');
    Route::put('/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
    Route::get('/materials/{material}/download', [MaterialController::class, 'download'])->name('materials.download');

    Route::get('/courses/{course}/assessments/withQ/{type}', [AssessmentController::class, 'createQuiz'])->name('assessments.create.quiz');
    Route::post('/courses/{course}/assessments/store/quiz', [AssessmentController::class, 'storeQuiz'])->name('assessments.store.quiz');
    Route::get('/courses/{course}/assessments/{assessment}/showQ', [AssessmentController::class, 'showQuiz'])->name('assessments.show.quiz');
    Route::get('/courses/{course}/assessments/{assessment}/edit/quizType', [AssessmentController::class, 'editQuiz'])->name('assessments.edit.quiz');
    Route::put('/courses/{course}/assessments/{assessment}/update/quizType', [AssessmentController::class, 'updateQuiz'])->name('assessments.update.quiz');
    Route::delete('/{assessment}', [AssessmentController::class, 'destroy'])->name('assessments.destroy');

    Route::get('/courses/{course}/assessments/withOutQ/{typeAct}', [AssessmentController::class, 'createAssignment'])->name('assessments.create.assignment');
    Route::post('/courses/{course}/assessments/store/assignment', [AssessmentController::class, 'storeAssignment'])->name('assessments.store.assignment');
    Route::get('/courses/{course}/assessments/{assessment}/showWoutQ', [AssessmentController::class, 'showAssignment'])->name('assessments.show.assignment');
    Route::get('/courses/{course}/assessments/{assessment}/edit/assignmentType', [AssessmentController::class, 'editAssignment'])->name('assessments.edit.assignment');
    Route::put('/courses/{course}/assessments/{assessment}/update/assignmentType', [AssessmentController::class, 'updateAssignment'])->name('assessments.update.assignment');
});

// Media serving route for profile images (works regardless of symlink/base path)
Route::get('/media/profile/{filename}', [InstructorController::class, 'serveProfileImage'])->name('media.profile');