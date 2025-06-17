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

function generateUniqueRandomNumber($no_of_digit)
{
	// Generate an array of digits 0-9
	$digits = range(0, 9);

	// Shuffle the array
	shuffle($digits);

	// Take the first $no_of_digits
	$uniqueDigits = array_slice($digits, 0, $no_of_digit);

	// Convert the array of digits to a string
	$randomNumber = implode('', $uniqueDigits);

	return $randomNumber;
}

function transformPhoneNumbers($numbers)
{
    $transformedNumbers = collect($numbers)->map(function ($number) {
        if (str_starts_with($number, '10')) {
            return '82' . $number;
        } elseif (str_starts_with($number, '010')) {
            // Remove the leading '0' and then prefix with '82'
            return '82' . substr($number, 1);
        }
        return $number; // Return the number as is if it doesn't match the conditions
    })->toArray();

    return $transformedNumbers;
}

function transformSinglePhoneNumber($num)
{
    if (str_starts_with($num, '10')) {
        return '82' . $num;
    } elseif (str_starts_with($num, '010')) {
        // Remove the leading '0' and then prefix with '82'
        return '82' . substr($num, 1);
    } else {
        return $num;
    };
}