<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddThirdPartyNumberRequest;
use App\Models\ThirdPartyNumber;
use Exception;
use Illuminate\Http\Request;

class ThirdPartyController extends Controller
{
    public function fetchThirdPartyNumbers()
    {
        try {
            $third_party_numbers = ThirdPartyNumber::get();

            return response()->json([
                'status' => 'success',
                'data' => $third_party_numbers
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function addThirdPartyNumber(AddThirdPartyNumberRequest $request)
    {
        try {
            $third_party_number = ThirdPartyNumber::create([
                'label' => $request->label,
                'phone' => $request->phoneNumber,
                'status' => $request->status
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Third party number added successfully',
                'data' => $third_party_number
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function editThirdPartyNumber(AddThirdPartyNumberRequest $request, $id)
    {
        try {
            $third_party_number = ThirdPartyNumber::where('id', $id)->first();

            if(!$third_party_number) {
                throw new Exception('Third Party Number not found!');
            }

            $third_party_number->update([
                'label' => $request->label,
                'phone' => $request->phoneNumber,
                'status' => $request->status
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Third party number updated successfully',
                'data' => $third_party_number
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
