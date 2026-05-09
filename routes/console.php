<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Schedule::command('logs:prune-activity')->daily();
Schedule::command('data:prune-tri-yearly-records')->daily();
Schedule::command('data:prune-old-reviews')->daily();
Schedule::command('data:prune-old-posts')->daily();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
