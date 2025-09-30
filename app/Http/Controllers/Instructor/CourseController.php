<?php

// app/Http/Controllers/Instructor/CourseController.php

namespace App\Http\Controllers\Instructor;

use App\Models\Course;
use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Models\Program;
use App\Models\Topic;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function create()
    {
        return view('instructor.course.createCourse');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'course_code' => 'required|string|max:50|unique:courses,course_code',
            'description' => 'nullable|string',
            'credits' => 'nullable|integer|min:1',
            'department' => 'required|string|max:10',
            'program_name' => 'required|string|max:255',
            'status' => 'required|in:draft,published,archived',
        ]);

        // Extract only the acronym from program_name (in case full name is sent)
        $programName = Str::upper($request->program_name);

        $program = Program::firstOrCreate(
            ['name' => $programName]
        );

        $programId = $program->id;

        Course::create([
            'title' => $request->title,
            'course_code' => $request->course_code,
            'description' => $request->description,
            'credits' => $request->credits,
            'department' => $request->department, // Save department acronym
            'program_id' => $programId,
            'instructor_id' => Auth::id(),
            'status' => $request->status,
        ]);

        return redirect()->route('instructor.dashboard')->with('success', 'Course created successfully!');
    }

    public function show(Course $course)
    {
        $course->load(['program', 'instructor']);
        $topics = $course->topics()->with(['materials', 'assessments'])->get();
        $independentAssessments = $course->assessments()
                                        ->whereNull('topic_id')
                                        ->get();

        return view('instructor.course.show', compact('course', 'topics', 'independentAssessments'));
    }
    public function update(Request $request, Course $course)
    {
        // Ensure the instructor can only update their own courses
        if ($course->instructor_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'course_code' => 'required|string|max:50|unique:courses,course_code,' . $course->id,
            'description' => 'nullable|string',
            'credits' => 'nullable|integer|min:1',
            'department' => 'required|string|max:10',
            'program_name' => 'required|string|max:255',
            'status' => 'required|in:draft,published,archived',
        ]);

        // Extract only the acronym from program_name (in case full name is sent)
        $programName = Str::upper($request->program_name);

        $program = Program::firstOrCreate(
            ['name' => $programName]
        );

        $course->update([
            'title' => $request->title,
            'course_code' => $request->course_code,
            'description' => $request->description,
            'credits' => $request->credits,
            'department' => $request->department, // Save department acronym
            'program_id' => $program->id,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Course information updated successfully!');
    }


}