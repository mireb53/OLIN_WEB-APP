<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\Material; 
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\log;

class InstructorController extends Controller
{
    public function index()
    {
        $instructor = Auth::user();

        $courses = $instructor->taughtCourses()
            ->with(['program', 'assessments' => function ($query) {
                $query->where('unavailable_at', '>', Carbon::now())->orderBy('unavailable_at');
            }])
            ->get();

        $upcomingAssessments = $courses->flatMap(function ($course) {
            return $course->assessments;
        })->sortBy('unavailable_at');

        $totalStudents = $courses->flatMap(function ($course) {
            return $course->students->pluck('id');
        })->unique()->count();

        $courseIds = $courses->pluck('id');

        // --- Updated section for material counts ---
        $videoCount = Material::whereIn('course_id', $courseIds)
            ->where('material_type', 'video')
            ->count();

        $documentCount = Material::whereIn('course_id', $courseIds)
            ->where('material_type', 'document')
            ->count();

        // Count for 'audio', 'image', and other files
        $otherFileCount = Material::whereIn('course_id', $courseIds)
            ->whereNotIn('material_type', ['video', 'document'])
            ->count();
        // --- End of updated section ---

        // --- NEW: Calculate pending grading stats ---
        $assessmentIds = \App\Models\Assessment::whereIn('course_id', $courseIds)->pluck('id');
        
        // Get all submitted assessments that need grading (status not 'graded')
        $pendingSubmissions = \App\Models\SubmittedAssessment::whereIn('assessment_id', $assessmentIds)
            ->whereIn('status', ['submitted', 'completed']) // Only count submissions that are not yet graded
            ->with(['assessment', 'student'])
            ->get();

        $pendingGrading = $pendingSubmissions->count();

        // Count urgent submissions (due today or overdue based on assessment unavailable_at date)
        $urgentSubmissions = $pendingSubmissions->filter(function ($submission) {
            $assessment = $submission->assessment;
            if (!$assessment->unavailable_at) {
                return false;
            }
            
            $dueDate = Carbon::parse($assessment->unavailable_at);
            $now = Carbon::now();
            
            // Consider urgent if due today or already past due
            return $dueDate->lte($now->endOfDay());
        })->count();
        // --- End of pending grading calculation ---

        $startOfWeek = Carbon::now()->startOfWeek();
        $endOfWeek = Carbon::now()->endOfWeek();
        $today = Carbon::today();

        $assessmentDates = \App\Models\Assessment::whereIn('course_id', $courseIds)
            ->whereNotNull('available_at')
            ->whereBetween('available_at', [$startOfWeek, $endOfWeek])
            ->pluck('available_at');

        $materialDates = \App\Models\Material::whereIn('course_id', $courseIds)
            ->whereNotNull('available_at')
            ->whereBetween('available_at', [$startOfWeek, $endOfWeek])
            ->pluck('available_at');

        $allDates = $assessmentDates->merge($materialDates)
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-d H:i'))
            ->unique()
            ->sort()
            ->values();

        $classesToday = $allDates->filter(
            fn($dt) => Carbon::createFromFormat('Y-m-d H:i', $dt)->isSameDay($today)
        )->count();

        $classesThisWeek = $allDates->count();

        return view('instructor.dashboard', compact(
            'instructor', 'courses', 'totalStudents', 'upcomingAssessments', 'classesThisWeek', 'classesToday', 'videoCount', 'documentCount', 'otherFileCount', 'pendingGrading', 'urgentSubmissions'
        ));
    }

