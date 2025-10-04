<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Assessment;
use App\Models\SubmittedAssessment;
use App\Models\School;
use App\Models\Announcement;
use App\Models\Program;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Authorization check
        if (!$user || (!$user->isSuperAdmin() && !$user->isSchoolAdmin())) {
            abort(403, 'Unauthorized access to admin dashboard');
        }
        
    // Determine active school for scoping
    $activeSchool = $this->getActiveSchool();
        $activeSchoolId = $activeSchool ? $activeSchool->id : null;
        
        // Check if SuperAdmin needs to create/select a school
        $needsSchoolSelection = false;
        if ($user->isSuperAdmin()) {
            $totalSchools = School::count();
            if ($totalSchools === 0) {
                // No schools exist - redirect to settings to create first school
                return redirect()->route('admin.settings')
                    ->with('info', 'Welcome! Please create your first school to begin using the system.');
            } elseif (!$activeSchool) {
                // Schools exist but none selected
                $needsSchoolSelection = true;
            }
        }
        
        // Dashboard statistics (scoped by school)
        $stats = $needsSchoolSelection ? [] : $this->getDashboardStats($activeSchoolId);
        $recentActivities = $needsSchoolSelection ? [] : $this->getRecentActivities($activeSchoolId);
        $systemHealth = $this->getSystemHealth();
        $availableSchools = $user->isSuperAdmin() ? School::orderBy('name')->get() : collect();
        $announcements = $needsSchoolSelection ? collect() : $this->getAnnouncements($activeSchoolId);
        $programs = Program::orderBy('name')->get();

        return view('admin.admin_dashboard', compact(
            'stats', 
            'recentActivities', 
            'systemHealth', 
            'activeSchool',
            'availableSchools',
            'needsSchoolSelection',
            'announcements',
            'programs'
        ));
    }

    /**
     * Get active school based on user role and session
     */
    private function getActiveSchool()
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }
        
        if ($user->isSuperAdmin()) {
            // Super Admin: Use active_school from session only (no implicit default)
            $activeSchoolId = Session::get('active_school');
            if ($activeSchoolId) {
                $school = School::find($activeSchoolId);
                // Verify school still exists
                return $school;
            }
            return null;
        } elseif ($user->isSchoolAdmin()) {
            // School Admin: Use their assigned school
            if (!$user->school_id) {
                return null; // No school assigned
            }
            return $user->school;
        }
        
        return null;
    }

    /**
     * Get dashboard statistics (scoped by school)
     */
    private function getDashboardStats($schoolId = null)
    {
        // Base queries
        $userQuery = User::query();
        $courseQuery = Course::query();
        $assessmentQuery = Assessment::query();
        $submissionQuery = SubmittedAssessment::query();
        
        // Apply school filtering if schoolId is provided
        if ($schoolId) {
            $userQuery->where('school_id', $schoolId);
            
            // Courses are related to schools through their instructors
            $courseQuery->whereHas('instructor', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
            
            // Assessments are related to schools through course -> instructor
            $assessmentQuery->whereHas('course.instructor', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
            
            // Submissions are related to schools through assessment -> course -> instructor
            $submissionQuery->whereHas('assessment.course.instructor', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }
        
        // Active users (Currently Online) based on sessions.last_activity within last 15 minutes
        $activeUsersNow = 0;
        try {
            $cutoff = Carbon::now()->subMinutes(15)->getTimestamp();
            if (\Schema::hasTable('sessions')) {
                $sessionRows = DB::table('sessions')
                    ->select(['user_id', 'payload', 'last_activity'])
                    ->where('last_activity', '>=', $cutoff)
                    ->get();

                $userIds = collect();
                foreach ($sessionRows as $row) {
                    if (!is_null($row->user_id)) {
                        $userIds->push((int) $row->user_id);
                        continue;
                    }
                    // Fallback: try to parse user id from payload (serialized string)
                    if (!empty($row->payload)) {
                        $payload = @base64_decode($row->payload, true);
                        if ($payload === false) { $payload = $row->payload; }
                        if (preg_match('/"user_id";i:(\d+)/', $payload, $m)) {
                            $userIds->push((int) $m[1]);
                        } elseif (preg_match('/"id";i:(\d+)/', $payload, $m2)) {
                            $userIds->push((int) $m2[1]);
                        }
                    }
                }
                $userIds = $userIds->unique()->values();

                if ($userIds->isNotEmpty()) {
                    if ($schoolId) {
                        $activeUsersNow = User::whereIn('id', $userIds)
                            ->where('school_id', $schoolId)
                            ->count();
                    } else {
                        $activeUsersNow = $userIds->count();
                    }
                }
            }

            // Fallback if sessions not available or zero: use users.last_activity_at within 15 minutes
            if ($activeUsersNow === 0 && \Schema::hasColumn('users', 'last_activity_at')) {
                $activeUsersNow = (clone $userQuery)
                    ->whereNotNull('last_activity_at')
                    ->where('last_activity_at', '>=', Carbon::now()->subMinutes(15))
                    ->count();
            }
        } catch (\Throwable $e) {
            // If anything fails, keep zero (safe default)
            $activeUsersNow = 0;
        }

        return [
            'total_users' => $userQuery->count(),
            'total_courses' => $courseQuery->count(),
            'total_instructors' => (clone $userQuery)->where('role', 'instructor')->count(),
            'total_students' => (clone $userQuery)->where('role', 'student')->count(),
            'active_courses' => (clone $courseQuery)->where('status', 'published')->count(),
            'total_assessments' => $assessmentQuery->count(),
            'submitted_assessments' => $submissionQuery->count(),
            'recent_registrations' => (clone $userQuery)->where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'active_users_today' => (clone $userQuery)->where('last_login_at', '>=', Carbon::today())->count(),
            'active_users_now' => $activeUsersNow,
            'completed_assessments_this_month' => (clone $submissionQuery)->where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
            // Security & Health metrics
            'pending_users' => (clone $userQuery)->where('status', 'inactive')->count(),
            'pending_courses' => (clone $courseQuery)->where('status', 'draft')->count(),
            'failed_logins_24h' => $this->getFailedLoginsCount(),
        ];
    }

    /**
     * Get recent activities (scoped by school)
     */
    private function getRecentActivities($schoolId = null)
    {
        $activities = [];

        // Base queries for recent activities
        $userQuery = User::query();
        $courseQuery = Course::with('instructor');
        $submissionQuery = SubmittedAssessment::with(['student', 'assessment']);
        
        // Apply school filtering if schoolId is provided
        if ($schoolId) {
            $userQuery->where('school_id', $schoolId);
            
            // Courses are related to schools through their instructors
            $courseQuery->whereHas('instructor', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
            
            // Submissions are related to schools through assessment -> course -> instructor
            $submissionQuery->whereHas('assessment.course.instructor', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }

        // Recent user registrations
        $recentUsers = $userQuery->orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentUsers as $user) {
            $activities[] = [
                'type' => 'user_registration',
                'description' => "New {$user->role} '{$user->name}' registered",
                'time' => $user->created_at,
                'icon' => 'fas fa-user-plus',
            ];
        }

        // Recent course creation
        $recentCourses = $courseQuery->orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentCourses as $course) {
            $activities[] = [
                'type' => 'course_creation',
                'description' => "Course '{$course->title}' created by {$course->instructor->name}",
                'time' => $course->created_at,
                'icon' => 'fas fa-book',
            ];
        }

        // Recent assessment submissions
        $recentSubmissions = $submissionQuery->orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentSubmissions as $submission) {
            $activities[] = [
                'type' => 'assessment_submission',
                'description' => "{$submission->student->name} submitted '{$submission->assessment->title}'",
                'time' => $submission->created_at,
                'icon' => 'fas fa-file-alt',
            ];
        }

        // Sort by time descending and return top 5
        usort($activities, fn($a, $b) => $b['time']->timestamp - $a['time']->timestamp);

        return array_slice($activities, 0, 5);
    }

    /**
     * Get system health information
     */
    private function getSystemHealth()
    {
        // Pull max storage from system config (bytes). Fallback to 100GB if not configured.
        $configured = \App\Models\SystemConfig::get('max_storage', null);
        // Accept human-readable like "100 GB" or raw bytes
        $totalStorage = $configured ? $this->parseByteString($configured) : (100 * 1024 * 1024 * 1024);
        $usedStorage = $this->calculateUsedStorage();
        $storagePercentage = ($usedStorage / $totalStorage) * 100;

        return [
            'storage_used' => $this->formatBytes($usedStorage),
            'storage_total' => $this->formatBytes($totalStorage),
            'storage_percentage' => round($storagePercentage, 1),
            'server_uptime' => $this->getServerUptime(),
            'last_backup' => $this->getLastBackupTime(),
            'database_size' => $this->getDatabaseSize(),
            'total_files' => $this->getTotalFiles(),
        ];
    }

    /**
     * Calculate used storage space
     */
    private function calculateUsedStorage()
    {
        try {
            $publicSize = $this->getDirectorySize(public_path());
            $storageSize = $this->getDirectorySize(storage_path());
            return $publicSize + $storageSize;
        } catch (\Exception $e) {
            // Return default estimate if calculation fails
            return 15.2 * 1024 * 1024 * 1024; // 15.2GB
        }
    }

    /**
     * Get directory size recursively
     */
    private function getDirectorySize($directory)
    {
        $size = 0;
        if (is_dir($directory)) {
            try {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory)) as $file) {
                    $size += $file->getSize();
                }
            } catch (\Exception $e) {
                // Handle permission errors or other issues
                return 0;
            }
        }
        return $size;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes($size, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        return round($size, $precision) . ' ' . $units[$i];
    }

    /**
     * Parse strings like "100GB", "100 GB", "512 mb" into bytes; numbers are bytes.
     */
    private function parseByteString($value): int
    {
        if (is_numeric($value)) return (int)$value;
        $str = trim(strtolower((string)$value));
        if ($str === '') return 0;
        if (preg_match('/^([\d.]+)\s*(b|kb|mb|gb|tb)$/i', $str, $m)) {
            $num = (float)$m[1];
            $unit = strtolower($m[2]);
            $pow = ['b'=>0,'kb'=>1,'mb'=>2,'gb'=>3,'tb'=>4][$unit] ?? 0;
            return (int) round($num * pow(1024, $pow));
        }
        // fallback: try to cast
        return (int) $value;
    }

    /**
     * Get server uptime (mock data for now)
     */
    private function getServerUptime()
    {
        return function_exists('sys_getloadavg') ? '99.9%' : 'N/A';
    }

    /**
     * Get last backup time (mock data for now)
     */
    private function getLastBackupTime()
    {
        return Carbon::now()->subHours(2);
    }

    /**
     * Get database size information
     */
    private function getDatabaseSize()
    {
        try {
            // Get database size from information_schema (MySQL/MariaDB)
            $result = DB::select("
                SELECT 
                    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'db_size_mb'
                FROM information_schema.tables 
                WHERE table_schema = ?
            ", [config('database.connections.' . config('database.default') . '.database')]);
            
            if (!empty($result)) {
                return round($result[0]->db_size_mb, 2) . ' MB';
            }
        } catch (\Exception $e) {
            // Fallback if query fails
        }
        
        return 'N/A';
    }

    /**
     * Get total number of files in storage
     */
    private function getTotalFiles()
    {
        try {
            $count = 0;
            $directories = [storage_path('app'), public_path()];
            
            foreach ($directories as $directory) {
                if (is_dir($directory)) {
                    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));
                    foreach ($iterator as $file) {
                        if ($file->isFile()) {
                            $count++;
                        }
                    }
                }
            }
            
            return number_format($count);
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get dashboard data for API/AJAX requests
     */
    public function getDashboardData(Request $request)
    {
        $activeSchool = $this->getActiveSchool();
        $activeSchoolId = $activeSchool ? $activeSchool->id : null;
        
        return response()->json([
            'stats' => $this->getDashboardStats($activeSchoolId),
            'activities' => $this->getRecentActivities($activeSchoolId),
            'health' => $this->getSystemHealth(),
        ]);
    }

    /**
     * Get chart data for dashboard visualizations
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'users');
        $activeSchool = $this->getActiveSchool();
        $activeSchoolId = $activeSchool ? $activeSchool->id : null;
        
        switch ($type) {
            case 'users':
                return $this->getUsersChartData($activeSchoolId);
            case 'courses':
                return $this->getCoursesChartData($activeSchoolId);
            case 'assessments':
                return $this->getAssessmentsChartData($activeSchoolId);
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    /**
     * Get users chart data (scoped by school)
     */
    private function getUsersChartData($schoolId = null)
    {
        $last30Days = collect(range(0, 29))->map(function ($i) use ($schoolId) {
            $date = Carbon::now()->subDays($i);
            $query = User::whereDate('created_at', $date);
            
            if ($schoolId) {
                $query->where('school_id', $schoolId);
            }
            
            return [
                'date' => $date->format('Y-m-d'),
                'count' => $query->count()
            ];
        })->reverse()->values();

        return response()->json([
            'labels' => $last30Days->pluck('date'),
            'data' => $last30Days->pluck('count'),
        ]);
    }

    /**
     * Get courses chart data (scoped by school)
     */
    private function getCoursesChartData($schoolId = null)
    {
        $query = Course::select('status', DB::raw('count(*) as count'));
        
        if ($schoolId) {
            $query->whereHas('instructor', function($q) use ($schoolId) {
                $q->where('school_id', $schoolId);
            });
        }
        
        $coursesByStatus = $query->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'labels' => $coursesByStatus->keys(),
            'data' => $coursesByStatus->values(),
        ]);
    }

    /**
     * Get assessments chart data (scoped by school)
     */
    private function getAssessmentsChartData($schoolId = null)
    {
        $last7Days = collect(range(0, 6))->map(function ($i) use ($schoolId) {
            $date = Carbon::now()->subDays($i);
            $query = SubmittedAssessment::whereDate('created_at', $date);
            
            if ($schoolId) {
                $query->whereHas('assessment.course.instructor', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            }
            
            return [
                'date' => $date->format('M d'),
                'submissions' => $query->count()
            ];
        })->reverse()->values();

        return response()->json([
            'labels' => $last7Days->pluck('date'),
            'data' => $last7Days->pluck('submissions'),
        ]);
    }

    /**
     * Get announcements for dashboard
     */
    private function getAnnouncements($schoolId = null)
    {
        return Announcement::active()
            ->forSchool($schoolId)
            ->with('author')
            ->orderBy('is_pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }

    /**
     * Get failed login attempts count (placeholder - you might want to implement proper tracking)
     */
    private function getFailedLoginsCount()
    {
        try {
            $since = Carbon::now()->subHours(24);
            if (\Schema::hasTable('failed_logins')) {
                return DB::table('failed_logins')
                    ->where('created_at', '>=', $since)
                    ->count();
            }
            return 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Store new announcement
     */
    public function storeAnnouncement(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'is_pinned' => 'boolean',
            'expires_at' => 'nullable|date|after:now'
        ]);

        $user = Auth::user();
        $activeSchool = $this->getActiveSchool();

        Announcement::create([
            'title' => $request->title,
            'message' => $request->message,
            'author_id' => $user->id,
            'school_id' => $activeSchool ? $activeSchool->id : null,
            'is_pinned' => $request->boolean('is_pinned'),
            'expires_at' => $request->expires_at,
            'status' => 'active'
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Announcement posted successfully!']);
        }

        return back()->with('success', 'Announcement posted successfully!');
    }
}