<?php

namespace App\Services;

use App\Models\SmsGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EimsSmsGateway
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        
    }

     public function sendSMS($sms_messages)
    {
        $sms_gateway = SmsGateway::where('name', 'eims')->first();

        $url = $sms_gateway->credentials['url']."/sendsms";
        $account = $sms_gateway->credentials['account'];
        $password = $sms_gateway->credentials['password'];


        $recipients = $sms_messages->recipients;
        Log::info([$recipients, $url, $account, $password]);

        $response = Http::withHeaders([
            "access-control-allow-credentials" => "false",
            "access-control-allow-headers" => "status,content-type,server,x-powered-by,access-control-allow-origin,date",
            "access-control-allow-methods" => "GET, HEAD, POST, PUT, DELETE, CONNECT, OPTIONS, TRACE, PATCH",
            "access-control-allow-origin" => "*",
            "content-type" => 'application/json;charset=utf-8'
        ])
            ->post($url, [
                "account" => $account,
                "password" => $password,
                "content" => urlencode($sms_messages->content),
                "smstype" => 0,
                "numbers" => implode(', ', $recipients)
            ]);
        
        $api_response = $response->json();

        Log::info(['sms_response' => $api_response]);
    }
}
