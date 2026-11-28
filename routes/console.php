<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Newsletter : lot suivant des campagnes en cours (reprend aussi un envoi interrompu).
\Illuminate\Support\Facades\Schedule::command('geniuspress:newsletter-send')->everyMinute()->withoutOverlapping(20);

// Sauvegarde automatique (réglage du site : chaque jour, chaque semaine ou jamais) ; la commande décide si elle est due.
\Illuminate\Support\Facades\Schedule::command('geniuspress:backup --auto')->hourly()->withoutOverlapping(120);
