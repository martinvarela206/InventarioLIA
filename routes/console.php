<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rutina de respaldo automático de base de datos con rotación y adjuntos (protocolo de contingencia)
Schedule::command('app:backup-database --keep=14 --with-attachments')
    ->dailyAt('03:00')
    ->description('Respaldo diario y rotación de base de datos y adjuntos multimedia para contingencia institucional');
