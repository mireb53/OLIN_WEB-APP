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

        return view('admin.admin_dashboard', compact(
            'stats', 
            'recentActivities', 
            'systemHealth', 
            'activeSchool',
            'availableSchools',
            'needsSchoolSelection'
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
            // Super Admin: Use active_school from session
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
            'completed_assessments_this_month' => (clone $submissionQuery)->where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
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
        $totalStorage = 20 * 1024 * 1024 * 1024; // 20GB
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
}