<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('presufactura:process-reminders')->hourly();
Schedule::command('presufactura:verifactu-retry-failed')->everyFifteenMinutes();
Schedule::command('presufactura:process-fiscal-reminders')->dailyAt('08:15');
