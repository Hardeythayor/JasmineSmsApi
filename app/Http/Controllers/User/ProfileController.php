<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = User::where('id', $request->user()->id)->first();

            $user->update([
                'password' => bcrypt($request->password),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Password changed successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
