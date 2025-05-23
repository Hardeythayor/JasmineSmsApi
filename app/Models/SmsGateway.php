<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsGateway extends Model
{
    protected $guarded = [];
    protected $casts = [
        'credentials' => 'array'
    ];
}
