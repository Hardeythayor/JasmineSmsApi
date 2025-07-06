<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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

/**
     * Formats a given string based on specific prefixes.
     *
     * If the string starts with '006', it removes the '006' prefix.
     * If the string starts with '+', it removes the '+' prefix.
     * Otherwise, the original string is returned.
     *
     * @param string $inputString The string to format.
     * @return string The formatted string.
*/
function formatPhoneNumber(string $inputString): string
{
    // Check if the string starts with '006'
    if (Str::startsWith($inputString, '006')) {
        // Remove '006' from the beginning and return the rest
        return Str::after($inputString, '006');
    }

    // Check if the string starts with '+'
    if (Str::startsWith($inputString, '+')) {
        // Remove '+' from the beginning and return the rest
        // Using substr(1) is a simple way to remove the first character
        return substr($inputString, 1);
    }

    // If neither prefix is found, return the original string
    return $inputString;
}