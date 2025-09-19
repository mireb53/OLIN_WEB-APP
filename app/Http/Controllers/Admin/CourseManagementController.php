<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;

class CourseManagementController extends Controller
{
    public function index()
    {
        // Simple version - just return the view without fetching any data
        // This will use the sample data in the blade file
        return view('admin.course_management');
    }
    
    // These methods are commented out to prevent errors
    // You can uncomment and implement them later when needed
    
    /*
    public function show($courseId)
    {
        $course = Course::with(['instructor'])->findOrFail($courseId);
        return view('admin.courses.show', compact('course'));
    }

    public function create()
    {
        return view('admin.courses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive,draft,archived',
            'instructor_id' => 'required|exists:users,id',
        ]);

        $course = Course::create($request->all());

        // Handle thumbnail upload if provided
        if ($request->hasFile('thumbnail')) {
            $request->validate([
                'thumbnail' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);
            
            $thumbnailPath = $request->file('thumbnail')->store('course_thumbnails', 'public');
            $course->update(['thumbnail' => $thumbnailPath]);
        }

        return redirect()->route('admin.courses.index')->with('success', 'Course created successfully');
    }

    public function edit($courseId)
    {
        $course = Course::findOrFail($courseId);
        return view('admin.courses.edit', compact('course'));
    }

    public function update(Request $request, $courseId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive,draft,archived',
            'instructor_id' => 'required|exists:users,id',
        ]);

        $course = Course::findOrFail($courseId);
        
        $course->update([
            'title' => $request->title,
            'description' => $request->description,
            'status' => $request->status,
            'instructor_id' => $request->instructor_id,
        ]);

        // Handle thumbnail upload if provided
        if ($request->hasFile('thumbnail')) {
            $request->validate([
                'thumbnail' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Delete old thumbnail if it exists
            if ($course->thumbnail && Storage::exists('public/' . $course->thumbnail)) {
                Storage::delete('public/' . $course->thumbnail);
            }

            // Store new thumbnail
            $thumbnailPath = $request->file('thumbnail')->store('course_thumbnails', 'public');
            $course->update(['thumbnail' => $thumbnailPath]);
        }

        return redirect()->route('admin.course_management')->with('success', 'Course updated successfully');
    }

    public function destroy($courseId)
    {
        $course = Course::findOrFail($courseId);
        
        // Note: You'll need to implement the enrollments relationship in the Course model
        // before uncommenting this check
        // 
        // if ($course->enrollments()->count() > 0) {
        //     return redirect()->route('admin.courses.index')
        //         ->with('error', 'Cannot delete course with active enrollments');
        // }

        // Delete course thumbnail if it exists
        if ($course->thumbnail && Storage::exists('public/' . $course->thumbnail)) {
            Storage::delete('public/' . $course->thumbnail);
        }

        $course->delete();

        return redirect()->route('admin.courses.index')->with('success', 'Course deleted successfully');
    }
    */
}


