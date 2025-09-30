<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UpdateLastActivity
{
    /**
     * Handle an incoming request.
     * Update the authenticated user's last_activity_at timestamp on each web request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            if (Auth::check()) {
                $user = Auth::user();
                // Only touch the column if it exists to avoid migration race conditions
                if (\Schema::hasColumn('users', 'last_activity_at')) {
                    $user->forceFill(['last_activity_at' => now()])->saveQuietly();
                }
            }
        } catch (\Throwable $e) {
            // Swallow any errors to avoid breaking requests due to telemetry
        }

        return $response;
    }
}
