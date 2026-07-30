<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('backup:database --type=daily')->daily();
Schedule::command('backup:database --type=weekly')->weekly();
Schedule::command('backup:database --type=monthly')->monthly();
