<?php

namespace App\Http\Controllers\API\auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class AuthController extends Controller
{
//    login process
    public function login(Request $request)
    {
        try {
            //        Validate request
            $credentials = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);

//        check validation
            if ($credentials->fails()) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Validation failed',
                    'errors' => $credentials->errors()
                ], ResponseAlias::HTTP_BAD_REQUEST);
            }

//        check user account is register in database
            $user = User::where('email', $request->email)->first();

//        if user doesn't exist
            if (!$user) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found',
                    'errors' => []
                ], ResponseAlias::HTTP_FORBIDDEN);
            }

//        if user password wrong
            if (!password_verify($request->password, $user->password)) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Wrong password',
                    'errors' => []
                ], ResponseAlias::HTTP_FORBIDDEN);
            }

//        authenticated user
            return response()->json([
                'status' => 'success',
                'message' => 'Login successfully',
                'token' => $user->createToken(uuid_create(), ['*'], now()->addWeek(3))->plainTextToken,
            ], ResponseAlias::HTTP_OK);
        } catch (\Exception $exception) {
//            response if the process is failure
            return response()->json([
                'status' => 'failed',
                'message' => $exception->getMessage(),
                'errors' => $exception->getTrace()
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

//    logout process
    public function logout(Request $request)
    {
        try {
//            deleting current token
            $request->user()->currentAccessToken()->delete();

//            response if user success logout
            return response()->json([
                'status' => 'success',
                'message' => 'Logout successfully',
            ], ResponseAlias::HTTP_OK);
        } catch (\Exception $exception) {
//            response if the process is failure
            return response()->json([
                'status' => 'failed',
                'message' => $exception->getMessage(),
                'errors' => $exception->getTrace()
            ], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
