<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class UserController extends Controller
{
    public function getOperator()
    {
        try {
            $users = User::with('role')->get()
                ->makeHidden(['email', 'email_verified_at', 'role_id', 'created_at', 'updated_at']);

            $operators = $users->filter(function ($user) {
                return $user->role->name === 'Operator';
            })->values()->toArray();

            return response()->json([
                'status' => 'success',
                'operators' => $operators
            ], ResponseAlias::HTTP_OK);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'failed',
                'message' => $exception->getMessage(),
                'errors' => $exception->getTrace()
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
