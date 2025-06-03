<?php

namespace App\Http\Controllers\User;

use App\Events\SendReservedMessageEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\SendSmsRequest;
use App\Jobs\SendSmsChunk;
use App\Models\MessageRecipient;
use App\Models\SmsGateway;
use App\Models\SmsMessage;
use App\Models\ThirdPartyNumber;
use App\Models\User;
use App\Models\UserCredit;
use App\Models\UserCreditHistory;
use App\Services\EasySmsGateway;
use App\Services\EimsSmsGateway;
use App\Services\VonageSmsGateway;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SMSController extends Controller
{
    public function fetchThirdpartyNumbers()
    {
        try {
            $third_party_numbers = ThirdPartyNumber::where('status', 'active')->pluck('phone')->toArray();

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

    public function fetchSmsCharge()
    {
        try {
            $sms_gateway = SmsGateway::where('status', 'active')->first();

            if(is_null($sms_gateway)){
                throw new Exception("No active gateway found");
            }

            $sms_charge = $sms_gateway->sms_charge;

            return response()->json([
                'status' => 'success',
                'data' => $sms_charge
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function sendMessage(SendSmsRequest $request)
    {
        try {
            $chunkSize = 30; // Maximum number of recipients per chunk

            DB::beginTransaction();
            $credit = UserCredit::where('user_id', $request->user()->id)->first();
            if(is_null($credit) || $credit->credit_balance < $request->smsAmount) {
                throw new Exception('Insufficient credit balance');
            }

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
                'scheduled' => 'no',
                'source' => generateUniqueRandomNumber(11),
                'message_type' => $request->type
            ]);
            
            UserCreditHistory::create([
                'user_id' => $request->user()->id,
                'type' => 'deduction',
                'purpose' => $request->type == 'test' ? '3rd party test sent' : "Send message",
                'amount' => $request->smsAmount,
                'recipient_count' => $request->type == 'test' ? NULL : intVal($request->recipientCount),
            ]);

            $created_sms->recipients = transformPhoneNumbers($created_sms->recipients);

            DB::commit();

            // Chunk the recipients array into smaller arrays
            $chunks = array_chunk($created_sms->recipients, $chunkSize);

            foreach ($chunks as $chunk) {
                // Dispatch a *separate* job for each chunk
                // Each job contains one array of up to 30 phone numbers
                SendSmsChunk::dispatch($created_sms, $chunk);
            }

            // $eims = new EasySmsGateway;
            // $eims->sendSMS($created_sms);

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

    public function fetchSmsReport(Request $request, $user_id = null)
    {
        try {
            $sms_report = SmsMessage::with('sender', 'messageRecipients')->where('message_type', 'normal');

            if(!is_null($user_id)) {
                $user = User::where('id', $user_id)->first();

                if(!$user) {
                    throw new Exception('User not found!');
                }

                $sms_report->where('user_id', $user_id);
            }

            if($request->has('search') && !is_null($request->search)) {
                $sms_report->where('content', 'LIKE', "%{$request->search}%");
            }

            return response()->json([
                'status' => 'success',
                'data' => $sms_report->orderBy('created_at', 'DESC')->paginate('50')
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function fetchSingleSmsReport($id)
    {
        try {
            $success_count = 0;
            $fail_count = 0;

            $sms_report = SmsMessage::with('sender', 'messageRecipients')->where('id', $id)->first();

            foreach ($sms_report->messageRecipients as $key => $value) {
                if($value->status == 'completed') {
                    $success_count++;
                }
                if($value->status == 'failed') {
                    $fail_count++;
                }
            }

            $sms_report->success_count = $success_count;
            $sms_report->fail_count = $fail_count;

            return response()->json([
                'status' => 'success',
                'data' => $sms_report
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function fetchThirdpartyResult($user_id=null)
    {
        try {
            $sms_test_numbers = ThirdPartyNumber::where('status', 'active')->orderBy('id', 'ASC')->get();
            $test_sms_report = SmsMessage::with('sender', 'messageRecipients')->where('message_type', 'test');

            if(!is_null($user_id)) {
                $user = User::where('id', $user_id)->first();

                if(!$user) {
                    throw new Exception('User not found!');
                }

                $test_sms_report->where('user_id', $user_id);
            }

            $data = $test_sms_report->orderBy('created_at', 'DESC')->paginate('50');

            foreach ($data as $key => $test_report) {
                foreach ($sms_test_numbers as $subkey => $num) {
                    $collection = collect($test_report->messageRecipients);
                    $element = $collection->firstWhere('phone_number', $num->phone);
                    if ($element) {
                        $status = $element['status'];
                        $data[$key][$num->label] = $status;
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function webhookResponse(Request $request)
    {
        $data = $request->input();

        Log::info(['webhook_data' => $data]);

        if(count($data) > 0) {
            $sms_message = SmsMessage::where('source' , $data['source'])->first();

            if($sms_message) {
                MessageRecipient::where(['message_id' => $sms_message->id, 'transformed_phone' => $data['msisdn']])->update([
                    'status' => ($data['status'] == 'DELIVRD') ? 'completed' : (($data['status'] == 'UNDELIV') ? 'failed' : 'pending'),
                    'sent_at' => $data['sentdate'],
                    'phone_sms_id' => $data['smsid'],
                    'fail_reason' => $data['status'] == 'EXPIRED' ? 'The carrier has timed out.' : NULL
                ]);
            }
        }

        return response()->json(['message' => 'success'], 200);
    }
}
