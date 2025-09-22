<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = \App\Models\User::all();
        
        if ($users->count() > 0) {
            $sampleLogs = [
                ['action' => 'User Login', 'details' => 'Successfully logged into the system'],
                ['action' => 'User Logout', 'details' => 'User logged out of the system'],
                ['action' => 'Course Created', 'details' => 'Created a new course: Introduction to Programming'],
                ['action' => 'Course Updated', 'details' => 'Updated course information for Database Systems'],
                ['action' => 'User Created', 'details' => 'Created a new user account'],
                ['action' => 'User Updated', 'details' => 'Updated user profile information'],
                ['action' => 'Assessment Submitted', 'details' => 'Student submitted Quiz #1 for Introduction to Programming'],
                ['action' => 'Material Uploaded', 'details' => 'Uploaded lecture material: Variables and Data Types.pdf'],
                ['action' => 'Grade Assigned', 'details' => 'Assigned grade for student assessment'],
                ['action' => 'Password Reset', 'details' => 'User requested password reset'],
            ];

            foreach ($users->take(5) as $user) {
                foreach ($sampleLogs as $log) {
                    \App\Models\ActivityLog::create([
                        'user_id' => $user->id,
                        'action' => $log['action'],
                        'details' => $log['details'],
                        'created_at' => \Carbon\Carbon::now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59)),
                        'updated_at' => \Carbon\Carbon::now()
                    ]);
                }
            }
        }
    }
}
