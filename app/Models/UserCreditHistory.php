<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCreditHistory extends Model
{
    protected $guarded = [];

    protected $casts  = [
        'created_at' => 'datetime:F j, Y H:i'
    ];

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
