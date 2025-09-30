<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\SubmittedAssessment;
use App\Models\SubmittedQuestion;
use App\Models\SubmittedQuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use App\Services\NotificationService;

class StudentSubmittedAssessmentController extends Controller
{
    public function startQuizAttempt(Assessment $assessment)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $normalizedAssessmentType = strtolower(trim($assessment->type));
        if (!in_array($normalizedAssessmentType, ['quiz', 'exam'])) {
            return response()->json(['message' => 'This assessment is not a quiz or exam type.'], 400);
        }

        if (!method_exists($user, 'isEnrolledInCourse') || !$user->isEnrolledInCourse($assessment->course_id)) {
            return response()->json(['message' => 'Unauthorized: Not enrolled in the course for this assessment.'], 403);
        }

        if (!$this->isAssessmentAvailable($assessment)) {
            return response()->json([
                'message' => 'Assessment is not yet available.',
                'available_at' => $assessment->available_at
            ], 400);
        }

        // Check for an existing in-progress attempt
        $existingInProgressAttempt = SubmittedAssessment::where('student_id', $user->id)
                                            ->where('assessment_id', $assessment->id)
                                            ->where('status', 'in_progress')
                                            ->first();

        if ($existingInProgressAttempt) {
            return response()->json([
                'message' => 'Resuming existing attempt.',
                'submitted_assessment' => $existingInProgressAttempt->load('submittedQuestions.submittedOptions')
            ], 200);
        }

        // Count completed attempts for the current user and assessment
        $completedAttemptsCount = SubmittedAssessment::where('student_id', $user->id)
                                             ->where('assessment_id', $assessment->id)
                                             ->whereIn('status', ['completed', 'graded']) // Consider 'graded' as well if it's a final state
                                             ->count();

        // Determine the next attempt number
        $nextAttemptNumber = $completedAttemptsCount + 1;

        // Check if max attempts limit is reached
        if ($assessment->max_attempts !== null && $completedAttemptsCount >= $assessment->max_attempts) {
            return response()->json([
                'message' => 'Maximum attempt limit reached. You have used ' . $completedAttemptsCount . ' out of ' . $assessment->max_attempts . ' attempts.',
                'max_attempts' => $assessment->max_attempts,
                'attempts_made' => $completedAttemptsCount,
                'can_attempt' => false // Explicitly state that no more attempts are allowed
            ], 400);
        }