    public function show()
    {
        $instructor = Auth::user();
        $courses = $instructor->taughtCourses()->with('program')->get();
        return view('instructor.course.myCourse', compact('instructor', 'courses'));
    }
    public function showCourseDetails($id)
    {
        $course = Course::with(['program'])
            ->withCount(['students', 'materials', 'assessments'])
            ->findOrFail($id);

        return view('instructor.course.courseDetails', compact('course'));
    }
    public function showCourseEnrollee($id)
    {
        $course = Course::with(['program', 'students.section'])
            ->withCount(['students', 'materials', 'assessments'])
            ->findOrFail($id);

        // Get sections for this course
        $sections = \App\Models\Section::where('course_id', $id)->get();

        return view('instructor.course.courseEnrollee', compact('course', 'sections'));
    }
    public function showStudentManagement(Request $request)
    {
        $instructor = Auth::user();
        
        // Get all courses taught by this instructor
        $courses = $instructor->taughtCourses()->with(['students', 'program'])->get();
        
        // Get all sections created by this instructor
        $sections = \App\Models\Section::where('user_id', $instructor->id)
            ->with(['course', 'students'])
            ->get();
        
        // Filter logic based on request parameters
        $selectedCourseId = $request->get('course_id');
        $selectedSectionId = $request->get('section_id');
        $searchTerm = $request->get('search');
        $sortBy = $request->get('sort', 'name');
        
        // Start with base query for students
        $studentsQuery = collect();
        
        // Priority: If section is selected, show only students in that section
        if ($selectedSectionId && $selectedSectionId !== 'all') {
            $section = $sections->find($selectedSectionId);
            if ($section) {
                $studentsQuery = $section->students;
                // Add course count for each student
                $studentsQuery = $studentsQuery->map(function ($student) use ($courses) {
                    $student->course_count = $courses->filter(function ($course) use ($student) {
                        return $course->students->contains('id', $student->id);
                    })->count();
                    return $student;
                });
            }
        } elseif ($selectedCourseId && $selectedCourseId !== 'all') {
            // Filter by specific course only if no section is selected
            $course = $courses->find($selectedCourseId);
            if ($course) {
                $studentsQuery = $course->students;
                // Add course count for each student
                $studentsQuery = $studentsQuery->map(function ($student) use ($courses) {
                    $student->course_count = $courses->filter(function ($course) use ($student) {
                        return $course->students->contains('id', $student->id);
                    })->count();
                    return $student;
                });
            }
        } else {
            // All students across all instructor's courses
            $studentsQuery = $courses->flatMap(function ($course) {
                return $course->students;
            })->unique('id');
            
            // Add course count for each student
            $studentsQuery = $studentsQuery->map(function ($student) use ($courses) {
                $student->course_count = $courses->filter(function ($course) use ($student) {
                    return $course->students->contains('id', $student->id);
                })->count();
                return $student;
            });
        }
        
        // Apply search filter if provided
        if ($searchTerm) {
            $studentsQuery = $studentsQuery->filter(function ($student) use ($searchTerm) {
                return stripos($student->name, $searchTerm) !== false ||
                       stripos($student->email, $searchTerm) !== false ||
                       ($student->id && stripos($student->id, $searchTerm) !== false);
            });
        }
        
        // Apply sorting
        switch ($sortBy) {
            case 'enrollment':
                $studentsQuery = $studentsQuery->sortBy(function ($student) {
                    return $student->pivot ? $student->pivot->enrollment_date : null;
                });
                break;
            case 'section':
                $studentsQuery = $studentsQuery->sortBy(function ($student) {
                    return $student->section ? $student->section->name : 'zzz'; // Put unsectioned at end
                });
                break;
            case 'name':
            default:
                $studentsQuery = $studentsQuery->sortBy('name');
                break;
        }
        
        // Calculate metrics
        $totalStudents = $studentsQuery->count();
        
        // Students without sections
        $studentsWithoutSections = $studentsQuery->filter(function ($student) {
            return is_null($student->section_id);
        });
        
        // Recent enrollments (last 30 days)
        $recentEnrollments = $studentsQuery->filter(function ($student) {
            return $student->pivot && 
                   $student->pivot->enrollment_date && 
                   \Carbon\Carbon::parse($student->pivot->enrollment_date)->greaterThan(now()->subDays(30));
        });
        
        // Convert to paginated collection for easier handling
        $allStudents = $studentsQuery->values(); // Reset keys for proper indexing
        
        // Implement manual pagination
        $perPage = 10;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        
        $students = $allStudents->slice($offset, $perPage);
        $totalStudents = $allStudents->count();
        
        // Create pagination data
        $pagination = [
            'current_page' => $currentPage,
            'per_page' => $perPage,
            'total' => $totalStudents,
            'last_page' => ceil($totalStudents / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $totalStudents),
            'has_pages' => $totalStudents > $perPage,
            'has_more_pages' => $currentPage < ceil($totalStudents / $perPage),
            'prev_page_url' => $currentPage > 1 ? $request->fullUrlWithQuery(['page' => $currentPage - 1]) : null,
            'next_page_url' => $currentPage < ceil($totalStudents / $perPage) ? $request->fullUrlWithQuery(['page' => $currentPage + 1]) : null,
        ];
        
        return view('instructor.student.studentManagementDetails', compact(
            'courses',
            'sections', 
            'students',
            'allStudents', // For total count calculations
            'totalStudents',
            'studentsWithoutSections',
            'recentEnrollments',
            'selectedCourseId',
            'selectedSectionId',
            'searchTerm',
            'sortBy',
            'pagination'
        ));
    }

    public function showProfile() {
        return view('instructor.profile');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'birth_date' => 'nullable|date|before:today',
            'gender' => 'nullable|in:Male,Female,Other,Prefer not to say',
            'bio' => 'nullable|string|max:1000',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update([
            'name' => $request->name,
            'title' => $request->title,
            'department' => $request->department,
            'phone' => $request->phone,
            'birth_date' => $request->birth_date,
            'gender' => $request->gender,
            'bio' => $request->bio,
            'address' => $request->address,
        ]);

        return redirect()->route('instructor.showProfile')
            ->with('success', 'Profile updated successfully!');
    }

    public function uploadProfileImage(Request $request)
    {
        $request->validate([
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);

        $user = Auth::user();

        // Delete old profile image if exists
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Store new profile image
        $imagePath = $request->file('profile_image')->store('profile_images', 'public');

        $user->update([
            'profile_image' => $imagePath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile image updated successfully!',
            'image_url' => asset('storage/' . $imagePath),
            // Relative public path (works under subfolders when combined with base path)
            'image_public_path' => 'storage/' . $imagePath,
            // Preferred route URL that streams the image and avoids symlink/base-path issues
            'image_route' => route('media.profile', ['filename' => basename($imagePath)])
        ]);
    }

    /**
     * Stream a profile image from the public disk to the browser.
     * Avoids issues with symlinks and subfolder base paths.
     */
    public function serveProfileImage($filename)
    {
        $path = 'profile_images/' . $filename;
        if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return abort(404);
        }

        // Leverage Storage to set appropriate headers
        return \Illuminate\Support\Facades\Storage::disk('public')->response($path);
    }
    public function assignSection(Request $request, $studentId)
    {
        $request->validate([
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $student = \App\Models\User::findOrFail($studentId);
        $student->section_id = $request->section_id;
        $student->save();

        return redirect()->back()->with('success', 'Section assigned successfully.');
    }

    public function createSection(Request $request, $courseId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $course = Course::findOrFail($courseId);
        
        // Check if section with same name exists for this course
        $existingSection = \App\Models\Section::where('course_id', $courseId)
            ->where('name', $request->name)
            ->first();
            
        if ($existingSection) {
            return redirect()->back()->with('error', 'A section with this name already exists for this course.');
        }

        \App\Models\Section::create([
            'name' => $request->name,
            'course_id' => $courseId,
            'user_id' => Auth::id(), // The instructor who created the section
        ]);

        return redirect()->back()->with('success', 'Section created successfully.');
    }

    public function bulkAssignSection(Request $request, $courseId)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
            'section_id' => 'nullable|exists:sections,id',
        ]);

