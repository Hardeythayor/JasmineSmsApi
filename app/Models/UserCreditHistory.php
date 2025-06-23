<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class UserCreditHistory extends Model
{
    protected $guarded = [];

    // protected $casts  = [
    //     'created_at' => 'datetime:F j, Y H:i'
    // ];

    public function getCreatedAtAttribute($value)
    {
        if($value) {
            return Carbon::parse($value)->setTimezone('Asia/Seoul')->format('F j, Y H:i');
        }
        return null;
    }

    public static function boot()
	{
		parent::boot();

		static::created(function ($credit_history) {
            $credit = UserCredit::where('user_id', $credit_history->user_id)->lockForUpdate()->first();

			if($credit_history->type == 'charge' ) {
                $credit->credit_balance += $credit_history->amount;
            } else {
                $credit->credit_balance -= abs($credit_history->amount);
            }
            $credit->save();
		});

	}
}
