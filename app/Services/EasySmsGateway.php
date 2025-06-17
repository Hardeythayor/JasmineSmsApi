<?php

namespace App\Services;

use App\Models\SmsGateway;
use App\Models\SmsMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EasySmsGateway
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        
    }

    public function sendSMS($sms_messages)
    {
        $sms_gateway = SmsGateway::where('name', 'easy_sms')->first();

        $url = $sms_gateway->credentials['url'];
        $account = $sms_gateway->credentials['account'];
        $password = $sms_gateway->credentials['password'];
        $api_key = $sms_gateway->credentials['api_key'];


        $recipients = $sms_messages->recipients;

        $post_data = [
            "from" => $sms_messages->source,
            "to" => implode(',', $recipients),
            "text" => $sms_messages->content,
            "type" => "1"
        ];

        if($sms_messages->send_type == 'reserved')
        {
            $post_data['scheduled'] = $sms_messages->reservation_date->format('Y-m-d\TH:i:s');
        }
        // Log::info(['date' => $sms_messages->reservation_date->format('Y-m-d\TH:i:s'), 'postData' => $post_data, 'url' => $url, 'apikey' => $api_key]);

        $response = Http::withHeaders([
            "content-type" => 'application/json',
            "accept" => "application/json",
            "apikey" => $api_key
        ])
        ->post($url, $post_data);
        
        $api_response = $response->json();
        Log::info(['response' => $api_response]);

        SmsMessage::where('id', $sms_messages->id)->update([
            'raw_response' => $api_response,
            'status' => (isset($api_response['status']) && $api_response['status'] == 'OK') ? 'success' : ((isset($api_response['error']) &&  $api_response['error'] == 4012) ? 'failure' : 'pending') 
        ]);
    }

    public function sendChunkedSMS($sms_messages, $recipients)
    {
        $sms_gateway = SmsGateway::where('name', 'easy_sms')->first();

        $url = $sms_gateway->credentials['url'];
        $account = $sms_gateway->credentials['account'];
        $password = $sms_gateway->credentials['password'];
        $api_key = $sms_gateway->credentials['api_key'];

        $post_data = [
            "from" => $sms_messages->source,
            "to" => implode(',', $recipients),
            "text" => $sms_messages->content,
            "type" => "1"
        ];

        if($sms_messages->send_type == 'reserved')
        {
            $post_data['scheduled'] = $sms_messages->reservation_date->format('Y-m-d\TH:i:s');
        }
        // Log::info(['date' => $sms_messages->reservation_date->format('Y-m-d\TH:i:s'), 'postData' => $post_data, 'url' => $url, 'apikey' => $api_key]);

        $response = Http::withHeaders([
            "content-type" => 'application/json',
            "accept" => "application/json",
            "apikey" => $api_key
        ])
        ->post($url, $post_data);
        
        $api_response = $response->json();
        Log::info(['response' => $api_response]);

        SmsMessage::where('id', $sms_messages->id)->update([
            'raw_response' => $api_response,
            'status' => (isset($api_response['status']) && $api_response['status'] == 'OK') ? 'success' : ((isset($api_response['error']) &&  $api_response['error'] == 4012) ? 'failure' : 'pending') 
        ]);
    }
}
