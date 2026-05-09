<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('service-orders:auto-cancel-old')->daily();
Schedule::command('invoices:mark-overdue')->daily();
Schedule::command('machine-attendance:auto-close')->dailyAt('23:30');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