        $studentIds = $request->student_ids;
        
        \App\Models\User::whereIn('id', $studentIds)->update([
            'section_id' => $request->section_id
        ]);

        $assignedCount = count($studentIds);
        $sectionName = $request->section_id ? \App\Models\Section::find($request->section_id)->name : 'No Section';
        
        return redirect()->back()->with('success', "Successfully assigned {$assignedCount} student(s) to section: {$sectionName}");
    }

    public function deleteSection($sectionId)
    {
        $section = \App\Models\Section::findOrFail($sectionId);
        
        // Move students to no section before deleting
        \App\Models\User::where('section_id', $sectionId)->update(['section_id' => null]);
        
        $section->delete();
        
        return redirect()->back()->with('success', 'Section deleted successfully. Students have been moved to no section.');
    }

    public function bulkRemoveStudents(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id',
            'course_id' => 'required|exists:courses,id'
        ]);

        $instructor = Auth::user();
        $course = Course::findOrFail($request->course_id);
        
        // Verify that the instructor owns this course
        if ($course->instructor_id !== $instructor->id) {
            return redirect()->back()->with('error', 'Unauthorized access to this course.');
        }

        $studentIds = $request->student_ids;
        $removedCount = 0;

        try {
            // Remove students from the course
            foreach ($studentIds as $studentId) {
                $course->students()->detach($studentId);
                $removedCount++;
            }
            
            return redirect()->back()->with('success', "Successfully removed {$removedCount} student(s) from all courses.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error removing students. Please try again.');
        }
    }

    public function addStudents(Request $request, $courseId)
    {
        $request->validate([
            'emails' => 'required|string',
            'section_id' => 'nullable|exists:sections,id',
            'send_notification' => 'boolean'
        ]);

        $course = Course::findOrFail($courseId);
        
        // Verify that the instructor owns this course
        if ($course->instructor_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to add students to this course.');
        }

        // Parse emails from the textarea
        $emailString = $request->emails;
        $emails = collect(preg_split('/[\s,;]+/', $emailString))
            ->map(fn($email) => trim($email))
            ->filter(fn($email) => !empty($email))
            ->unique();

        $results = [
            'added' => [],
            'already_enrolled' => [],
            'not_found' => [],
            'invalid_emails' => []
        ];

        foreach ($emails as $email) {
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $results['invalid_emails'][] = $email;
                continue;
            }

            // Find user by email
            $user = User::where('email', $email)->first();
            
            if (!$user) {
                $results['not_found'][] = $email;
                continue;
            }

            // Check if user has student role (assuming you have role-based system)
            if ($user->role !== 'student') {
                $results['not_found'][] = $email . ' (not a student)';
                continue;
            }

            // Check if already enrolled
            if ($course->students()->where('student_id', $user->id)->exists()) {
                $results['already_enrolled'][] = $email;
                continue;
            }

            try {
                DB::beginTransaction();

                // Enroll the student
                $course->students()->attach($user->id, [
                    'status' => 'enrolled',
                    'enrollment_date' => now(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Assign to section if specified
                if ($request->section_id) {
                    $user->section_id = $request->section_id;
                    $user->save();
                }

                $results['added'][] = $email;

                // Send notification email if requested
                if ($request->send_notification) {
                    try {
                        // You can create a proper Mail class for this
                        Mail::raw(
                            "Hello {$user->name},\n\nYou have been enrolled in the course: {$course->title} ({$course->course_code}).\n\nBest regards,\n{$course->instructor->name}",
                            function ($message) use ($user, $course) {
                                $message->to($user->email)
                                       ->subject("Enrolled in {$course->title}");
                            }
                        );
                    } catch (\Exception $e) {
                        // Log email error but don't fail the enrollment
                        Log::error("Failed to send enrollment notification to {$user->email}: " . $e->getMessage());
                    }
                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to enroll student {$email}: " . $e->getMessage());
                $results['not_found'][] = $email . ' (enrollment failed)';
            }
        }

        // Prepare success/error messages
        $messages = [];
        
        if (!empty($results['added'])) {
            $count = count($results['added']);
            $messages[] = "Successfully enrolled {$count} student(s).";
        }
        
        if (!empty($results['already_enrolled'])) {
            $count = count($results['already_enrolled']);
            $messages[] = "{$count} student(s) were already enrolled.";
        }
        
        if (!empty($results['not_found'])) {
            $count = count($results['not_found']);
            $messages[] = "{$count} email(s) not found or invalid: " . implode(', ', array_slice($results['not_found'], 0, 3)) . (count($results['not_found']) > 3 ? '...' : '');
        }
        
        if (!empty($results['invalid_emails'])) {
            $count = count($results['invalid_emails']);
            $messages[] = "{$count} invalid email format(s): " . implode(', ', array_slice($results['invalid_emails'], 0, 3)) . (count($results['invalid_emails']) > 3 ? '...' : '');
        }

        $messageType = !empty($results['added']) ? 'success' : 'error';
        $message = implode(' ', $messages);

        return redirect()->back()->with($messageType, $message);
    }

    public function removeStudent($courseId, $studentId)
    {
        $course = Course::findOrFail($courseId);
        
        // Verify that the instructor owns this course
        if ($course->instructor_id !== Auth::id()) {
            return redirect()->back()->with('error', 'You are not authorized to remove students from this course.');
        }

        $student = User::findOrFail($studentId);

        // Check if student is enrolled
        if (!$course->students()->where('student_id', $studentId)->exists()) {
            return redirect()->back()->with('error', 'Student is not enrolled in this course.');
        }

        try {
            // Remove enrollment
            $course->students()->detach($studentId);
            
            return redirect()->back()->with('success', "Successfully removed {$student->name} from the course.");
        } catch (\Exception $e) {
            Log::error("Failed to remove student {$studentId} from course {$courseId}: " . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to remove student from course.');
        }
    }

    public function showStudentProgress(Request $request)
    {
        $instructor = Auth::user();
        
        // Get all courses taught by this instructor with related data
        $courses = $instructor->taughtCourses()->with([
            'students', 
            'assessments', 
            'program'
        ])->get();

        // Get filter parameters
        $selectedCourseId = $request->get('course_id');
        $selectedPeriod = $request->get('period', '30'); // Last 30 days by default
        $selectedStatus = $request->get('status', 'all');

        // Calculate date range based on selected period
        $dateRange = $this->getDateRange($selectedPeriod);

        // Get all students across instructor's courses
        $allStudents = $courses->flatMap(function ($course) {
            return $course->students->map(function ($student) use ($course) {
                $student->course_info = $course;
                return $student;
            });
        })->unique('id');

        // Filter by course if selected
        if ($selectedCourseId && $selectedCourseId !== 'all') {
            $selectedCourse = $courses->find($selectedCourseId);
            $allStudents = $selectedCourse ? $selectedCourse->students : collect();
        }

        // Calculate progress metrics for each student
        $studentsWithProgress = $allStudents->map(function ($student) use ($courses, $dateRange) {
            return $this->calculateStudentProgress($student, $courses, $dateRange);
        });

        // Filter by performance status if selected
        if ($selectedStatus !== 'all') {
            $studentsWithProgress = $studentsWithProgress->filter(function ($student) use ($selectedStatus) {
                return $student->performance_status === $selectedStatus;
            });
        }

        // Calculate overall statistics
        $overallStats = $this->calculateOverallStats($studentsWithProgress, $courses);

        // Get course performance data for charts
        $coursePerformanceData = $this->getCoursePerformanceData($courses);

        return view('instructor.student.studentProgress', compact(
            'courses',
            'studentsWithProgress',
            'overallStats',
            'coursePerformanceData',
            'selectedCourseId',
            'selectedPeriod',
            'selectedStatus'
        ));
    }

    private function getDateRange($period)
    {
        $endDate = Carbon::now();
        
        switch ($period) {
            case '7':
                $startDate = Carbon::now()->subDays(7);
                break;
            case '30':
                $startDate = Carbon::now()->subDays(30);
                break;
            case '90':
                $startDate = Carbon::now()->subDays(90);
                break;
            case 'semester':
                $startDate = Carbon::now()->subMonths(6);
                break;
            default:
                $startDate = Carbon::now()->subDays(30);
        }

        return compact('startDate', 'endDate');
    }

    private function calculateStudentProgress($student, $courses, $dateRange)
    {
        // Get all assessments for courses the student is enrolled in
        $studentCourses = $courses->filter(function ($course) use ($student) {
            return $course->students->contains('id', $student->id);
        });

        $totalAssessments = $studentCourses->flatMap->assessments->count();
        
        // Get actual completed assessments from SubmittedAssessment table
        $studentAssessmentIds = $studentCourses->flatMap->assessments->pluck('id');
        $completedSubmissions = \App\Models\SubmittedAssessment::where('student_id', $student->id)
            ->whereIn('assessment_id', $studentAssessmentIds)
            ->whereIn('status', ['completed', 'graded', 'submitted'])
            ->get();
        
        $completedAssessments = $completedSubmissions->count();
        
        // Calculate average grade from actual submissions with proper percentage calculation
        $submissionScores = [];
        foreach ($completedSubmissions as $submission) {
            if ($submission->score !== null) {
                // Get the assessment to determine type
                $assessment = $studentCourses->flatMap->assessments->where('id', $submission->assessment_id)->first();
                if ($assessment) {
                    $calculatedScore = $this->calculateAssessmentScore($assessment, $submission->load('submittedQuestions'));
                    $submissionScores[] = $calculatedScore;
                }
            }
        }
        
        $averageGradeFromSubmissions = !empty($submissionScores) ? array_sum($submissionScores) / count($submissionScores) : 0;

        // Also get grades from pivot data (enrollment grades) as fallback
        $enrollmentGrades = [];
        foreach ($studentCourses as $course) {
            $studentEnrollment = $course->students->where('id', $student->id)->first();
            if ($studentEnrollment && $studentEnrollment->pivot->grade) {
                $enrollmentGrades[] = (float) $studentEnrollment->pivot->grade;
            }
        }

        // Use submission grades if available, otherwise fall back to enrollment grades
        $averageGrade = $averageGradeFromSubmissions > 0 ? $averageGradeFromSubmissions : 
                       (!empty($enrollmentGrades) ? array_sum($enrollmentGrades) / count($enrollmentGrades) : 0);

        // Get last activity from submissions
        $lastActivity = $completedSubmissions->max('submitted_at');

        // Determine performance status based on average grade
        $performanceStatus = 'excellent';
        if ($averageGrade < 60) {
            $performanceStatus = 'at-risk';
        } elseif ($averageGrade < 75) {
            $performanceStatus = 'needs-improvement';
        } elseif ($averageGrade < 85) {
            $performanceStatus = 'good';
        }

        // Add calculated fields to student
        $student->total_assessments = $totalAssessments;
        $student->completed_assessments = $completedAssessments;
        $student->completion_rate = $totalAssessments > 0 ? ($completedAssessments / $totalAssessments) * 100 : 0;
        $student->average_grade = round($averageGrade, 1);
        $student->performance_status = $performanceStatus;
        $student->enrolled_courses_count = $studentCourses->count();
        $student->last_activity = $lastActivity;

        return $student;
    }

    private function calculateOverallStats($students, $courses)
    {
        $totalStudents = $students->count();
        $averageCompletion = $students->avg('completion_rate') ?? 0;
        $averageGrade = $students->avg('average_grade') ?? 0;
        
        $performanceDistribution = [
            'excellent' => $students->where('performance_status', 'excellent')->count(),
            'good' => $students->where('performance_status', 'good')->count(),
            'needs-improvement' => $students->where('performance_status', 'needs-improvement')->count(),
            'at-risk' => $students->where('performance_status', 'at-risk')->count(),
        ];

        return compact(
            'totalStudents',
            'averageCompletion',
            'averageGrade',
            'performanceDistribution'
        );
    }

    private function getCoursePerformanceData($courses)
    {
        return $courses->map(function ($course) {
            $studentCount = $course->students->count();
            $avgGrade = 0;
            
            if ($studentCount > 0) {
                // Get average grade from actual submissions for this course with proper percentage calculation
                $courseSubmissions = \App\Models\SubmittedAssessment::with('submittedQuestions')
                    ->whereIn('assessment_id', $course->assessments->pluck('id'))
                    ->whereIn('status', ['completed', 'graded', 'submitted'])
                    ->whereNotNull('score')
                    ->get();

                if ($courseSubmissions->isNotEmpty()) {
                    $submissionScores = [];
                    foreach ($courseSubmissions as $submission) {
                        $assessment = $course->assessments->where('id', $submission->assessment_id)->first();
                        if ($assessment) {
                            $calculatedScore = $this->calculateAssessmentScore($assessment, $submission);
                            $submissionScores[] = $calculatedScore;
                        }
                    }
                    $avgGrade = !empty($submissionScores) ? array_sum($submissionScores) / count($submissionScores) : 0;
                } else {
                    // Fall back to enrollment grades if no submissions
                    $grades = $course->students->pluck('pivot.grade')->filter()->map(function ($grade) {
                        return (float) $grade;
                    });
                    $avgGrade = $grades->count() > 0 ? $grades->avg() : 0;
                }
            }

            return [
                'course_name' => $course->title,
                'course_code' => $course->course_code,
                'student_count' => $studentCount,
                'average_grade' => round($avgGrade, 1),
                'assessment_count' => $course->assessments->count(),
            ];
        });
    }

    private function getRecentActivities($courses, $dateRange)
    {
        // Get actual activity data - focus only on student activities
        $activities = collect();

        $courseIds = $courses->pluck('id');

        // Add recent assessment submissions (main notification type)
        $recentSubmissions = \App\Models\SubmittedAssessment::with(['student', 'assessment.course'])
            ->whereHas('assessment', function ($query) use ($courseIds) {
                $query->whereIn('course_id', $courseIds);
            })
            ->whereBetween('submitted_at', [$dateRange['startDate'], $dateRange['endDate']])
            ->whereIn('status', ['completed', 'graded', 'submitted'])
            ->orderBy('submitted_at', 'desc')
            ->get();

        foreach ($recentSubmissions as $submission) {
            // Get submission status for better context
            $statusText = '';
            switch($submission->status) {
                case 'submitted':
                    $statusText = ' (Needs Review)';
                    break;
                case 'completed':
                    $statusText = ' (Pending Grading)';
                    break;
                case 'graded':
                    $statusText = ' (Graded)';
                    break;
            }
            
            $activities->push([
                'type' => 'assessment_submitted',
                'description' => "{$submission->student->name} submitted '{$submission->assessment->title}'{$statusText}",
                'date' => $submission->submitted_at,
                'course' => $submission->assessment->course->title,
                'student_id' => $submission->student->id,
                'submission_id' => $submission->id,
                'status' => $submission->status,
            ]);
        }

        // Add recent enrollments (still useful for instructors to know)
        foreach ($courses as $course) {
            $recentEnrollments = $course->students()
                ->wherePivotBetween('enrollment_date', [$dateRange['startDate'], $dateRange['endDate']])
                ->get();

            foreach ($recentEnrollments as $student) {
                $activities->push([
                    'type' => 'student_enrolled',
                    'description' => "New student {$student->name} enrolled in {$course->title}",
                    'date' => $student->pivot->enrollment_date,
                    'course' => $course->title,
                    'student_id' => $student->id,
                ]);
            }
        }

        return $activities->sortByDesc('date')->take(15);
    }

    public function getNotifications()
    {
        $instructor = Auth::user();
        
        // Get all courses taught by this instructor
        $courses = $instructor->taughtCourses()->get();
        
        // Get activities from the last 30 days for notifications
        $dateRange = [
            'startDate' => Carbon::now()->subDays(30),
            'endDate' => Carbon::now()
        ];
        
        $activities = $this->getRecentActivities($courses, $dateRange);
        
        // Use DB-backed read tracking like capstone_webDashboard-B
        $readNotificationHashes = \App\Models\InstructorNotification::where('instructor_id', $instructor->id)
            ->where('is_read', true)
            ->pluck('notification_hash')
            ->toArray();
        
        // Add read status to each activity
        $notifications = $activities->map(function ($activity) use ($readNotificationHashes) {
            $notificationHash = md5($activity['type'] . $activity['description'] . $activity['date']);
            $activity['id'] = $notificationHash;
            $activity['read'] = in_array($notificationHash, $readNotificationHashes);
            return $activity;
        });
        
        $unreadCount = $notifications->where('read', false)->count();
        
        return response()->json([
            'notifications' => $notifications->values(),
            'unread_count' => $unreadCount
        ]);
    }

    public function markNotificationAsRead(Request $request)
    {
        $instructor = Auth::user();
        $notificationHash = $request->input('notification_id');
        
        // Create or update notification record in database
        \App\Models\InstructorNotification::updateOrCreate(
            [
                'instructor_id' => $instructor->id,
                'notification_hash' => $notificationHash
            ],
            [
                'is_read' => true,
                'read_at' => now()
            ]
        );
        
        return response()->json(['success' => true]);
    }

    public function markAllNotificationsAsRead(Request $request)
    {
        $instructor = Auth::user();
        $notificationHashes = $request->input('notification_ids', []);
        
        foreach ($notificationHashes as $hash) {
            \App\Models\InstructorNotification::updateOrCreate(
                [
                    'instructor_id' => $instructor->id,
                    'notification_hash' => $hash
                ],
                [
                    'is_read' => true,
                    'read_at' => now()
                ]
            );
        }
        
        return response()->json(['success' => true]);
    }

    public function showStudentDetails(Request $request, $studentId = null)
    {
        $instructor = Auth::user();
        
        // Get all courses taught by this instructor
        $courses = $instructor->taughtCourses()->with([
            'students', 
            'assessments.questions', 
            'program'
        ])->get();

        // Get all students across instructor's courses for the search table
        $allStudents = $courses->flatMap(function ($course) {
            return $course->students->map(function ($student) use ($course) {
                return $student;
            });
        })->unique('id')->map(function ($student) use ($courses) {
            return $this->calculateStudentProgress($student, $courses, ['startDate' => now()->subDays(30), 'endDate' => now()]);
        });

        // Apply search and filters
        $searchTerm = $request->get('search');
        $selectedCourseId = $request->get('course_id');
        $selectedStatus = $request->get('status', 'all');

        if ($searchTerm) {
            $allStudents = $allStudents->filter(function ($student) use ($searchTerm) {
                return stripos($student->name, $searchTerm) !== false ||
                       stripos($student->email, $searchTerm) !== false ||
                       stripos($student->id, $searchTerm) !== false;
            });
        }

        if ($selectedCourseId && $selectedCourseId !== 'all') {
            $selectedCourse = $courses->find($selectedCourseId);
            if ($selectedCourse) {
                $allStudents = $allStudents->filter(function ($student) use ($selectedCourse) {
                    return $selectedCourse->students->contains('id', $student->id);
                });
            }
        }

        if ($selectedStatus !== 'all') {
            $allStudents = $allStudents->filter(function ($student) use ($selectedStatus) {
                return $student->performance_status === $selectedStatus;
            });
        }

        // Get detailed info for selected student if provided
        $selectedStudent = null;
        $studentCourses = collect();
        $studentAssessments = collect();
        $studentProgress = null;

        if ($studentId) {
            $selectedStudent = User::with(['section', 'program'])->find($studentId);
            if ($selectedStudent) {
                // Get courses this student is enrolled in under this instructor
                $studentCourses = $courses->filter(function ($course) use ($selectedStudent) {
                    return $course->students->contains('id', $selectedStudent->id);
                });

                // Get all assessments for these courses with student's submissions
                $studentAssessments = $studentCourses->flatMap(function ($course) use ($selectedStudent) {
                    return $course->assessments->map(function ($assessment) use ($course, $selectedStudent) {
                        $assessment->course_info = $course;
                        
                        // Get actual submission data
                        $submission = \App\Models\SubmittedAssessment::with('submittedQuestions')
                            ->where('student_id', $selectedStudent->id)
                            ->where('assessment_id', $assessment->id)
                            ->whereIn('status', ['completed', 'graded', 'submitted'])
                            ->latest()
                            ->first();
                        
                        if ($submission) {
                            $assessment->student_submitted = true;
                            
                            // Calculate percentage score for quiz/exam assessments
                            $calculatedScore = $this->calculateAssessmentScore($assessment, $submission);
                            
                            $assessment->student_score = $calculatedScore;
                            $assessment->submission_date = $submission->submitted_at;
                            $assessment->submitted_at = $submission->submitted_at;
                            $assessment->submitted_file = $submission->original_filename;
                            $assessment->submission_id = $submission->id;
                            $assessment->submission_status = $submission->status;
                        } else {
                            $assessment->student_submitted = false;
                            $assessment->student_score = null;
                            $assessment->submission_date = null;
                            $assessment->submitted_at = null;
                            $assessment->submitted_file = null;
                            $assessment->submission_id = null;
                            $assessment->submission_status = null;
                        }
                        
                        return $assessment;
                    });
                });

                // Calculate detailed progress for this student
                $studentProgress = $this->calculateStudentProgress($selectedStudent, $courses, ['startDate' => now()->subDays(30), 'endDate' => now()]);
            }
        }

        return view('instructor.student.studentDetails', compact(
            'courses',
            'allStudents',
            'selectedStudent',
            'studentCourses',
            'studentAssessments',
            'studentProgress',
            'searchTerm',
            'selectedCourseId',
            'selectedStatus'
        ));
    }

    public function getSubmissionDetails(Request $request, $submission)
    {
        $instructor = Auth::user();
        
        $submission = \App\Models\SubmittedAssessment::with([
            'assessment.course',
            'student',
            'submittedQuestions.question'
        ])->findOrFail($submission);
        
        // Verify instructor has access to this submission
        if ($submission->assessment->course->instructor_id !== $instructor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Calculate percentage score for display
        $percentageScore = $this->calculateAssessmentScore($submission->assessment, $submission);
        
        // Get total and earned points from submitted questions
        $totalPoints = $submission->submittedQuestions->sum(function($q) {
            return $q->max_points ?? ($q->question ? $q->question->points ?? 1 : 1);
        });
        
        $earnedPoints = $submission->submittedQuestions->sum('score_earned');
        
        $data = [
            'submission' => $submission,
            'assessment' => $submission->assessment,
            'student' => $submission->student,
            'submitted_questions' => $submission->submittedQuestions->map(function($sq) {
                return [
                    'id' => $sq->id,
                    'question_text' => $sq->question_text,
                    'question_type' => $sq->question_type,
                    'submitted_answer' => $sq->submitted_answer,
                    'is_correct' => $sq->is_correct,
                    'score_earned' => $sq->score_earned,
                    'max_points' => $sq->max_points,
                    'question' => $sq->question ? [
                        'id' => $sq->question->id,
                        'question_text' => $sq->question->question_text,
                        'question_type' => $sq->question->question_type ?? 'multiple_choice',
                        'correct_answer' => $sq->question->correct_answer,
                        'points' => $sq->question->points
                    ] : null
                ];
            }),
            'percentage_score' => $percentageScore,
            'raw_score' => $submission->score,
            'total_points' => $totalPoints,
            'earned_points' => $earnedPoints,
            // Add file information for assignment submissions
            'submitted_file' => $submission->original_filename,
            'submitted_file_path' => $submission->submitted_file_path,
            'file_size' => $submission->submitted_file_path ? $this->getFileSize($submission->submitted_file_path) : null,
        ];
        
        return response()->json($data);
    }

    private function getFileSize($filePath)
    {
        if (!$filePath || !file_exists(storage_path('app/' . $filePath))) {
            return null;
        }
        
        $bytes = filesize(storage_path('app/' . $filePath));
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    public function updateGrade(Request $request, $submission)
    {
        $instructor = Auth::user();
        
        $submission = \App\Models\SubmittedAssessment::with('assessment.course')
            ->findOrFail($submission);
        
        // Verify instructor has access to this submission
        if ($submission->assessment->course->instructor_id !== $instructor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'score' => 'nullable|numeric|min:0|max:100',
            'feedback' => 'nullable|string|max:1000',
            'return_to_student' => 'boolean'
        ]);
        
        $updateData = [];
        
        // For quiz/exam type assessments, auto-calculate score from question points
        if (in_array(strtolower($submission->assessment->type), ['quiz', 'exam'])) {
            // Get all submitted questions and calculate total score
            $submittedQuestions = $submission->submittedQuestions()->with('question')->get();
            $totalPoints = 0;
            $earnedPoints = 0;
            
            foreach ($submittedQuestions as $submittedQuestion) {
                $totalPoints += $submittedQuestion->question->points;
                if ($submittedQuestion->is_correct) {
                    $earnedPoints += $submittedQuestion->question->points;
                }
            }
            
            // Calculate percentage score
            $calculatedScore = $totalPoints > 0 ? round(($earnedPoints / $totalPoints) * 100, 2) : 0;
            $updateData['score'] = $calculatedScore;
            $updateData['status'] = 'graded';
            $updateData['graded_at'] = now();
        } else {
            // For assignment type assessments, use manual score input
            if ($request->has('score') && $request->score !== null) {
                $updateData['score'] = $request->score;
                $updateData['status'] = 'graded';
                $updateData['graded_at'] = now();
            }
        }
        
        if ($request->has('feedback')) {
            $updateData['instructor_feedback'] = $request->feedback;
        }
        
        $submission->update($updateData);
        
        // If returning to student, you could send a notification here
        if ($request->return_to_student) {
            // Implement notification logic if needed
            // Mail::to($submission->student)->send(new AssessmentGradedNotification($submission));
        }
        
        return response()->json([
            'success' => true,
            'message' => 'Grade updated successfully',
            'submission' => $submission->fresh()
        ]);
    }

    public function updateQuestionGrade(Request $request, $submittedQuestion)
    {
        $instructor = Auth::user();
        
        $submittedQuestion = \App\Models\SubmittedQuestion::with([
            'submittedAssessment.assessment.course',
            'question'
        ])->findOrFail($submittedQuestion);
        
        // Verify instructor has access to this submission
        if ($submittedQuestion->submittedAssessment->assessment->course->instructor_id !== $instructor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'is_correct' => 'required|boolean'
        ]);
        
        // Update the question correctness and score
        $points = $submittedQuestion->max_points ?? ($submittedQuestion->question ? $submittedQuestion->question->points ?? 1 : 1);
        $scoreEarned = $request->is_correct ? $points : 0;
        
        $submittedQuestion->update([
            'is_correct' => $request->is_correct,
            'score_earned' => $scoreEarned
        ]);
        
        // Recalculate the submission score
        $submittedAssessment = $submittedQuestion->submittedAssessment;
        $submittedQuestions = $submittedAssessment->submittedQuestions()->with('question')->get();
        
        $totalPoints = $submittedQuestions->sum(function ($q) {
            return $q->max_points ?? ($q->question ? $q->question->points ?? 1 : 1);
        });
        
        $earnedPoints = $submittedQuestions->sum(function ($q) {
            return $q->score_earned ?? ($q->is_correct ? ($q->question ? $q->question->points ?? 1 : 1) : 0);
        });
        
        $percentageScore = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
        
        // Update the submission with new calculated score
        $submittedAssessment->update([
            'score' => round($percentageScore, 2),
            'status' => 'graded',
            'graded_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Question grade updated successfully',
            'new_score' => round($percentageScore, 2),
            'earned_points' => $earnedPoints,
            'total_points' => $totalPoints
        ]);
    }


    public function downloadSubmission(Request $request, $submission)
    {
        $instructor = Auth::user();
        
        $submission = \App\Models\SubmittedAssessment::with('assessment.course')
            ->findOrFail($submission);
        
        // Verify instructor has access to this submission
        if ($submission->assessment->course->instructor_id !== $instructor->id) {
            abort(403, 'Unauthorized');
        }
        
        if (!$submission->submitted_file_path) {
            abort(404, 'No file found for this submission');
        }
        
        // Check both storage paths
        $filePath = storage_path('app/' . $submission->submitted_file_path);
        
        // If file doesn't exist in storage/app, try storage/app/public
        if (!file_exists($filePath)) {
            $filePath = storage_path('app/public/' . $submission->submitted_file_path);
        }
        
        if (!file_exists($filePath)) {
            Log::error('File not found: ' . $submission->submitted_file_path);
            abort(404, 'File not found on server');
        }
        
        // Check if this is a view request
        if ($request->get('view')) {
            $mimeType = mime_content_type($filePath);
            
            // For viewable file types, return inline response
            if (in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'text/plain', 'text/html'])) {
                return response()->file($filePath, [
                    'Content-Type' => $mimeType,
                    'Content-Disposition' => 'inline; filename="' . $submission->original_filename . '"'
                ]);
            }
        }
        
        // Default: download the file
        return response()->download($filePath, $submission->original_filename);
    }

    private function calculateAssessmentScore($assessment, $submission)
    {
        $assessmentType = strtolower(trim($assessment->type));
        
        // For quiz/exam assessments, calculate percentage based on earned vs max points
        if (in_array($assessmentType, ['quiz', 'exam'])) {
            if ($submission->submittedQuestions && $submission->submittedQuestions->count() > 0) {
                $totalMaxPoints = $submission->submittedQuestions->sum('max_points');
                $totalEarnedPoints = $submission->submittedQuestions->sum('score_earned');
                
                if ($totalMaxPoints > 0) {
                    return round(($totalEarnedPoints / $totalMaxPoints) * 100, 1);
                }
            }
            
            // If no questions or max points, return the direct score as percentage
            return $submission->score ? round($submission->score, 1) : 0;
        }
        
        // For assignments, activities, projects - return the stored score directly as it's usually a percentage
        return $submission->score ? round($submission->score, 1) : 0;
    }
    public function updateQuestionPoints(Request $request, $submittedQuestion)
    {
        $instructor = Auth::user();
        
        $submittedQuestion = \App\Models\SubmittedQuestion::with([
            'submittedAssessment.assessment.course',
            'question'
        ])->findOrFail($submittedQuestion);
        
        // Verify instructor has access to this submission
        if ($submittedQuestion->submittedAssessment->assessment->course->instructor_id !== $instructor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'points' => 'required|numeric|min:0'
        ]);
        
        $maxPoints = $submittedQuestion->max_points ?? ($submittedQuestion->question ? $submittedQuestion->question->points ?? 1 : 1);
        $newPoints = $request->points;
        
        // Validate that points don't exceed maximum
        if ($newPoints > $maxPoints) {
            return response()->json(['error' => 'Points cannot exceed maximum points for this question'], 400);
        }
        
        // Update the question points and mark as correct if points > 0
        $submittedQuestion->update([
            'score_earned' => $newPoints,
            'is_correct' => $newPoints > 0
        ]);
        
        // Recalculate the submission score
        $submittedAssessment = $submittedQuestion->submittedAssessment;
        $submittedQuestions = $submittedAssessment->submittedQuestions()->with('question')->get();
        
        $totalPoints = $submittedQuestions->sum(function ($q) {
            return $q->max_points ?? ($q->question ? $q->question->points ?? 1 : 1);
        });
        
        $earnedPoints = $submittedQuestions->sum('score_earned');
        
        $percentageScore = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
        
        // Update the submission with new calculated score
        $submittedAssessment->update([
            'score' => round($percentageScore, 2),
            'status' => 'graded',
            'graded_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Question points updated successfully',
            'new_score' => round($percentageScore, 2),
            'earned_points' => $earnedPoints,
            'total_points' => $totalPoints
        ]);
    }

    public function recalculateSubmissionScore(Request $request, $submissionId)
    {
        $instructor = Auth::user();
        
        $submission = \App\Models\SubmittedAssessment::with([
            'assessment.course',
            'submittedQuestions.question'
        ])->findOrFail($submissionId);
        
        // Verify instructor has access to this submission
        if ($submission->assessment->course->instructor_id !== $instructor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        // Recalculate the score based on current question scores
        $submittedQuestions = $submission->submittedQuestions;
        
        $totalPoints = $submittedQuestions->sum(function ($q) {
            return $q->max_points ?? ($q->question ? $q->question->points ?? 1 : 1);
        });
        
        $earnedPoints = $submittedQuestions->sum('score_earned');
        
        $percentageScore = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;
        
        // Update the submission
        $submission->update([
            'score' => round($percentageScore, 2),
            'status' => 'graded',
            'graded_at' => now()
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Score recalculated successfully',
            'new_score' => round($percentageScore, 2),
            'earned_points' => $earnedPoints,
            'total_points' => $totalPoints
        ]);
    }
    public function deleteProfileImage(Request $request)
    {
        $user = Auth::user();

        if ($user->profile_image) {
            // Delete the file from public storage
            Storage::disk('public')->delete($user->profile_image);

            // Set the profile_image field to null in the database
            $user->update(['profile_image' => null]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image deleted successfully!',
                'user_initial' => strtoupper(substr($user->name, 0, 1))
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No profile image to delete.'
        ], 404);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                'different:current_password'
            ],
            'confirm_password' => 'required|same:new_password',
        ], [
            'new_password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
            'new_password.different' => 'New password must be different from current password.',
            'confirm_password.same' => 'Password confirmation does not match.',
        ]);

        $user = Auth::user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => ['current_password' => ['Current password is incorrect.']]
            ], 422);
        }

        try {
            // Update password
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!'
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to update password for user {$user->id}: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update password. Please try again.'
            ], 500);
        }
    }
}