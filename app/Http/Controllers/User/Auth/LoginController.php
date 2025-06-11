<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(LoginRequest $request)
    {
        $user = User::where('userid', $request->userId)->first();

        if (!Auth::attempt(['userid' => $request->userId, 'password' => $request->password], $request->get('remember'))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid login details'
            ], 401);
        }

        if($user->status == 'inactive') {
            return response()->json([
                'status' => 'error',
                'message' => 'Account not Active. Contact Admin'
            ], 401);
        }

        $token = $user->createToken($request->ip())->plainTextToken;

        $user->token = $token;

        return response()->json([
            'status' => 'success',
            'message' => 'Login Successful',
            'user' => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Logout Successful'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
