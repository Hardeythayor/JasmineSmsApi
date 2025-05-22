<?php

namespace App\Http\Controllers\User;

use App\Events\SendReservedMessageEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\SendSmsRequest;
use App\Models\SmsMessage;
use App\Services\EasySmsGateway;
use App\Services\EimsSmsGateway;
use App\Services\VonageSmsGateway;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SMSController extends Controller
{
    public function sendMessage(SendSmsRequest $request)
    {
        try {
            //save message
            $created_sms = SmsMessage::create([
                'user_id' => $request->user()->id,
                'send_type' => $request->sendMode,
                'reservation_date' => $request->sendDate,
                'split_sending' => $request->splitSend,
                'split_number' => $request->splitNumber,
                'recipients' => $request->recipients,
                'recipient_count' => $request->recipientCount,
                'content' => $request->content,
                'scheduled' => 'no'
            ]);

            $eims = new EasySmsGateway;
            $eims->sendSMS($created_sms);

            return response()->json([
                'status' => 'success',
                'message' => 'Message sent successfully',
                'data' => $created_sms
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }   

        // $response = Http::withHeaders([
        //     "access-control-allow-credentials" => "false",
        //     "access-control-allow-headers" => "status,content-type,server,x-powered-by,access-control-allow-origin,date",
        //     "access-control-allow-methods" => "GET, HEAD, POST, PUT, DELETE, CONNECT, OPTIONS, TRACE, PATCH",
        //     "access-control-allow-origin" => "*",
        // ])
        //     ->post(env('BULK_SMS_API_URL', 'https://www.bulksmsnigeria.com/api/v1/sms/create'), [
        //         'from' => $new_sender_id,
        //         'api_token' => env('BULK_SMS_API_KEY', 'FHDEnUGTrAGjN6wz1dAIMHPYPHAtykypDpGYGNWi6aDNsPFAGM6hiERHnwaj'),
        //         'to' => implode(', ', $request->receivers),
        //         'body' => $request->message
        //     ]);
        // $api_response = json_decode($response);
    }

    public function webhookResponse(Request $request)
    {
        $data = $request->input();

        Log::info(['webhook_data' => $data]);

        return response()->json(['message' => 'success'], 200);
    }

    public function runArtisanCommand()
    {
        $t = Artisan::call('migrate:fresh --seed');
        
        Artisan::call('optimize');
        return Artisan::output();
    }
}
