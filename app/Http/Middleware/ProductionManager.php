<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ProductionManager
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return \response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized',
                'errors' => []
            ], Response::HTTP_UNAUTHORIZED);
        } elseif ($user->role !== 'pm') {
            return \response()->json([
                'status' => 'failed',
                'message' => 'You do not have permission to access this action.',
                'errors' => []
            ], Response::HTTP_FORBIDDEN);
        } else {
            return $next($request);
        }
    }
}
