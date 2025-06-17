<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserCredit;
use App\Models\UserCreditHistory;
use Exception;
use Illuminate\Http\Request;

class UserCreditController extends Controller
{
    public function fetchUserCredit(Request $request, $user_id= null)
    {
        if(!$user_id) {
            $user_id = $request->user()->id;
        } 

        try {
            $user = User::find($user_id);

            if(!$user) {
                throw new Exception('User not found');
            }

            $user_credit = UserCredit::where('user_id', $user_id)->first();

            return response()->json([
                'status' => 'success',
                'message' => 'User credit fetched successfully',
                'data' => $user_credit
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function fetchUserCreditHistory(Request $request, $user_id= null)
    {
        if(!$user_id) {
            $user_id = $request->user()->id;
        } 

        try {
            $user = User::find($user_id);

            if(!$user) {
                throw new Exception('User not found');
            }

            $user_credit_history = UserCreditHistory::where('user_id', $user_id);

            if($request->has('type') && !is_null($request->type))
            {
                $user_credit_history->where('type', $request->type);
            }

            if($request->has('paginated') && $request->paginated == true) {
                $user_credit_history = $user_credit_history->orderBy('created_at', 'DESC')->paginate('30');
            } else {
                $user_credit_history = $user_credit_history->orderBy('created_at', 'DESC')->get();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User credit history fetched successfully',
                'data' => $user_credit_history
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
