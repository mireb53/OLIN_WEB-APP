<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User;
use App\Models\School;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $schoolId = $request->input('school_id');

        // Filter by school if SchoolAdmin
        if ($user->role === 'SchoolAdmin') {
            $userSchoolId = $user->school_id;
            $courses = Course::whereHas('program', function ($q) use ($userSchoolId) {
                $q->where('school_id', $userSchoolId);
            })->get();
            $instructors = User::where('school_id', $userSchoolId)->where('role', 'Instructor')->get();
            $logs = ActivityLog::with('user')
                ->whereHas('user', fn($q) => $q->where('school_id', $userSchoolId))
                ->latest()->paginate(10);
            $schools = collect(); // empty
        } else {
            // SuperAdmin → all schools
            $schools = School::all();
            
            $coursesQuery = Course::query();
            $instructorsQuery = User::where('role', 'Instructor');
            $logsQuery = ActivityLog::with('user');
            
            if ($schoolId) {
                $coursesQuery->whereHas('program', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
                $instructorsQuery->where('school_id', $schoolId);
                $logsQuery->whereHas('user', fn($q) => $q->where('school_id', $schoolId));
            }
            
            $courses = $coursesQuery->get();
            $instructors = $instructorsQuery->get();
            $logs = $logsQuery->latest()->paginate(10);
        }

        return view('admin.reports_logs', compact('courses', 'instructors', 'logs', 'schools'));
    }

    /**
     * Fetch data for charts.
     */
    public function getReportData(Request $request)
    {
        $user = Auth::user();
        $schoolId = $request->input('school_id');
        $dateRange = $request->input('date_range', 30); // Default to 30 days
        $courseId = $request->input('course_id');

        $queryScope = function ($query) use ($user, $schoolId) {
            if ($user->role === 'SuperAdmin' && $schoolId) {
                $query->whereHas('program', function ($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                });
            } elseif ($user->role === 'SchoolAdmin') {
                $query->whereHas('program', function ($q) use ($user) {
                    $q->where('school_id', $user->school_id);
                });
            }
        };

        // Student Progress Data (e.g., count of submitted assessments over time)
        $studentProgressQuery = DB::table('submitted_assessments')
            ->join('users', 'submitted_assessments.user_id', '=', 'users.id')
            ->select(DB::raw('DATE(submitted_assessments.created_at) as date'), DB::raw('count(*) as count'))
            ->where('users.role', 'Student')
            ->where('submitted_assessments.created_at', '>=', now()->subDays($dateRange));
        
        if ($user->role === 'SuperAdmin' && $schoolId) {
            $studentProgressQuery->where('users.school_id', $schoolId);
        } elseif ($user->role === 'SchoolAdmin') {
            $studentProgressQuery->where('users.school_id', $user->school_id);
        }
        if ($courseId) {
            $studentProgressQuery->where('submitted_assessments.course_id', $courseId);
        }

        $studentProgress = $studentProgressQuery->groupBy('date')->orderBy('date')->get();

        // Instructor Activity Data (e.g., count of created materials)
        $instructorActivityQuery = DB::table('materials')
            ->join('users', 'materials.user_id', '=', 'users.id')
            ->select('users.name as instructor_name', DB::raw('count(materials.id) as count'))
            ->where('materials.created_at', '>=', now()->subDays($dateRange));

        if ($user->role === 'SuperAdmin' && $schoolId) {
            $instructorActivityQuery->where('users.school_id', $schoolId);
        } elseif ($user->role === 'SchoolAdmin') {
            $instructorActivityQuery->where('users.school_id', $user->school_id);
        }
        
        $instructorActivity = $instructorActivityQuery->groupBy('instructor_name')->get();

        // Course Completion Data
        $courseCompletionQuery = Course::withCount('students');
        $queryScope($courseCompletionQuery);
        if ($courseId) {
            $courseCompletionQuery->where('id', $courseId);
        }
        $courseCompletion = $courseCompletionQuery->get(['title', 'students_count']);

        return response()->json([
            'studentProgress' => [
                'labels' => $studentProgress->pluck('date'),
                'data' => $studentProgress->pluck('count'),
            ],
            'instructorActivity' => [
                'labels' => $instructorActivity->pluck('instructor_name'),
                'data' => $instructorActivity->pluck('count'),
            ],
            'courseCompletion' => [
                'labels' => $courseCompletion->pluck('title'),
                'data' => $courseCompletion->pluck('students_count'),
            ],
        ]);
    }

    /**
     * Export reports to a file.
     */
    public function exportReports()
    {
        // Placeholder for report export logic
        // Example: return Excel::download(new ReportsExport, 'reports.csv');
    }

    /**
     * Export logs to a file.
     */
    public function exportLogs()
    {
        // Placeholder for log export logic
        // Example: return Excel::download(new LogsExport, 'logs.csv');
    }
}
