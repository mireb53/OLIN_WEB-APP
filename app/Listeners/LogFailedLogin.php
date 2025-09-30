<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Notification;

class LogFailedLogin
{
    public function handle(Failed $event): void
    {
        try {
            DB::table('failed_logins')->insert([
                'user_id' => $event->user?->id,
                'email' => is_array($event->credentials) ? ($event->credentials['email'] ?? null) : null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Do not interrupt auth flow
        }

        // Create security alert notification for admins
        try {
            $message = 'Failed login attempt for ' . (is_array($event->credentials) ? ($event->credentials['email'] ?? 'unknown') : 'unknown') . ' from IP ' . request()->ip();
            $recipients = User::whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_SCHOOL_ADMIN])->get();
            foreach ($recipients as $recipient) {
                Notification::create([
                    'user_id' => $recipient->id,
                    'type' => 'security_alert',
                    'title' => 'Security Alert: Failed Login',
                    'message' => $message,
                    'is_read' => false,
                ]);

                // Future enhancement (commented): send email for critical alerts
                // Mail::to($recipient->email)->queue(new \App\Mail\SecurityAlertMail($message));
            }
        } catch (\Throwable $e) {
            // swallow errors
        }

        // Future enhancement (commented): broadcast real-time event
        // event(new \App\Events\AdminNotificationCreated($payload));
    }
}
