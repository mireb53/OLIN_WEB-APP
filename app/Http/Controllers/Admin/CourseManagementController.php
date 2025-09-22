<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class CourseManagementController extends Controller
{
    public function index()
    {
        // Server-side filtering and pagination
        $query = Course::with(['instructor','program'])->withCount('students');

        // search q: course title, id, instructor name, program name
        $q = request()->input('q');
        if ($q) {
            $query->where(function($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('id', 'like', "%{$q}%")
                    ->orWhereHas('instructor', function($iq) use ($q) { $iq->where('name','like',"%{$q}%"); })
                    ->orWhereHas('program', function($pq) use ($q) { $pq->where('name','like',"%{$q}%"); });
            });
        }

        // filter by program id
        $programFilter = request()->input('program');
        if ($programFilter) {
            $query->where('program_id', $programFilter);
        }

        // filter by status
        $statusFilter = request()->input('status');
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $courses = $query->orderBy('created_at','desc')->paginate(10)->withQueryString();

        // load all programs for the create modal program select
        $programs = Program::orderBy('name')->get();
        return view('admin.course_management', compact('courses','programs'));
    }

    /**
     * AJAX: find instructor by email (returns id and name) for the create modal
     */
    public function findInstructor(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');
        $user = User::where('email', $email)->where('role', 'instructor')->first();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Instructor not found.'], 404);
        }
        return response()->json(['success' => true, 'instructor' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email]]);
    }

    /**
     * Store a new course as admin (associates to instructor)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'course_code' => 'required|string|max:50|unique:courses,course_code',
            'status' => 'required|in:published,draft,archived',
            'program_id' => 'required|exists:programs,id',
            'description' => 'nullable|string',
            'credits' => 'nullable|numeric|min:0',
            'instructor_id' => 'required|exists:users,id',
        ]);

        $course = Course::create($validated);

        // load relations for client-side append
        $course->load('instructor','program');

        return response()->json(['success' => true, 'course' => $course]);
    }

    public function show($courseId)
    {
        $course = \App\Models\Course::with([
            'instructor',
            'program',
            'topics',
            'materials',
            'assessments',
            'students'
        ])->findOrFail($courseId);

        // If the request expects JSON (AJAX/modal), return JSON payload
        if (request()->wantsJson() || request()->ajax()) {
            // Format students for modal (only id, name, email)
            $course->students = $course->students->map(function($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'email' => $s->email
                ];
            })->values();
            return response()->json(['success' => true, 'course' => $course]);
        }
        // For direct page access, redirect back to course management (we now use modals)
        return redirect()->route('admin.course_management');
    }

    /**
     * Show a full page view of the course details (same content as modal).
     */
    public function showDetails($courseId)
    {
        $course = \App\Models\Course::with([
            'instructor',
            'program',
            'topics',
            'materials',
            'assessments',
            'students'
        ])->findOrFail($courseId);

        return view('admin.courses.course-details', compact('course'));
    }

    public function edit($courseId)
    {
        $course = Course::with('instructor')->findOrFail($courseId);
        // Return JSON for modal edit; redirect to management if accessed directly
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'course' => $course]);
        }
        return redirect()->route('admin.course_management');
    }

    public function update(Request $request, $courseId)
    {
        $course = Course::findOrFail($courseId);
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'course_code' => 'required|string|max:50|unique:courses,course_code,' . $course->id,
            'status' => 'required|in:published,draft,archived',
            'program_id' => 'required|exists:programs,id',
            'description' => 'nullable|string',
            'credits' => 'nullable|numeric|min:0',
            'instructor_id' => 'required|exists:users,id',
        ]);

        $course->update($validated);

        // load relations for client-side update
        $course->load('instructor','program');

        return response()->json(['success' => true, 'course' => $course]);
    }

    public function destroy($courseId)
    {
        $course = Course::findOrFail($courseId);
        $course->delete();
        return response()->json(['success' => true]);
    }
}