        try {
            DB::beginTransaction();

            // Create a new submitted assessment record
            $submittedAssessment = SubmittedAssessment::create([
                'student_id' => $user->id,
                'assessment_id' => $assessment->id,
                'status' => 'in_progress',
                'attempt_number' => $nextAttemptNumber, 
                'started_at' => now(),
            ]);

            // Fetch questions for the assessment
            $questions = Question::where('assessment_id', $assessment->id)
                                 ->with('questionOptions')
                                 ->get();

            if ($questions->isEmpty()) {
                DB::rollBack();
                return response()->json(['message' => 'No questions found for this quiz/exam.'], 404);
            }

            foreach ($questions as $question) {
                if (!in_array($question->question_type, ['multiple_choice', 'true_false']) && $question->questionOptions->isNotEmpty()) {
                    Log::warning("Question ID {$question->id} has type '{$question->question_type}' but also has options. Options will be ignored for non-MC/TF types.");
                }

                $submittedQuestion = SubmittedQuestion::create([
                    'submitted_assessment_id' => $submittedAssessment->id,
                    'question_id' => $question->id,
                    'question_text' => $question->question_text,
                    'question_type' => $question->question_type,
                    'max_points' => $question->points,
                    'submitted_answer' => null, 
                    'is_correct' => null,
                    'score_earned' => $question->points,
                ]);

                // Create submitted options for MC and T/F questions
                if (in_array($question->question_type, ['multiple_choice', 'true_false'])) {
                    foreach ($question->questionOptions as $option) {
                        SubmittedQuestionOption::create([
                            'submitted_question_id' => $submittedQuestion->id,
                            'question_option_id' => $option->id, // Use the actual option ID
                            'option_text' => $option->option_text,
                            // CORRECTED: Compare option_order with the question's correct_answer
                            'is_correct_option' => $option->option_order == $question->correct_answer, 
                            'is_selected' => false,
                        ]);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Quiz attempt started successfully.',
                'submitted_assessment' => $submittedAssessment->load('submittedQuestions.submittedOptions')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error starting quiz attempt: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to start quiz attempt. Please try again.'], 500);
        }
    }

    public function syncOfflineQuiz(Request $request, Assessment $assessment)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        try {
            // ... validation and data parsing ...
            $validated = $request->validate([
                'answers' => 'required|array',
                'answers.*.question_id' => 'required|integer|exists:questions,id',
                'answers.*.question_type' => 'required|string',
                'answers.*.submitted_answer' => 'nullable|string',
                'answers.*.selected_options' => 'nullable|array',
                'answers.*.selected_options.*' => 'integer',
                'started_at' => 'required|date',
                'completed_at' => 'required|date',
                'submitted_at' => 'required|string',
            ]);
            
            $answersData = $validated['answers'];
            if (is_string($answersData)) {
                $answersData = json_decode($answersData, true);
            }

            if (empty($answersData)) {
                return response()->json(['message' => 'No answers provided.'], 400);
            }

            DB::beginTransaction();

            $completedAttemptsCount = SubmittedAssessment::where('student_id', $user->id)
                ->where('assessment_id', $assessment->id)
                ->whereIn('status', ['completed', 'graded'])
                ->count();
            $nextAttemptNumber = $completedAttemptsCount + 1;

            $submittedAssessment = SubmittedAssessment::create([
                'assessment_id' => $assessment->id,
                'student_id' => $user->id,
                'status' => 'completed',
                'started_at' => $validated['started_at'],
                'completed_at' => $validated['completed_at'],
                'submitted_at' => $validated['submitted_at'],
                'attempt_number' => $nextAttemptNumber,
                'is_passed' => false,
            ]);

            $totalPointsEarned = 0;
            $totalPossiblePoints = 0;

            $questions = $assessment->questions()->with('options')->get();
            $questionsById = $questions->keyBy('id');

            foreach ($answersData as $answer) {
                $originalQuestion = $questionsById->get($answer['question_id']);
                if (!$originalQuestion) continue;

                $totalPossiblePoints += $originalQuestion->points;
                
                // Initialize scoring variables
                $isCorrect = false;
                $pointsEarned = 0;
                
                $submittedAnswer = $answer['submitted_answer'] ?? '';
    
                // Create submitted question
                $submittedQuestion = SubmittedQuestion::create([
                    'submitted_assessment_id' => $submittedAssessment->id,
                    'question_id' => $originalQuestion->id,
                    'question_text' => $originalQuestion->question_text,
                    'question_type' => $originalQuestion->question_type,
                    'max_points' => $originalQuestion->points,
                    'submitted_answer' => $submittedAnswer, // ✅ Always a string
                    'is_correct' => $isCorrect,
                ]);

                // GRADING LOGIC - This was missing in the original method
                if ($originalQuestion->question_type === 'multiple_choice') {
                    // For multiple choice, check if selected option's order matches correct_answer
                    $selectedOptions = collect($answer['selected_options'] ?? []);
                    
                    if ($selectedOptions->isNotEmpty()) {
                        foreach ($selectedOptions as $selectedOptionId) {
                            $questionOption = QuestionOption::find($selectedOptionId);
                            if ($questionOption && $questionOption->option_order == $originalQuestion->correct_answer) {
                                $isCorrect = true;
                                break;
                            }
                        }
                    }

                    if ($isCorrect) {
                        $pointsEarned = $originalQuestion->points;
                        $totalPointsEarned += $pointsEarned;
                    }

                    // Create submitted options for tracking
                    foreach ($originalQuestion->options as $option) {
                        SubmittedQuestionOption::create([
                            'submitted_question_id' => $submittedQuestion->id,
                            'question_option_id' => $option->id,
                            'option_text' => $option->option_text,
                            'is_correct_option' => $option->is_correct ?? false,
                            'is_selected' => $selectedOptions->contains($option->id),
                        ]);
                    }
                    
                } elseif ($originalQuestion->question_type === 'true_false') {
                    // For true/false, use the same validation logic as finalizeQuizAttempt
                    $isCorrect = $this->validateTrueFalseAnswerForOffline($answer, $originalQuestion);
                    
                    if ($isCorrect) {
                        $pointsEarned = $originalQuestion->points;
                        $totalPointsEarned += $pointsEarned;
                    }

                    // Create submitted options for tracking
                    $selectedOptions = collect($answer['selected_options'] ?? []);
                    foreach ($originalQuestion->options as $option) {
                        SubmittedQuestionOption::create([
                            'submitted_question_id' => $submittedQuestion->id,
                            'question_option_id' => $option->id,
                            'option_text' => $option->option_text,
                            'is_correct_option' => $option->is_correct ?? false,
                            'is_selected' => $selectedOptions->contains($option->id),
                        ]);
                    }
                    
                } elseif (in_array($originalQuestion->question_type, ['short_answer', 'identification'])) {
                    // Simple case-insensitive string comparison for text-based answers
                    if ($answer['submitted_answer'] && $originalQuestion->correct_answer) {
                        $isCorrect = strtolower(trim($answer['submitted_answer'])) === strtolower(trim($originalQuestion->correct_answer));
                    }
                    
                    if ($isCorrect) {
                        $pointsEarned = $originalQuestion->points;
                        $totalPointsEarned += $pointsEarned;
                    }
                    
                } else {
                    // For question types that require manual grading (e.g., essay), score is 0 initially.
                    $pointsEarned = 0;
                }

                // Update the submitted question with calculated score and correctness
                $submittedQuestion->update([
                    'is_correct' => $isCorrect ? 1 : 0,
                    'score_earned' => $pointsEarned,
                ]);
            }

            // Update the submission with final score
            $score = $totalPointsEarned;
            $passingScore = ($assessment->passing_percentage ?? 50) / 100 * $totalPossiblePoints;
            $isPassed = $totalPointsEarned >= $passingScore;

            $submittedAssessment->update([
                'score' => $score,
                'is_passed' => $isPassed,
            ]);

            DB::commit();

            return response()->json(['score' => $score, 'is_passed' => $isPassed], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error syncing offline quiz: ' . $e->getMessage(), [
                'exception' => $e,
                'line' => $e->getLine()
            ]);
            return response()->json([
                'message' => 'An error occurred while syncing the quiz.',
                'error' => $e->getMessage(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    private function validateTrueFalseAnswerForOffline($answerData, $originalQuestion)
    {
        // Strategy 1: Check selected options (if using option-based true/false)
        $selectedOptionIds = collect($answerData['selected_options'] ?? []);
        
        if ($selectedOptionIds->isNotEmpty()) {
            foreach ($selectedOptionIds as $selectedOptionId) {
                $questionOption = QuestionOption::find($selectedOptionId);
                if ($questionOption && $questionOption->option_order == $originalQuestion->correct_answer) {
                    return true;
                }
            }
        }

        // Strategy 2: Check submitted_answer text directly
        if (isset($answerData['submitted_answer']) && $originalQuestion->correct_answer) {
            $submittedAnswer = strtolower(trim($answerData['submitted_answer']));
            $correctAnswer = strtolower(trim($originalQuestion->correct_answer));
            
            // Direct text comparison
            if ($submittedAnswer === $correctAnswer) {
                return true;
            }

            // Handle common true/false variations
            $trueVariations = ['true', 't', '1', 'yes', 'correct'];
            $falseVariations = ['false', 'f', '0', 'no', 'incorrect'];
            
            $isSubmittedTrue = in_array($submittedAnswer, $trueVariations);
            $isSubmittedFalse = in_array($submittedAnswer, $falseVariations);
            $isCorrectTrue = in_array($correctAnswer, $trueVariations);
            $isCorrectFalse = in_array($correctAnswer, $falseVariations);
            
            if (($isSubmittedTrue && $isCorrectTrue) || ($isSubmittedFalse && $isCorrectFalse)) {
                return true;
            }

            // Strategy 3: If correct_answer is numeric (option_order), match with option text
            if (is_numeric($correctAnswer)) {
                $correctOption = QuestionOption::where('question_id', $originalQuestion->id)
                    ->where('option_order', $correctAnswer)
                    ->first();
                
                if ($correctOption) {
                    $correctOptionText = strtolower(trim($correctOption->option_text));
                    if ($submittedAnswer === $correctOptionText) {
                        return true;
                    }
                    
                    // Check if option text matches true/false variations
                    if (($isSubmittedTrue && in_array($correctOptionText, $trueVariations)) ||
                        ($isSubmittedFalse && in_array($correctOptionText, $falseVariations))) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    public function submitAssignment(Request $request, Assessment $assessment)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $normalizedAssessmentType = strtolower(trim($assessment->type));
        if (!in_array($normalizedAssessmentType, ['assignment', 'activity', 'project'])) {
            return response()->json(['message' => 'This assessment is not an assignment, activity, or project type.'], 400);
        }

        if (!method_exists($user, 'isEnrolledInCourse') || !$user->isEnrolledInCourse($assessment->course_id)) {
            return response()->json(['message' => 'Unauthorized: Not enrolled in the course for this assignment.'], 403);
        }

        if (!$this->isAssessmentAvailable($assessment)) {
            return response()->json([
                'message' => 'Assessment is not yet available.',
                'available_at' => $assessment->available_at
            ], 400);
        }

        try {
            $request->validate([
                'assignment_file' => 'required|file|max:10240|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,txt,zip,rar,jpg,jpeg,png',
                // Add submitted_at as an optional validation rule
                'submitted_at' => 'sometimes|date',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

        // Initialize variables
        $filePath = null;
        $oldFilePath = null;

        try {
            DB::beginTransaction();

            // Get existing submission BEFORE processing the new file
            $existingSubmission = SubmittedAssessment::where('student_id', $user->id)
                                                ->where('assessment_id', $assessment->id)
                                                ->first();

            // Store old file path if it exists
            if ($existingSubmission && $existingSubmission->submitted_file_path) {
                $oldFilePath = $existingSubmission->submitted_file_path;
                Log::info("Existing submission found. Old file path: {$oldFilePath}");
            }

            // Process the new file upload
            if ($request->hasFile('assignment_file')) {
                $file = $request->file('assignment_file');
                $originalFileName = $file->getClientOriginalName();
                // Clean the filename to prevent issues
                $cleanFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalFileName);
                // Check if file with same name already exists and handle conflicts
                $directory = 'submitted_assignments/' . $user->id . '/' . $assessment->id;
                $finalFileName = $cleanFileName;
                $counter = 1;
                // If the same filename exists and it's not the current user's existing file, add a counter
                while (Storage::disk('public')->exists($directory . '/' . $finalFileName)) {
                    $pathInfo = pathinfo($cleanFileName);
                    $baseName = $pathInfo['filename'];
                    $extension = isset($pathInfo['extension']) ? '.' . $pathInfo['extension'] : '';
                    $finalFileName = $baseName . '_' . $counter . $extension;
                    $counter++;
                    // Break if we've tried too many times to avoid infinite loop
                    if ($counter > 100) {
                        $finalFileName = time() . '_' . $cleanFileName;
                        break;
                    }
                }
                // Store the file with the final filename
                $filePath = $file->storeAs($directory, $finalFileName, 'public');
                Log::info("New file uploaded. Original name: {$originalFileName}, Final path: {$filePath}");
                // Verify the file was actually stored
                if (!Storage::disk('public')->exists($filePath)) {
                    throw new \Exception("Failed to store the uploaded file");
                }
            } else {
                throw new \Exception("No file uploaded");
            }

            // Get the submitted_at timestamp from the request, falling back to current time
            $submittedAt = $request->input('submitted_at', now());

            // Create or update the submitted assessment record
            $submittedAssessment = SubmittedAssessment::updateOrCreate(
                ['student_id' => $user->id, 'assessment_id' => $assessment->id],
                [
                    'submitted_file_path' => $filePath,
                    'original_filename' => $originalFileName,
                    'status' => 'submitted', // Change status to 'submitted'
                    'submitted_at' => $submittedAt, // Use the submitted_at from the request or the current time
                    'attempt_number' => DB::raw('attempt_number + 1') // Increment attempt number
                ]
            );

            // Clean up old file if a new one was successfully uploaded
            if ($oldFilePath && Storage::disk('public')->exists($oldFilePath)) {
                Storage::disk('public')->delete($oldFilePath);
                Log::info("Old file deleted successfully: {$oldFilePath}");
            }

            DB::commit();

            // Notify the instructor about the assignment submission
            NotificationService::notifyInstructorAssignmentSubmission(
                $assessment->course->instructor_id,
                $user,
                $assessment
            );

            return response()->json([
                'message' => 'Assignment submitted successfully.',
                'submitted_at' => $submittedAssessment->submitted_at,
                'submitted_assessment' => $submittedAssessment
            ], 200);

        } catch (ValidationException $e) {
            DB::rollBack();
            Log::error('Validation error during assignment submission: ' . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting assignment: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to submit assignment. Please try again.'], 500);
        }
    }

    public function showSubmittedAssessment(SubmittedAssessment $submittedAssessment)
    {
        $user = Auth::user();
        if (!$user || $submittedAssessment->student_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to view this submission.'], 403);
        }

        $submittedAssessment->load([
            'assessment',
            'submittedQuestions.submittedOptions'
        ]);

        return response()->json(['submitted_assessment' => $submittedAssessment]);
    }
    public function updateSubmittedQuestionAnswer(Request $request, SubmittedQuestion $submittedQuestion)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Check if the submitted question belongs to the authenticated user
        $submittedAssessment = $submittedQuestion->submittedAssessment;
        if ($submittedAssessment->student_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to update this answer.'], 403);
        }

        // Check if the assessment is still in progress
        if ($submittedAssessment->status !== 'in_progress') {
            return response()->json(['message' => 'Cannot update answers for a completed assessment.'], 400);
        }

        try {
            if (in_array($submittedQuestion->question_type, ['multiple_choice', 'true_false'])) {
                // Handle multiple choice/true false
                $request->validate([
                    'selected_option_ids' => 'required|array',
                    'selected_option_ids.*' => 'integer|exists:submitted_question_options,question_option_id'
                ]);

                DB::beginTransaction();

                // Reset all options for this question
                SubmittedQuestionOption::where('submitted_question_id', $submittedQuestion->id)
                    ->update(['is_selected' => false]);

                $selectedOptionIds = $request->selected_option_ids;

                // Set selected options
                if (!empty($selectedOptionIds)) {
                    SubmittedQuestionOption::where('submitted_question_id', $submittedQuestion->id)
                        ->whereIn('question_option_id', $selectedOptionIds)
                        ->update(['is_selected' => true]);
                }

                // Fetch the newly selected options to update the submitted_answer field
                $newlySelectedOptions = SubmittedQuestionOption::where('submitted_question_id', $submittedQuestion->id)
                                                              ->where('is_selected', true)
                                                              ->get();

                // Format the submitted answer based on the selected options
                $submittedAnswerText = $newlySelectedOptions->pluck('option_text')->implode(', ');
                $submittedQuestion->update(['submitted_answer' => $submittedAnswerText]);

                DB::commit();

            } else {
                // Handle text-based questions
                $request->validate([
                    'submitted_answer' => 'nullable|string|max:5000'
                ]);

                $submittedQuestion->update([
                    'submitted_answer' => $request->submitted_answer
                ]);
            }

            return response()->json([
                'message' => 'Answer updated successfully.',
                'submitted_question' => $submittedQuestion->fresh(['submittedOptions'])
            ]);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating submitted question answer: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to update answer. Please try again.'], 500);
        }
    }
    public function finalizeQuizAttempt(SubmittedAssessment $submittedAssessment)
    {
        $user = Auth::user();
        if ($submittedAssessment->student_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized to finalize this assessment.'], 403);
        }

        if ($submittedAssessment->status !== 'in_progress') {
            return response()->json(['message' => 'This assessment has already been finalized.'], 400);
        }

        DB::beginTransaction();

        try {
            $totalScore = 0;
            
            // Eager load the necessary relationships to avoid N+1 queries
            $submittedQuestions = $submittedAssessment->submittedQuestions()->with([
                'question.options',
                'submittedOptions'
            ])->get();

            foreach ($submittedQuestions as $submittedQuestion) {
                $isCorrect = false;
                $scoreEarned = 0;
                $questionType = $submittedQuestion->question_type;
                
                $originalQuestion = $submittedQuestion->question;

                if ($questionType === 'multiple_choice') {
                    // For multiple choice, check if selected option's order matches correct_answer
                    $selectedOptions = $submittedQuestion->submittedOptions()
                        ->where('is_selected', true)
                        ->get();

                    if ($selectedOptions->isNotEmpty()) {
                        foreach ($selectedOptions as $selectedOption) {
                            $questionOption = QuestionOption::find($selectedOption->question_option_id);
                            if ($questionOption && $questionOption->option_order == $originalQuestion->correct_answer) {
                                $isCorrect = true;
                                break;
                            }
                        }
                    }

                    if ($isCorrect) {
                        $scoreEarned = $submittedQuestion->max_points;
                    }

                } elseif ($questionType === 'true_false') {
                    // For true/false, use multiple validation approaches
                    $isCorrect = $this->validateTrueFalseAnswer($submittedQuestion, $originalQuestion);
                    
                    if ($isCorrect) {
                        $scoreEarned = $submittedQuestion->max_points;
                    }

                } elseif (in_array($questionType, ['short_answer', 'identification'])) {
                    // Simple case-insensitive string comparison
                    if ($submittedQuestion->submitted_answer && $originalQuestion->correct_answer) {
                        $isCorrect = strtolower(trim($submittedQuestion->submitted_answer)) === strtolower(trim($originalQuestion->correct_answer));
                    }
                    
                    if ($isCorrect) {
                        $scoreEarned = $submittedQuestion->max_points;
                    }

                } else {
                    // For question types that require manual grading (e.g., essay), score is 0 initially.
                    $scoreEarned = 0;
                }

                // Update the submitted question record with the calculated score and correctness
                $submittedQuestion->update([
                    'is_correct' => $isCorrect ? 1 : 0,
                    'score_earned' => $scoreEarned
                ]);
                
                $totalScore += $scoreEarned;
            }

            // Update the submitted assessment status and final score
            $submittedAssessment->update([
                'score' => $totalScore,
                'status' => 'completed',
                'submitted_at' => now(),
                'completed_at' => now(),
            ]);

            DB::commit();

            // Notify the instructor about the quiz submission
            $assessment = $submittedAssessment->assessment;
            if ($assessment && $assessment->course && $assessment->course->instructor_id) {
                NotificationService::notifyInstructorAssignmentSubmission(
                    $assessment->course->instructor_id,
                    $user,
                    $assessment
                );
            }

            return response()->json([
                'message' => 'Quiz completed successfully!',
                'submitted_assessment' => $submittedAssessment->fresh(['submittedQuestions.submittedOptions'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error finalizing quiz attempt: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Failed to finalize quiz. Please try again.'], 500);
        }
    }

    private function validateTrueFalseAnswer($submittedQuestion, $originalQuestion)
    {
        // Strategy 1: Check selected options (if using option-based true/false)
        $selectedOptions = $submittedQuestion->submittedOptions()
            ->where('is_selected', true)
            ->get();

        if ($selectedOptions->isNotEmpty()) {
            foreach ($selectedOptions as $selectedOption) {
                $questionOption = QuestionOption::find($selectedOption->question_option_id);
                if ($questionOption && $questionOption->option_order == $originalQuestion->correct_answer) {
                    return true;
                }
            }
        }

        // Strategy 2: Check submitted_answer text directly
        if ($submittedQuestion->submitted_answer && $originalQuestion->correct_answer) {
            $submittedAnswer = strtolower(trim($submittedQuestion->submitted_answer));
            $correctAnswer = strtolower(trim($originalQuestion->correct_answer));
            
            // Direct text comparison
            if ($submittedAnswer === $correctAnswer) {
                return true;
            }

            // Handle common true/false variations
            $trueVariations = ['true', 't', '1', 'yes', 'correct'];
            $falseVariations = ['false', 'f', '0', 'no', 'incorrect'];
            
            $isSubmittedTrue = in_array($submittedAnswer, $trueVariations);
            $isSubmittedFalse = in_array($submittedAnswer, $falseVariations);
            $isCorrectTrue = in_array($correctAnswer, $trueVariations);
            $isCorrectFalse = in_array($correctAnswer, $falseVariations);
            
            if (($isSubmittedTrue && $isCorrectTrue) || ($isSubmittedFalse && $isCorrectFalse)) {
                return true;
            }

            // Strategy 3: If correct_answer is numeric (option_order), match with option text
            if (is_numeric($correctAnswer)) {
                $correctOption = QuestionOption::where('question_id', $originalQuestion->id)
                    ->where('option_order', $correctAnswer)
                    ->first();
                
                if ($correctOption) {
                    $correctOptionText = strtolower(trim($correctOption->option_text));
                    if ($submittedAnswer === $correctOptionText) {
                        return true;
                    }
                    
                    // Check if option text matches true/false variations
                    if (($isSubmittedTrue && in_array($correctOptionText, $trueVariations)) ||
                        ($isSubmittedFalse && in_array($correctOptionText, $falseVariations))) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
    public function getAttemptStatus(Assessment $assessment)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Only applicable for quiz/exam types
        $normalizedAssessmentType = strtolower(trim($assessment->type));
        if (!in_array($normalizedAssessmentType, ['quiz', 'exam'])) {
            return response()->json(['message' => 'Attempt status is only applicable for quiz or exam types.'], 400);
        }

        // Count completed attempts
        $completedAttemptsCount = SubmittedAssessment::where('student_id', $user->id)
                                             ->where('assessment_id', $assessment->id)
                                             ->whereIn('status', ['completed', 'graded'])
                                             ->count();

        // Check for an in-progress attempt
        $inProgressAttempt = SubmittedAssessment::where('student_id', $user->id)
                                            ->where('assessment_id', $assessment->id)
                                            ->where('status', 'in_progress')
                                            ->first();

        $maxAttempts = $assessment->max_attempts;
        $canAttempt = true;
        $attemptsRemaining = null;

        if ($maxAttempts !== null) {
            $attemptsRemaining = $maxAttempts - $completedAttemptsCount;
            if ($attemptsRemaining <= 0) {
                $canAttempt = false;
            }
        }

        if ($inProgressAttempt) {
            $canAttempt = true;
        }


        return response()->json([
            'max_attempts' => $maxAttempts,
            'attempts_made' => $completedAttemptsCount,
            'attempts_remaining' => $attemptsRemaining,
            'can_start_new_attempt' => $canAttempt && !$inProgressAttempt, 
            'has_in_progress_attempt' => (bool) $inProgressAttempt,
            'in_progress_submitted_assessment_id' => $inProgressAttempt ? $inProgressAttempt->id : null,
        ]);
    }
    public function getLatestSubmittedAssignment(Assessment $assessment)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Ensure it's an assignment-like type
        $normalizedAssessmentType = strtolower(trim($assessment->type));
        if (!in_array($normalizedAssessmentType, ['assignment', 'activity', 'project'])) {
            return response()->json(['message' => 'This is not an assignment, activity, or project type.'], 400);
        }

        $latestSubmission = SubmittedAssessment::where('student_id', $user->id)
                                            ->where('assessment_id', $assessment->id)
                                            ->whereNotNull('submitted_file_path')
                                            ->latest('submitted_at') // Get the most recent submission
                                            ->first();

        if ($latestSubmission) {
            $fileUrl = Storage::url($latestSubmission->submitted_file_path);

            // Use original filename if available, otherwise fallback to extracting from path
            $fileName = $latestSubmission->original_filename ?? basename($latestSubmission->submitted_file_path);

            return response()->json([
                'has_submitted_file' => true,
                'submitted_file_path' => $latestSubmission->submitted_file_path,
                'submitted_file_url' => $fileUrl,
                'submitted_file_name' => $fileName,
                'original_filename' => $latestSubmission->original_filename ?? $fileName, // Fallback to extracted filename
                'submitted_at' => $latestSubmission->submitted_at,
                'status' => $latestSubmission->status,
            ]);
        }

        return response()->json([
            'has_submitted_file' => false,
            'submitted_file_path' => null,
            'submitted_file_url' => null,
            'submitted_file_name' => null,
            'original_filename' => null,
            'submitted_at' => null,
            'status' => null,
        ]);
    }
    private function isAssessmentAvailable(Assessment $assessment)
    {
        $now = now();

        if ($assessment->available_at && $now->lt($assessment->available_at)) {
            return false;
        }

        if ($assessment->unavailable_at && $now->gt($assessment->unavailable_at)) {
            return false;
        }

        return true;
    }
}