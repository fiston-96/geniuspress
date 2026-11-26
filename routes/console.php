<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Newsletter : lot suivant des campagnes en cours (reprend aussi un envoi interrompu).
\Illuminate\Support\Facades\Schedule::command('geniuspress:newsletter-send')->everyMinute()->withoutOverlapping(20)->runInBackground();
