<?php

return [
    'url' => env('BULK_SMS_API_URL', 'https://www.bulksmsnigeria.com/api/v2/sms'),
    'vonage' => [
        'key' => env('VONAGE_API_KEY'),
        'secret' => env('VONAGE_API_SECRET')
    ],
    'easy_sms' => [
        'timeout' => 10000,
        'debug' => env('APP_DEBUG', false),
    ]
];