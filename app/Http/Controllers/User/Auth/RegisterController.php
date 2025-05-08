<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->userid = $request->userId;
            $user->password = Hash::make($request->password);
            $user->save();

            $token = $user->createToken($request->ip())->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Registration Successful',
                'user' => $user,
                'token' => $token
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
