<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $date = now()->format('Y-m-d');
        rename($logPath, storage_path("logs/laravel-{$date}.log"));
    }
})->daily();

Schedule::call(function () {
    $files = glob(storage_path('logs/laravel-*.log'));
    if (count($files) > 7) { // Maximal 7 Log-Dateien behalten
        array_map('unlink', array_slice($files, 0, count($files) - 7));
    }
})->daily();
