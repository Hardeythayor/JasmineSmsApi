<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ToggleStatusRequest;
use App\Models\SmsGateway;
use App\Models\User;
use App\Models\UserCreditHistory;
use Exception;
use Illuminate\Http\Request;

class ManageUserController extends Controller
{
    public function fetchUsers(Request $request)
    {
        try {
            $sms_gateway = SmsGateway::where('status', 'active')->first();

            $users = User::with('smsCredit:user_id,credit_balance')->where('user_type', 'user');

            if($request->has('search') && !is_null($request->search)) {
                $users->whereAny(['name', 'email', 'userid', 'invite_code'], 'LIKE', "%{$request->search}%");
            }
            $users = $users->paginate(50);

            if($sms_gateway) {
                foreach ($users as $key => $user) {
                    $user->remaining_sms = $user->smsCredit ? floor($user->smsCredit->credit_balance/$sms_gateway->sms_charge) : NULL;
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $users
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function fetchUserProfile($id)
    {
        try {
            $user = User::withCount('messages')
                        ->with('smsCredit:user_id,credit_balance')
                        ->where('id', $id)->first();
            
            if(!$user) {
                throw new Exception('User not found!');
            }

            return response()->json([
                'status' => 'success',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function toggleUserStatus(ToggleStatusRequest $request, $id)
    {
        try {
            $user = User::where('id', $id)->first();
            
            if(!$user) {
                throw new Exception('User not found!');
            }

            $user->status = $request->status;
            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'User status updated successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function addUserSmsCredit(Request $request, $id)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric'
            ]);

            $user = User::with('smsCredit')->where('id', $id)->first();
            
            if(!$user) {
                throw new Exception('User not found!');
            }

            if (is_null($user->smsCredit)) {
                $user->smsCredit()->create(['credit_balance' => 0]);
            }

            UserCreditHistory::create([
                'user_id' => $id,
                'type' => 'charge',
                'amount' => abs($request->amount),
                'purpose' => 'Admin Charge'
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'User credit added successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
