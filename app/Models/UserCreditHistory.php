<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCreditHistory extends Model
{
    protected $guarded = [];

    protected $casts  = [
        'created_at' => 'datetime:F j, Y H:i'
    ];
}
