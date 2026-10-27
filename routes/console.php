<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Rutina de respaldo automático de base de datos con rotación (protocolo de contingencia)
Schedule::command('app:backup-database --keep=10')
    ->weekly()
    ->sundays()
    ->at('03:00')
    ->description('Respaldo periódico y rotación de base de datos para contingencia institucional');
