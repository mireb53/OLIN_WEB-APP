<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Assessment;
use App\Models\SubmittedAssessment;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function index()
    {
        // Dashboard statistics
        $stats = $this->getDashboardStats();
        $recentActivities = $this->getRecentActivities();
        $systemHealth = $this->getSystemHealth();

        return view('admin.admin_dashboard', compact('stats', 'recentActivities', 'systemHealth'));
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        return [
            'total_users' => User::count(),
            'total_courses' => Course::count(),
            'total_instructors' => User::where('role', 'instructor')->count(),
            'total_students' => User::where('role', 'student')->count(),
            'active_courses' => Course::where('status', 'active')->count(),
            'total_assessments' => Assessment::count(),
            'submitted_assessments' => SubmittedAssessment::count(),
            'recent_registrations' => User::where('created_at', '>=', Carbon::now()->subDays(7))->count(),
            'active_users_today' => User::where('last_login_at', '>=', Carbon::today())->count(),
            'completed_assessments_this_month' => SubmittedAssessment::where('created_at', '>=', Carbon::now()->startOfMonth())->count(),
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities()
    {
        $activities = [];

        // Recent user registrations
        $recentUsers = User::orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentUsers as $user) {
            $activities[] = [
                'type' => 'user_registration',
                'description' => "New {$user->role} '{$user->name}' registered",
                'time' => $user->created_at,
                'icon' => 'fas fa-user-plus',
            ];
        }

        // Recent course creation
        $recentCourses = Course::with('instructor')->orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentCourses as $course) {
            $activities[] = [
                'type' => 'course_creation',
                'description' => "Course '{$course->title}' created by {$course->instructor->name}",
                'time' => $course->created_at,
                'icon' => 'fas fa-book',
            ];
        }

        // Recent assessment submissions
        $recentSubmissions = SubmittedAssessment::with(['student', 'assessment'])
            ->orderBy('created_at', 'desc')->limit(3)->get();
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
        return response()->json([
            'stats' => $this->getDashboardStats(),
            'activities' => $this->getRecentActivities(),
            'health' => $this->getSystemHealth(),
        ]);
    }

    /**
     * Get chart data for dashboard visualizations
     */
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'users');
        
        switch ($type) {
            case 'users':
                return $this->getUsersChartData();
            case 'courses':
                return $this->getCoursesChartData();
            case 'assessments':
                return $this->getAssessmentsChartData();
            default:
                return response()->json(['error' => 'Invalid chart type'], 400);
        }
    }

    /**
     * Get users chart data
     */
    private function getUsersChartData()
    {
        $last30Days = collect(range(0, 29))->map(function ($i) {
            $date = Carbon::now()->subDays($i);
            return [
                'date' => $date->format('Y-m-d'),
                'count' => User::whereDate('created_at', $date)->count()
            ];
        })->reverse()->values();

        return response()->json([
            'labels' => $last30Days->pluck('date'),
            'data' => $last30Days->pluck('count'),
        ]);
    }

    /**
     * Get courses chart data
     */
    private function getCoursesChartData()
    {
        $coursesByStatus = Course::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'labels' => $coursesByStatus->keys(),
            'data' => $coursesByStatus->values(),
        ]);
    }

    /**
     * Get assessments chart data
     */
    private function getAssessmentsChartData()
    {
        $last7Days = collect(range(0, 6))->map(function ($i) {
            $date = Carbon::now()->subDays($i);
            return [
                'date' => $date->format('M d'),
                'submissions' => SubmittedAssessment::whereDate('created_at', $date)->count()
            ];
        })->reverse()->values();

        return response()->json([
            'labels' => $last7Days->pluck('date'),
            'data' => $last7Days->pluck('submissions'),
        ]);
    }
}