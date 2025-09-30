<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Announcement;
use App\Models\User;
use App\Models\School;
use Carbon\Carbon;

class AnnouncementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin users to be authors
        $admins = User::whereIn('role', ['super_admin', 'school_admin'])->get();
        $schools = School::all();
        
        if ($admins->count() > 0) {
            $sampleAnnouncements = [
                [
                    'title' => 'Welcome to OLIN Learning Management System!',
                    'message' => 'We are excited to have you join our learning platform. Please explore all the features available to enhance your educational experience.',
                    'is_pinned' => true,
                    'expires_at' => null,
                ],
                [
                    'title' => 'System Maintenance Scheduled',
                    'message' => 'We will be performing routine maintenance on the system this Saturday from 10 PM to 2 AM. During this time, the platform may be temporarily unavailable.',
                    'is_pinned' => false,
                    'expires_at' => Carbon::now()->addDays(7),
                ],
                [
                    'title' => 'New Course Management Features',
                    'message' => 'New features have been added to the course management system including bulk enrollment, advanced analytics, and improved assessment tools.',
                    'is_pinned' => false,
                    'expires_at' => Carbon::now()->addDays(30),
                ],
                [
                    'title' => 'Security Update Reminder',
                    'message' => 'Please ensure your passwords are strong and consider enabling two-factor authentication for added security.',
                    'is_pinned' => false,
                    'expires_at' => null,
                ],
                [
                    'title' => 'End of Semester Preparations',
                    'message' => 'As we approach the end of the semester, please make sure all grades are submitted and final assessments are completed.',
                    'is_pinned' => true,
                    'expires_at' => Carbon::now()->addDays(14),
                ],
            ];

            foreach ($sampleAnnouncements as $announcement) {
                $randomAdmin = $admins->random();
                
                // If the admin is a school admin, assign their school; otherwise leave as system-wide
                $schoolId = ($randomAdmin->role === 'school_admin' && $randomAdmin->school_id) 
                    ? $randomAdmin->school_id 
                    : ($schools->count() > 0 ? $schools->random()->id : null);
                
                Announcement::create([
                    'title' => $announcement['title'],
                    'message' => $announcement['message'],
                    'author_id' => $randomAdmin->id,
                    'school_id' => $schoolId,
                    'status' => 'active',
                    'is_pinned' => $announcement['is_pinned'],
                    'expires_at' => $announcement['expires_at'],
                    'created_at' => Carbon::now()->subDays(rand(0, 7))->subHours(rand(0, 23)),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }
    }
}