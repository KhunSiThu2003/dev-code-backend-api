<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInstructorRole
{
    /**
     * Handle an incoming request. Allow only users with admin role.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        if ($request->user()->role !== 'instructor') {
            return response()->json([
                'success' => false,
                'message' => 'Only instructors can perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
