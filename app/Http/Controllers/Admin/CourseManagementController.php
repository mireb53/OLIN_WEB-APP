<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use App\Models\Program;
use App\Models\User;

class CourseManagementController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // Determine active school context (super_admin selects, school_admin fixed)
        $activeSchoolId = null;
        if ($user->role === 'super_admin') {
            $activeSchoolId = session('active_school');
        } elseif ($user->role === 'school_admin') {
            $activeSchoolId = $user->school_id;
        }

        $query = Course::with(['instructor','program'])->withCount('students');

        // Scope courses to active school if set (join via instructor->school_id OR program->school_id)
        if ($activeSchoolId) {
            $query->where(function($scoped) use ($activeSchoolId) {
                $scoped->whereHas('instructor', function($q) use ($activeSchoolId) {
                    $q->where('school_id', $activeSchoolId);
                })->orWhereHas('program', function($q) use ($activeSchoolId) {
                    $q->where('school_id', $activeSchoolId);
                });
            });
        }

        // Search
        if ($q = $request->input('q')) {
            $query->where(function($sub) use ($q) {
                $sub->where('title', 'like', "%{$q}%")
                    ->orWhere('id', 'like', "%{$q}%")
                    ->orWhereHas('instructor', function($iq) use ($q) { $iq->where('name','like',"%{$q}%"); })
                    ->orWhereHas('program', function($pq) use ($q) { $pq->where('name','like',"%{$q}%"); });
            });
        }

        // Program filter (ensure program belongs to active school if any)
        if ($programFilter = $request->input('program')) {
            $query->where('program_id', $programFilter);
        }

        // Status filter
        if ($statusFilter = $request->input('status')) {
            $query->where('status', $statusFilter);
        }

        $courses = $query->orderBy('created_at','desc')->paginate(10)->withQueryString();

        // Programs limited to active school (if set)
        $programsQuery = Program::query();
        if ($activeSchoolId) {
            $programsQuery->where('school_id', $activeSchoolId);
        }
        $programs = $programsQuery->orderBy('name')->get();

        return view('admin.course_management', compact('courses','programs'));
    }

    /**
     * AJAX: find instructor by email (returns id and name) for the create modal
     */
    public function findInstructor(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');

        $user = Auth::user();
        $activeSchoolId = null;
        if ($user->role === 'super_admin') {
            $activeSchoolId = session('active_school');
        } elseif ($user->role === 'school_admin') {
            $activeSchoolId = $user->school_id;
        }

        $instructorQuery = User::where('email', $email)->where('role', 'instructor');
        if ($activeSchoolId) {
            $instructorQuery->where('school_id', $activeSchoolId);
        }
        $instructor = $instructorQuery->first();
        if (!$instructor) {
            return response()->json(['success' => false, 'message' => 'Instructor not found.'], 404);
        }
        return response()->json(['success' => true, 'instructor' => ['id' => $instructor->id, 'name' => $instructor->name, 'email' => $instructor->email]]);
    }

    /**
     * Store a new course as admin (associates to instructor)
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $activeSchoolId = null;
        if ($user->role === 'super_admin') {
            $activeSchoolId = session('active_school');
        } elseif ($user->role === 'school_admin') {
            $activeSchoolId = $user->school_id;
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'course_code' => 'nullable|string|max:50|unique:courses,course_code',
            'status' => 'required|in:published,draft,archived',
            'program_id' => 'nullable|exists:programs,id',
            'description' => 'nullable|string',
            'credits' => 'nullable|numeric|min:0',
            'instructor_id' => 'required|exists:users,id',
        ]);

        // Ensure instructor belongs to active school (if scoped)
        if ($activeSchoolId) {
            $instructorValid = User::where('id', $validated['instructor_id'])
                ->where('role','instructor')
                ->where('school_id', $activeSchoolId)
                ->exists();
            if (!$instructorValid) {
                return response()->json(['success' => false, 'message' => 'Instructor not in selected school.'], 422);
            }
            if (!empty($validated['program_id'])) {
                $programValid = Program::where('id',$validated['program_id'])->where('school_id',$activeSchoolId)->exists();
                if (!$programValid) {
                    return response()->json(['success'=>false,'message'=>'Program not in selected school.'],422);
                }
            }
        }

        $course = Course::create($validated);
        $course->load('instructor','program');
        return response()->json(['success'=>true,'course'=>$course]);
    }

    public function show($courseId)
    {
        $user = Auth::user();
        $activeSchoolId = null;
        if ($user->role === 'super_admin') {
            $activeSchoolId = session('active_school');
        } elseif ($user->role === 'school_admin') {
            $activeSchoolId = $user->school_id;
        }

        $course = Course::with(['instructor','program','topics','materials','assessments','students'])->findOrFail($courseId);

        if ($activeSchoolId) {
            $inScope = false;
            if ($course->instructor && $course->instructor->school_id == $activeSchoolId) $inScope = true;
            if ($course->program && $course->program->school_id == $activeSchoolId) $inScope = true;
            if (!$inScope) {
                return response()->json(['success'=>false,'message'=>'Course not in selected school.'],403);
            }
        }

        if (request()->wantsJson() || request()->ajax()) {
            $course->students = $course->students->map(function($s){
                return ['id'=>$s->id,'name'=>$s->name,'email'=>$s->email];
            })->values();
            return response()->json(['success'=>true,'course'=>$course]);
        }
        return redirect()->route('admin.course_management');
    }

    /**
     * Show a full page view of the course details (same content as modal).
     */
    public function showDetails($courseId)
    {
        $user = Auth::user();
        $activeSchoolId = null;
        if ($user->role === 'super_admin') {
            $activeSchoolId = session('active_school');
        } elseif ($user->role === 'school_admin') {
            $activeSchoolId = $user->school_id;
        }
        $course = Course::with(['instructor','program','topics','materials','assessments','students'])->findOrFail($courseId);
        if ($activeSchoolId) {
            $inScope = false;
            if ($course->instructor && $course->instructor->school_id == $activeSchoolId) $inScope = true;
            if ($course->program && $course->program->school_id == $activeSchoolId) $inScope = true;
            if (!$inScope) abort(403,'Course not in selected school');
        }
        return view('admin.courses.course-details', compact('course'));
    }

    public function edit($courseId)
    {
        $user = Auth::user();
        $activeSchoolId = null;
        if ($user->role === 'super_admin') $activeSchoolId = session('active_school');
        elseif ($user->role === 'school_admin') $activeSchoolId = $user->school_id;
        $course = Course::with('instructor')->findOrFail($courseId);
        if ($activeSchoolId) {
            $inScope = false;
            if ($course->instructor && $course->instructor->school_id == $activeSchoolId) $inScope = true;
            if ($course->program && $course->program->school_id == $activeSchoolId) $inScope = true;
            if (!$inScope) return response()->json(['success'=>false,'message'=>'Course not in selected school.'],403);
        }
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success'=>true,'course'=>$course]);
        }
        return redirect()->route('admin.course_management');
    }

    public function update(Request $request, $courseId)
    {
        $user = Auth::user();
        $activeSchoolId = null;
        if ($user->role === 'super_admin') $activeSchoolId = session('active_school');
        elseif ($user->role === 'school_admin') $activeSchoolId = $user->school_id;

        $course = Course::findOrFail($courseId);
        if ($activeSchoolId) {
            $inScope = false;
            if ($course->instructor && $course->instructor->school_id == $activeSchoolId) $inScope = true;
            if ($course->program && $course->program->school_id == $activeSchoolId) $inScope = true;
            if (!$inScope) return redirect()->back()->with('error','Course not in selected school');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|in:published,draft,archived'
        ]);
        $course->update($validated);
        // The edit modal submits a normal form -> redirect back
        return redirect()->route('admin.course_management')->with('success','Course updated successfully.');
    }

    public function destroy($courseId)
    {
        $user = Auth::user();
        $activeSchoolId = null;
        if ($user->role === 'super_admin') $activeSchoolId = session('active_school');
        elseif ($user->role === 'school_admin') $activeSchoolId = $user->school_id;
        $course = Course::findOrFail($courseId);
        if ($activeSchoolId) {
            $inScope = false;
            if ($course->instructor && $course->instructor->school_id == $activeSchoolId) $inScope = true;
            if ($course->program && $course->program->school_id == $activeSchoolId) $inScope = true;
            if (!$inScope) return response()->json(['success'=>false,'message'=>'Course not in selected school'],403);
        }
        $course->delete();
        return response()->json(['success'=>true]);
    }
}
