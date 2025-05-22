<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'recipients' => 'array',
        'raw_response' => 'array',
        'reservation_date' => 'datetime', // Or 'timestamp'
    ];
}
