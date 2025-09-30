<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use App\Models\Assessment;
use Illuminate\Support\Facades\Auth;
use App\Models\Program;
use App\Models\User;

class CourseManagementController extends Controller
{
    public function index()
    {
        // Server-side filtering and pagination
        $query = Course::with(['instructor','program'])->withCount('students');
        // Define allowed departments (could be moved to config if needed)
        $departmentsList = ['CCS','CAS','CHS','CEA','CTDE','CTHBM'];

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

        // filter by program (accept numeric id or program name/code)
        $programFilter = request()->input('program');
        if ($programFilter) {
            if (ctype_digit((string)$programFilter)) {
                $query->where('program_id', $programFilter);
            } else {
                $query->whereHas('program', function($pq) use ($programFilter) {
                    $pq->where('name', $programFilter);
                });
            }
        }

        // filter by status
        $statusFilter = request()->input('status');
        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        // filter by instructor department
        $departmentFilter = request()->input('department');
        if ($departmentFilter && in_array($departmentFilter, $departmentsList, true)) {
            $query->whereHas('instructor', function($dq) use ($departmentFilter) {
                $dq->where('department', $departmentFilter);
            });
        }

    $courses = $query->orderBy('created_at','desc')->paginate(10)->withQueryString();

        // load all programs for the create modal program select
        $programs = Program::orderBy('name')->get();
        return view('admin.course_management', compact('courses','programs'))
            ->with('departments', $departmentsList)
            ->with('selectedDepartment', $departmentFilter);
    }

    // GET /admin/courses/search -> reuse index filters
    public function search(Request $request)
    {
        return $this->index();
    }

    // GET /admin/courses/create -> no separate page; redirect to management
    public function create(Request $request)
    {
        return redirect()->route('admin.course_management');
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

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully',
            'course' => $course
        ]);
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
            // Make course_code optional on edit; only validate uniqueness when provided
            'course_code' => 'nullable|string|max:50|unique:courses,course_code,' . $course->id,
            'status' => 'required|in:published,draft,archived',
            'program_id' => 'nullable|exists:programs,id',
            'description' => 'nullable|string',
            'credits' => 'nullable|numeric|min:0'
        ]);

        // Preserve existing course_code if not provided (avoid nulling it out)
        if (!array_key_exists('course_code', $validated) || $validated['course_code'] === null || $validated['course_code'] === '') {
            unset($validated['course_code']);
        }
        $course->fill($validated);
        $course->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'course' => $course->fresh('program')]);
        }
        return redirect()->route('admin.courseManagement')->with('success', 'Course updated successfully.');
    }

    public function destroy($courseId)
    {
        $course = Course::findOrFail($courseId);
        $course->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Stream / view a material (primarily PDF) in a new browser tab.
     * Falls back to download if file type not directly displayable.
     */
    public function viewMaterial(Material $material)
    {
        // Security: ensure relationship to a course (already implicit) - additional checks could be added.
        if (!$material->file_path || !\Storage::disk('public')->exists($material->file_path)) {
            abort(404, 'Material file not found');
        }
        $path = $material->file_path;
        $mime = \Storage::disk('public')->mimeType($path);
        $stream = \Storage::disk('public')->readStream($path);
        $disposition = in_array($mime, ['application/pdf','image/png','image/jpeg','image/gif','text/plain']) ? 'inline' : 'attachment';
        return response()->stream(function() use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition.'; filename="'.basename($path).'"'
        ]);
    }

    /**
     * Provide assessment full details including questions & options for modal display (AJAX JSON).
     */
    public function assessmentDetails(Assessment $assessment)
    {
        $assessment->load(['questions.options']);
        $payload = [
            'id' => $assessment->id,
            'title' => $assessment->title,
            'type' => $assessment->type,
            'description' => $assessment->description,
            'duration_minutes' => $assessment->duration_minutes,
            'available_at' => $assessment->available_at?->toDateTimeString(),
            'unavailable_at' => $assessment->unavailable_at?->toDateTimeString(),
            'has_file' => (bool)$assessment->assessment_file_path,
            'questions' => $assessment->questions->map(function($q){
                return [
                    'id' => $q->id,
                    'text' => $q->question_text,
                    'type' => $q->question_type,
                    'points' => $q->points,
                    'order' => $q->order,
                    'correct_answer' => $q->correct_answer,
                    'options' => $q->options->map(function($o){
                        return [
                            'id' => $o->id,
                            'text' => $o->option_text,
                            'order' => $o->option_order,
                            // expose correctness flag if schema supports it
                            'is_correct' => (bool)($o->is_correct ?? false),
                        ];
                    })->values(),
                ];
            })->values(),
        ];
        return response()->json(['success' => true, 'assessment' => $payload]);
    }

    /**
     * Stream / view attached assessment file if present.
     */
    public function viewAssessmentFile(Assessment $assessment)
    {
        if (!$assessment->assessment_file_path || !\Storage::disk('public')->exists($assessment->assessment_file_path)) {
            abort(404, 'Assessment file not found');
        }
        $path = $assessment->assessment_file_path;
        $mime = \Storage::disk('public')->mimeType($path);
        $stream = \Storage::disk('public')->readStream($path);
        $disposition = in_array($mime, ['application/pdf','image/png','image/jpeg','image/gif','text/plain']) ? 'inline' : 'attachment';
        return response()->stream(function() use ($stream) { fpassthru($stream); }, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => $disposition.'; filename="'.basename($path).'"'
        ]);
    }
}
