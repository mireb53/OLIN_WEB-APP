<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeSchool
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->isSchoolAdmin()) {
            // Force role filter scope if provided user is school admin
            $request->merge(['school_scope_id' => $user->school_id]);
        }
        return $next($request);
    }
}
