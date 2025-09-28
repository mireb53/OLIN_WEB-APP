<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AssessmentController extends Controller
{
    public function createQuiz(Course $course, $type, Request $request)
    {
        $assessmentType = $type;
        $topicId = $request->query('topic_id');
        return view('instructor.assessment.createQuiz', compact('course', 'assessmentType', 'topicId'));
    }

    public function storeQuiz(Request $request, $courseId)
    {
        Log::info($request->all());
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_minutes' => 'nullable|integer|min:0',
            'access_code' => 'nullable|string|max:255',
            'assessment_file' => 'nullable|file|max:20480', // 20MB
            'available_at' => 'nullable|date',
            'unavailable_at' => 'nullable|date|after_or_equal:available_at',
            'questions' => 'nullable|array',
            'questions.*.question_type' => 'required_with:questions|string|in:multiple_choice,identification,true_false,essay',
            'questions.*.question_text' => 'required_with:questions|string',
            'questions.*.points' => 'required_with:questions|integer|min:1',
            'topic_id' => 'nullable|exists:topics,id',
        ]);

        // Handle file upload if present
        $filePath = null;
        if ($request->hasFile('assessment_file')) {
            $filePath = $request->file('assessment_file')->store('assessments', 'public');
        }

        // Corrected: Convert available_at and unavailable_at from local time to UTC
        $localTimezone = 'Asia/Manila';
        $availableAt = $validated['available_at'] ? Carbon::parse($validated['available_at'], $localTimezone)->setTimezone('UTC') : null;
        $unavailableAt = $validated['unavailable_at'] ? Carbon::parse($validated['unavailable_at'], $localTimezone)->setTimezone('UTC') : null;

        // Create the assessment
        $assessment = \App\Models\Assessment::create([
            'course_id' => $courseId,
            'topic_id' => $validated['topic_id'] ?? null,
            'title' => $validated['title'],
            'type' => $request->input('type', 'quiz'),
            'description' => $validated['description'] ?? null,
            'assessment_file_path' => $filePath,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'access_code' => $validated['access_code'] ?? null,
            'available_at' => $availableAt,
            'unavailable_at' => $unavailableAt,
            'created_by' => Auth::id(),
        ]);

        // Save questions if present
        if ($request->has('questions')) {
            foreach ($request->input('questions') as $order => $q) {
                $question = new \App\Models\Question([
                    'assessment_id' => $assessment->id,
                    'question_text' => $q['question_text'],
                    'question_type' => $q['question_type'],
                    'points' => $q['points'],
                    'order' => $order,
                ]);

                // Handle correct_answer and options
                // In the storeQuiz method, inside the foreach loop for questions
                if ($q['question_type'] === 'multiple_choice') {
                    $question->correct_answer = $q['correct_answer'];
                        $question->save();

                    // Save options
                    if (isset($q['options']) && is_array($q['options'])) {
                        foreach ($q['options'] as $opt) {
                            if (!empty($opt['option_text'])) {
                                \App\Models\QuestionOption::create([
                                    'question_id' => $question->id,
                                    'option_text' => $opt['option_text'],
                                    'option_order' => $opt['option_order'],
                                ]);
                            }
                        }
                    }
                } elseif ($q['question_type'] === 'identification') {
                    $question->correct_answer = $q['correct_answer'];
                    $question->save();
                } elseif ($q['question_type'] === 'true_false') {
                    $question->correct_answer = $q['correct_answer']; // 'true' or 'false'
                    $question->save();
                    
                    // Create 'True' and 'False' options for the true/false question
                    \App\Models\QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => 'True',
                        'option_order' => 0,
                    ]);
                    \App\Models\QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => 'False',
                        'option_order' => 1,
                    ]);

                } else {
                    $question->save();
                }
            }
        }

        return response()->json(['success' => true, 'redirect' => route('courses.show', $courseId)]);
    }

    public function editQuiz(Course $course, Assessment $assessment)
    {
        // Ensure the assessment belongs to the course
        if ($assessment->course_id !== $course->id) {
            abort(404);
        }
        // Load questions and their options
        $assessment->load('questions.options');

        // Corrected: Convert UTC times to local time for display in the form
        if ($assessment->available_at) {
            $assessment->available_at = $assessment->available_at->setTimezone('Asia/Manila')->format('Y-m-d\TH:i');
        }
        if ($assessment->unavailable_at) {
            $assessment->unavailable_at = $assessment->unavailable_at->setTimezone('Asia/Manila')->format('Y-m-d\TH:i');
        }

        $assessmentType = $assessment->type; // 'quiz' or 'exam'
        $topicId = $assessment->topic_id; // Get topic_id if associated

        return view('instructor.assessment.createQuiz', compact('course', 'assessmentType', 'topicId', 'assessment'));
    }

    // Method to handle the update submission
    public function updateQuiz(Request $request, Course $course, Assessment $assessment)
    {
        // 1. (Optional but recommended) Ownership check - Already good
        if ($assessment->course_id !== $course->id) {
            Log::warning("Mismatched course_id for assessment during update. Course ID in URL: {$course->id}, Assessment course_id: {$assessment->course_id}, Assessment ID: {$assessment->id}");
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // 2. Validate the request data
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'duration_minutes' => 'nullable|integer|min:0',
                'access_code' => 'nullable|string|max:255',
                'available_at' => 'nullable|date',
                'unavailable_at' => 'nullable|date|after_or_equal:available_at',
                'questions' => 'nullable|array',
                'questions.*.id' => 'nullable|exists:questions,id', // For existing questions
                'questions.*.question_text' => 'required|string',
                'questions.*.question_type' => ['required', Rule::in(['multiple_choice', 'identification', 'true_false', 'essay'])],
                // Corrected: correct_answer is required for MC too (it will be the index)
                'questions.*.correct_answer' => 'required_if:questions.*.question_type,identification,true_false,multiple_choice|nullable|string',
                'questions.*.points' => 'required|integer|min:1', // Added points validation
                // 'questions.*.order' => 'required|integer', // REMOVED: Order is implicitly handled by array index
                'questions.*.options' => 'array', // For multiple choice options
                'questions.*.options.*.id' => 'nullable|exists:question_options,id',
                'questions.*.options.*.option_text' => 'required|string',
                // 'deleted_questions' and 'deleted_options' are no longer strictly needed in this flow
                // as we handle deletion by checking what's NOT in the submitted data.
            ]);

            // 3. Handle file upload if present (similar to store method)
            $filePath = $assessment->assessment_file_path; // Keep existing path by default

            // Check if a new file is uploaded
            if ($request->hasFile('assessment_file')) {
                // Delete old file if exists
                if ($filePath) {
                    Storage::disk('public')->delete($filePath);
                }
                $filePath = $request->file('assessment_file')->store('assessments', 'public');
            } elseif ($request->has('clear_assessment_file') && $request->input('clear_assessment_file') == '1') {
                // If "remove current file" checkbox is checked
                if ($filePath) {
                    Storage::disk('public')->delete($filePath);
                }
                $filePath = null;
            }

            // Corrected: Convert available_at and unavailable_at from local time to UTC
            $localTimezone = 'Asia/Manila';
            $availableAt = $validatedData['available_at'] ? Carbon::parse($validatedData['available_at'], $localTimezone)->setTimezone('UTC') : null;
            $unavailableAt = $validatedData['unavailable_at'] ? Carbon::parse($validatedData['unavailable_at'], $localTimezone)->setTimezone('UTC') : null;

            // 4. Update assessment details
            $assessment->fill([
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
                'assessment_file_path' => $filePath, // Update file path
                'duration_minutes' => $validatedData['duration_minutes'] ?? null,
                'access_code' => $validatedData['access_code'] ?? null,
                'available_at' => $availableAt,
                'unavailable_at' => $unavailableAt,
            ])->save();


            // 5. Handle question and option updates (synchronize based on submitted data)
            $submittedQuestionIds = collect($validatedData['questions'])->pluck('id')->filter()->toArray();

            // Delete questions that are no longer in the submitted data
            $assessment->questions()->whereNotIn('id', $submittedQuestionIds)->delete();

            // In the updateQuiz method, inside the foreach loop for questions
            foreach ($validatedData['questions'] as $order => $questionData) {
                $question = $assessment->questions()->updateOrCreate(
                    ['id' => $questionData['id'] ?? null],
                    [
                        'question_text' => $questionData['question_text'],
                        'question_type' => $questionData['question_type'],
                        'points' => $questionData['points'],
                        'order' => $order,
                        'correct_answer' => $questionData['correct_answer'] ?? null,
                    ]
                );

                // Handle options for different question types
                if ($questionData['question_type'] === 'multiple_choice' && isset($questionData['options'])) {
                    $submittedOptionIds = collect($questionData['options'])->pluck('id')->filter()->toArray();
                    $question->options()->whereNotIn('id', $submittedOptionIds)->delete();

                    foreach ($questionData['options'] as $optionOrder => $optionData) {
                        $question->options()->updateOrCreate(
                            ['id' => $optionData['id'] ?? null],
                            [
                                'option_text' => $optionData['option_text'],
                                'option_order' => $optionOrder,
                            ]
                        );
                    }
                } elseif ($questionData['question_type'] === 'true_false') {
                    // If question is true/false, delete existing options and create 'True' and 'False'
                    $question->options()->delete();
                    \App\Models\QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => 'True',
                        'option_order' => 0,
                    ]);
                    \App\Models\QuestionOption::create([
                        'question_id' => $question->id,
                        'option_text' => 'False',
                        'option_order' => 1,
                    ]);
                } else {
                    // If question type changes from one with options to one without, delete all options
                    $question->options()->delete();
                }
            }
            DB::commit();
            return response()->json(['success' => true, 'redirect' => route('courses.show', $course->id)]);
        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quiz update failed: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            // Check if a file was uploaded and delete it if the transaction fails
            if ($request->hasFile('assessment_file') && isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            return response()->json(['error' => 'Failed to update quiz: ' . $e->getMessage()], 500);
        }
    }


    public function showQuiz(Course $course, Assessment $assessment)
    {
        // Important: While you confirmed IDs match, this check is good practice for security and robustness.
        // It ensures the assessment truly belongs to the course passed in the URL.
        if ($assessment->course_id !== $course->id) {
            // Log the discrepancy for debugging purposes
            Log::warning("Mismatched course_id for assessment. Course ID in URL: {$course->id}, Assessment course_id: {$assessment->course_id}, Assessment ID: {$assessment->id}");
            abort(404, 'Quiz not found in this course.'); // Or return redirect()->back()->withErrors(...)
        }

        // Load any necessary relationships for displaying the quiz, e.g., its questions and options.
        // 'questions.options' will load questions, and for each question, its options.
        $assessment->load('questions.options');

        // Return the view for displaying the quiz details, passing the course and assessment data.
        // Ensure you have a 'showQuiz.blade.php' file in resources/views/instructor/assessment/
        return view('instructor.assessment.showQuiz', compact('course', 'assessment'));
    }


    public function createAssignment(Course $course, $typeAct, Request $request)
    {
        $assessmentType = $typeAct;
        $topicId = $request->query('topic_id');
        return view('instructor.assessment.createAssignment', compact('course', 'assessmentType', 'topicId'));
    }

    public function storeAssignment(Request $request, Course $course)
    {
        Log::info($request->all());
        try {
            $validatedAssessmentData = $request->validate([
                'topic_id' => 'nullable|exists:topics,id',
                'title' => 'required|string|max:255',
                'type' => ['required', Rule::in(['assignment', 'activity', 'project'])], // Only allow assignment/activity here
                'description' => 'nullable|string',
                // Match B: allow any file type up to 100MB
                'assessment_file' => 'nullable|file|max:102400', // 100MB
                'available_at' => 'nullable|date',
                'unavailable_at' => 'nullable|date|after_or_equal:available_at', // Fixed typo here
                'duration_minutes' => 'nullable|integer|min:0', // Added for assignments/activities/projects
                'access_code' => 'nullable|string|max:255', // Added for assignments/activities/projects
            ]);

            // For assignment/activity, if no description, then file is required
            if (empty($validatedAssessmentData['description']) && !$request->hasFile('assessment_file')) {
                throw ValidationException::withMessages([
                    'description' => ['For Assignment or Activity, either a description or an assessment file is required.'],
                    'assessment_file' => ['For Assignment or Activity, either a description or an assessment file is required.'],
                ]);
            }

            DB::beginTransaction();

            $assessmentFilePath = null;
            if ($request->hasFile('assessment_file')) {
                $assessmentFilePath = $request->file('assessment_file')->store('assessments/' . $course->id, 'public');
            }

            // Corrected: Convert available_at and unavailable_at from local time to UTC
            $localTimezone = 'Asia/Manila';
            $availableAt = $validatedAssessmentData['available_at'] ? Carbon::parse($validatedAssessmentData['available_at'], $localTimezone)->setTimezone('UTC') : null;
            $unavailableAt = $validatedAssessmentData['unavailable_at'] ? Carbon::parse($validatedAssessmentData['unavailable_at'], $localTimezone)->setTimezone('UTC') : null;

            $assessment = Assessment::create([
                'course_id' => $course->id,
                'topic_id' => $validatedAssessmentData['topic_id'] ?? null,
                'title' => $validatedAssessmentData['title'],
                'type' => $validatedAssessmentData['type'],
                'description' => $validatedAssessmentData['description'] ?? null,
                'assessment_file_path' => $assessmentFilePath,
                'duration_minutes' => $validatedAssessmentData['duration_minutes'] ?? null, // Now included
                'access_code' => $validatedAssessmentData['access_code'] ?? null, // Now included
                'available_at' => $availableAt,
                'unavailable_at' => $unavailableAt,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json(['success' => 'Assessment created successfully!', 'redirect' => route('courses.show', $course->id)]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($assessmentFilePath) && Storage::disk('public')->exists($assessmentFilePath)) {
                Storage::disk('public')->delete($assessmentFilePath);
            }
            Log::error('Assignment/Activity creation failed: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Failed to create assessment: ' . $e->getMessage()], 500);
        }
    }

    public function editAssignment(Course $course, Assessment $assessment)
    {
        // Ensure the assessment belongs to the course and is of the correct type
        if ($assessment->course_id !== $course->id || !in_array($assessment->type, ['assignment', 'activity', 'project'])) {
            abort(404);
        }

        $assessmentType = $assessment->type;
        $topicId = $assessment->topic_id; // Pass topicId if it exists

        return view('instructor.assessment.createAssignment', compact('course', 'assessmentType', 'topicId', 'assessment'));
    }

    public function updateAssignment(Request $request, Course $course, Assessment $assessment)
    {
        // Ensure the assessment belongs to the course and is of the correct type
        if ($assessment->course_id !== $course->id || !in_array($assessment->type, ['assignment', 'activity', 'project'])) {
            Log::warning("Mismatched course_id or invalid type for assignment update. Course ID in URL: {$course->id}, Assessment course_id: {$assessment->course_id}, Assessment ID: {$assessment->id}, Type: {$assessment->type}");
            abort(403, 'Unauthorized action or invalid assessment type for this operation.');
        }

        DB::beginTransaction();
        $filePath = $assessment->assessment_file_path; // Keep existing path by default
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                // Match B: allow any file type up to 100MB
                'assessment_file' => 'nullable|file|max:102400', // 100MB
                'clear_assessment_file' => 'nullable|boolean', // New field for clearing file
                'available_at' => 'nullable|date',
                'unavailable_at' => 'nullable|date|after_or_equal:available_at',
                'duration_minutes' => 'nullable|integer|min:0',
                'access_code' => 'nullable|string|max:255',
                'topic_id' => 'nullable|exists:topics,id',
            ]);

            // For assignment/activity, if no description AND no existing file AND no new file, then file is required
            if (empty($validatedData['description']) && !$request->hasFile('assessment_file') && !$assessment->assessment_file_path) {
                 throw ValidationException::withMessages([
                    'description' => ['For this assessment type, either a description or an assessment file is required.'],
                    'assessment_file' => ['For this assessment type, either a description or an assessment file is required.'],
                ]);
            }

            // Handle file upload or removal
            if ($request->hasFile('assessment_file')) {
                // Delete old file if exists
                if ($filePath) {
                    Storage::disk('public')->delete($filePath);
                }
                $filePath = $request->file('assessment_file')->store('assessments/' . $course->id, 'public');
            } elseif ($request->input('clear_assessment_file') == '1') {
                // If "remove current file" checkbox is checked and no new file uploaded
                if ($filePath) {
                    Storage::disk('public')->delete($filePath);
                }
                $filePath = null;
            }

            $localTimezone = 'Asia/Manila';
            $availableAt = $validatedData['available_at'] ? Carbon::parse($validatedData['available_at'], $localTimezone)->setTimezone('UTC') : null;
            $unavailableAt = $validatedData['unavailable_at'] ? Carbon::parse($validatedData['unavailable_at'], $localTimezone)->setTimezone('UTC') : null;

            // Update the assessment
            $assessment->fill([
                'topic_id' => $validatedData['topic_id'] ?? null,
                'title' => $validatedData['title'],
                'description' => $validatedData['description'] ?? null,
                'assessment_file_path' => $filePath, // Update file path
                'duration_minutes' => $validatedData['duration_minutes'] ?? null,
                'access_code' => $validatedData['access_code'] ?? null,
                'available_at' => $availableAt,
                'unavailable_at' => $unavailableAt,
            ])->save();

            DB::commit();

            return response()->json(['success' => true, 'redirect' => route('courses.show', $course->id)]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            // If a new file was uploaded but transaction failed, delete the newly uploaded file
            if ($request->hasFile('assessment_file') && isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
            Log::error('Assignment update failed: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            return response()->json(['error' => 'Failed to update assessment: ' . $e->getMessage()], 500);
        }
    }


    public function showAssignment(Course $course, Assessment $assessment)
    {
        if ($assessment->course_id !== $course->id) {
            // Log the discrepancy for debugging purposes
            Log::warning("Mismatched course_id for assessment. Course ID in URL: {$course->id}, Assessment course_id: {$assessment->course_id}, Assessment ID: {$assessment->id}");
            abort(404, 'Quiz not found in this course.'); // Or return redirect()->back()->withErrors(...)
        }
        return view('instructor.assessment.showAssignment', compact('course' , 'assessment'));
    }

}