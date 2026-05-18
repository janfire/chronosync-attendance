<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Weekly summary every Sunday at 23:55
Schedule::command('attendance:summarize --period=weekly')
    ->weekly()->sundays()->at('23:55');

// Monthly summary on last day of month at 23:55
Schedule::command('attendance:summarize --period=monthly')
    ->lastDayOfMonth('23:55');
