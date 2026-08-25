<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Settle STK payments whose callback never arrived. Runs with nobody watching,
 * which is the whole point: the browser-driven fallback stops the moment the
 * applicant closes the tab.
 *
 * withoutOverlapping so a slow Daraja cannot stack runs on top of each other,
 * and runInBackground so a stalled query does not hold up the scheduler.
 */
Schedule::command('mpesa:settle-pending-stk')
    ->everyMinute()
    ->withoutOverlapping(5)
    ->runInBackground();
