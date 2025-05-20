<?php
namespace App\Services;

use App\Models\SmsGateway;
use Illuminate\Support\Facades\Log;
use Vonage\Client\Credentials\Basic;
use Vonage\SMS\Message\SMS;

class VonageSmsGateway
{
    public function sendSMS($sms_messages)
    {
        $sms_gateway = SmsGateway::where('name', 'vonage')->first();

        $api_key = $sms_gateway->credentials['api_key'];
        $api_secret = $sms_gateway->credentials['api_secret'];

        $recipients = $sms_messages->recipients;
        // Log::info($recipients);

        $credentials = new Basic($api_key, $api_secret);
        $client = new \Vonage\Client($credentials);

        $readableResponse = [];

        foreach ($recipients as $recipient) {
            $message = new SMS(
                $recipient,
                'Vonage',
                $sms_messages->content,
                'unicode'
            );
    
            $response = $client->sms()->send($message);
    
            Log::info(['sms_response' => $response]);

            

            foreach ($response as $message) {
                $readableResponse[$recipient] = [
                    // 'to' => $message->getTo(),
                    // 'id' => $message->getMessageId(),
                    'status' => $message->getStatus(),
                    // 'network' => $message->getNetwork(),
                    // 'cost' => $message->getMessagePrice(),
                    // 'client_ref' => $message->getClientRef(),
                    // Add other properties you might need
                ];
            }

        }
        Log::info(['sms_response_array' => $readableResponse]);
    }
}