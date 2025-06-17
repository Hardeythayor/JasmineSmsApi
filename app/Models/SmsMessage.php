<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SmsMessage extends Model
{
    protected $guarded = [];
    
    protected $casts = [
        'recipients' => 'array',
        'raw_response' => 'array',
        'reservation_date' => 'datetime', // Or 'timestamp'
    ];

    public function sender()
    {
        return $this->belongsTo(User::class);
    }


    public function messageRecipients()
    {
        return $this->hasMany(MessageRecipient::class, 'message_id', 'id');
    }


    public function getCreatedAtAttribute($value)
    {
        if($value) {
            return Carbon::parse($value)->format('Y-m-d h:i');
        }
        return null;
    }

    /**
     * Boot the Model.
     */
    public static function boot()
    {
        parent::boot();


        static::created(function ($created_sms) {
            foreach ($created_sms->recipients as $recipient) {
                MessageRecipient::updateOrCreate([
                    'message_id' => $created_sms->id,
                    'phone_number' => $recipient,
                    'transformed_phone' => transformSinglePhoneNumber($recipient)
                ]);
            }
        });
    }
}
