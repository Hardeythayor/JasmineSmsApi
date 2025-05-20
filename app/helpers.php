<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

function sendSMS($message) 
{
    
// $recipients = json_decode($message->recipients);
    $response = Http::withHeaders([
            "access-control-allow-credentials" => "false",
            "access-control-allow-headers" => "status,content-type,server,x-powered-by,access-control-allow-origin,date",
            "access-control-allow-methods" => "GET, HEAD, POST, PUT, DELETE, CONNECT, OPTIONS, TRACE, PATCH",
            "access-control-allow-origin" => "*",
        ])
            ->post(config('bulksms.url'), [
                'from' => 'JasmineSMS',
                'api_token' => env('BULK_SMS_API_KEY', 'FHDEnUGTrAGjN6wz1dAIMHPYPHAtykypDpGYGNWi6aDNsPFAGM6hiERHnwaj'),
                'to' => implode(', ', $message->recipients),
                'body' => $message->content
            ]);
        $api_response = $response->json();
        Log::info($api_response);

        if ($api_response && $api_response['data']['status'] == 'success') {
            Log::info($api_response);
        }
}