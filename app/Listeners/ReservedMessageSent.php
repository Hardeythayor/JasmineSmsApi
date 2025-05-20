<?php

namespace App\Listeners;

use App\Events\SendReservedMessageEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

class ReservedMessageSent implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SendReservedMessageEvent $event): void
    {
        try {
            $model = $event->message;
            $reservationDate = \Carbon\Carbon::parse($model->reservation_date);
    
            Schedule::call(function () use ($model) {
                // Logic to send the successful creation message
                Log::info("Sending successful creation message for model ID: {$model->id}");
                // You would typically call your 'sendMessage' function here,
                // potentially passing the $model.
            })->at($reservationDate);
            // when->(function () use ($model) {
            //     return now()->toDateTimeString() === $model->reservation_date;
            // });
    
            Log::info("Scheduled successful creation message for model ID: {$model->id} at {$reservationDate}");
        } catch (\Throwable $th) {
            Log::error($th->getMessage());
        }
    }
}
