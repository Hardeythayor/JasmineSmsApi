<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Laravel\Sanctum\Console\Commands\PruneExpired;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// If you want it to run hourly and prune tokens expired for at least 1 hour:
Schedule::command(PruneExpired::class, ['--hours' => 1])->hourly();
