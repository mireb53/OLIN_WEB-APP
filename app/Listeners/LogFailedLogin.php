<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\DB;

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
    }
}
