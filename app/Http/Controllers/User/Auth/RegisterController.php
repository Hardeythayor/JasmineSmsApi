<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterRequest;
use App\Models\User;
use App\Models\UserCredit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->userid = $request->userId;
            $user->invite_code = $request->inviteCode;
            $user->password = Hash::make($request->password);
            $user->save();

            $credit = new UserCredit();
            $credit->user_id = $user->id;
            $credit->credit_balance = 0;
            $credit->save();

            $token = $user->createToken($request->ip())->plainTextToken;

            DB::commit();

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
