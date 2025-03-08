<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Operator
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        //        user data
        $userData = User::with('role')->find(Auth::id());
//        check user role
        $userRole = $userData['role']['name'];

//        validate user role
        if ($userRole === 'Operator') {
//            process to request
            return $next($request);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'You are not allowed to access this action.',
                'errors' => []
            ], Response::HTTP_FORBIDDEN);
        }
    }
}
