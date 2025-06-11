<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InvitationCode;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InviteCodeController extends Controller
{
    public function fetchInviteCodes()
    {
        try {
            $invite_codes = InvitationCode::paginate('50');

            return response()->json([
                'status' => 'success',
                'data' => $invite_codes
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function addInviteCode(Request $request)
    {
        $request->validate(['inviteCode' => 'required|string']);

        try {
            $invitation_code = InvitationCode::create([
                'invite_code' => $request->inviteCode,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Invitation Code added successfully',
                'data' => $invitation_code
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function editInviteCode(Request $request, $id)
    {
        $request->validate([
            'inviteCode' => 'required|string',
            'status' => [ // Add this rule
                'required',
                Rule::in(['active', 'inactive']),
            ],
        ]);

        try {
            $invitation_code = InvitationCode::where('id', $id)->first();

            if(!$invitation_code) {
                throw new Exception('Invitation Code not found!');
            }

            $invitation_code->update([
                'invite_code' => $request->inviteCode,
                'status' => $request->status
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Invitation Code updated successfully',
                'data' => $invitation_code
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
