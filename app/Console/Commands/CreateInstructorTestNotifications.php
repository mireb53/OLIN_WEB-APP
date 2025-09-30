<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Models\User;

class CreateInstructorTestNotifications extends Command
{
    protected $signature = 'notifications:instructor-test {user_id?}';
    protected $description = 'Create test notifications for instructor users';

    public function handle()
    {
        // Find first instructor or use provided user ID
        $userId = $this->argument('user_id');
        
        if ($userId) {
            $user = User::find($userId);
        } else {
            $user = User::where('role', 'instructor')->first();
            if ($user) {
                $userId = $user->id;
            }
        }
        
        if (!$user) {
            $this->error("No instructor user found");
            return 1;
        }

        $this->info("Creating notifications for instructor: {$user->name} (ID: {$user->id})");

        // Create test notifications for instructor
        NotificationService::createNotification(
            $userId,
            'student_enrollment',
            'New Student Enrollment',
            'Juan Dela Cruz has enrolled in Programming 101',
            ['student_id' => 123, 'course_id' => 1]
        );

        NotificationService::createNotification(
            $userId,
            'assignment_submission',
            'New Assignment Submission',
            'Maria Santos submitted Programming Assignment #1',
            ['student_id' => 456, 'assessment_id' => 5]
        );

        NotificationService::createNotification(
            $userId,
            'course_dropout',
            'Student Dropped Course',
            'Pedro Rodriguez dropped from Web Development Course',
            ['student_id' => 789, 'course_id' => 3]
        );

        NotificationService::createNotification(
            $userId,
            'grading_deadline',
            'Grading Deadline Reminder',
            'Grading deadline for Midterm Exam is approaching (2 days remaining)',
            ['assessment_id' => 8, 'deadline' => now()->addDays(2)]
        );

        $this->info("Test notifications created successfully for instructor ID: {$userId}");
        return 0;
    }
}