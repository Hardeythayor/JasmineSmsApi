<?php

use App\Models\SmsMessage;
use App\Services\EimsSmsGateway;
use App\Services\VonageSmsGateway;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    // ->withSchedule(function (Schedule $schedule) {
    //     $schedule->call(function () {
    //         $reserved_messages = DB::table('sms_messages')->where([
    //             'send_type' => 'reserved',
    //             'scheduled' => 'no'
    //         ])->get();

    //         $current_date = now('Africa/Lagos')->format('Y-m-d H:i');

    //         foreach ($reserved_messages as $message) {
    //             // $reservationDate = $message->reservation_date;
    //             $message->recipients = json_decode($message->recipients);
    //             $reservationDate = Carbon::parse($message->reservation_date);
    //             $reservationDate = $reservationDate->format('Y-m-d H:i');
    //             // Log::info(['recipientType' => gettype($message->recipients), 'recipients' => $message->recipients]);
    //             // Log::info(['now' => $current_date, 'res' => $reservationDate]);

    //             if ($current_date >= $reservationDate) {
    //                 // $vonage = new VonageSmsGateway;
    //                 // $vonage->sendSMS($message);

    //                 $eims = new EimsSmsGateway;
    //                 $eims->sendSMS($message);

    //                 // Update DB or dispatch message here
    //                 DB::table('sms_messages')->where('id', $message->id)->update(['scheduled' => 'yes']);
    //             }
    //         }
    //     })->everyMinute();
    // })
    ->create();
