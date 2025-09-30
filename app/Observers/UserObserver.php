<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\User;

class UserObserver
{
    public function created(User $user): void
    {
        // Notify admins when certain user types are created
        $type = null;
        $title = null;
        $message = null;

        if ($user->role === User::ROLE_STUDENT) {
            $type = 'new_registration';
            $title = 'New Student Registered';
            $message = sprintf('Student %s (%s) has registered.', $user->name, $user->email);
        } elseif ($user->role === User::ROLE_INSTRUCTOR) {
            $type = 'new_registration';
            $title = 'New Instructor Added';
            $message = sprintf('Instructor %s (%s) was added.', $user->name, $user->email);
        }

        if (!$type) return;

        // Recipients: all super admins and relevant school admins
        $recipients = User::query()
            ->whereIn('role', [User::ROLE_SUPER_ADMIN, User::ROLE_SCHOOL_ADMIN])
            ->when($user->school_id, function($q) use ($user) {
                $q->orWhere(function($qq) use ($user) {
                    $qq->where('role', User::ROLE_SCHOOL_ADMIN)
                       ->where('school_id', $user->school_id);
                });
            })
            ->get();

        foreach ($recipients as $recipient) {
            Notification::create([
                'user_id' => $recipient->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'is_read' => false,
            ]);

            // Future enhancement (commented): optionally email admins about important registrations
            // if ($user->role === User::ROLE_INSTRUCTOR) {
            //     Mail::to($recipient->email)->queue(new \App\Mail\NewInstructorMail($user));
            // }
        }

        // Future enhancement (commented): broadcast real-time event to admins
        // event(new \App\Events\AdminNotificationCreated([
        //     'type' => $type,
        //     'title' => $title,
        //     'message' => $message,
        // ]));
    }
}
