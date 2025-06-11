<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SmsGateway;
use Exception;
use Illuminate\Http\Request;

class ManageSmsGatewayController extends Controller
{
    public function fetchSmsGateway()
    {
        try {
            $sms_gateway = SmsGateway::all();

            return response()->json([
                'status' => 'success',
                'data' => $sms_gateway
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function activateSmsGateway($id)
    {
        try {
            
            $sms_gateway = SmsGateway::where('id', $id)->first();
            
            if(!$sms_gateway) {
                throw new Exception('SMS gateway not found!');
            }
            
            SmsGateway::select('*')->update(['status' => 'inactive']);

            $sms_gateway->status = 'active';
            $sms_gateway->save();

            return response()->json([
                'status' => 'success',
                'message' => 'SMS gateway status updated successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function updateSmsGatewayCharge(Request $request, $id)
    {
        try {
            $request->validate([
                'charge' => 'required|numeric'
            ]);

            $sms_gateway = SmsGateway::where('id', $id)->first();
            
            if(!$sms_gateway) {
                throw new Exception('SMS gateway not found!');
            }

            $sms_gateway->sms_charge = $request->charge;
            $sms_gateway->save();

            return response()->json([
                'status' => 'success',
                'message' => 'SMS gateway charge updated successfully'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
