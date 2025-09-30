<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;
use App\Models\User;

class CreateTestNotifications extends Command
{
    protected $signature = 'notifications:test {user_id?}';
    protected $description = 'Create test notifications for admin users';

    public function handle()
    {
        $userId = $this->argument('user_id') ?? 1;
        
        // Check if user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }

        // Create test notifications
        NotificationService::createNotification(
            $userId,
            'new_registration',
            'New Student Registration',
            'A new student has registered: Juan Dela Cruz',
            ['user_id' => 123, 'user_role' => 'student']
        );

        NotificationService::createNotification(
            $userId,
            'security_alert',
            'Security Alert',
            'Multiple failed login attempts detected from IP: 192.168.1.100',
            ['ip_address' => '192.168.1.100', 'attempts' => 5]
        );

        NotificationService::createNotification(
            $userId,
            'system_alert',
            'System Maintenance',
            'Scheduled system maintenance will begin in 2 hours',
            ['maintenance_time' => now()->addHours(2)]
        );

        NotificationService::createNotification(
            $userId,
            'new_registration',
            'New Instructor Registration',
            'A new instructor has registered: Maria Santos',
            ['user_id' => 456, 'user_role' => 'instructor']
        );

        $this->info("Test notifications created successfully for user ID: {$userId}");
        return 0;
    }
}