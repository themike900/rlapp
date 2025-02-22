<?php

use App\Http\Controllers\Api\ApiCronController;
use App\Http\Controllers\Api\ApiDetailsController;
use App\Http\Controllers\Api\ApiListController;
use App\Http\Controllers\Api\ApiRegController;
use App\Http\Controllers\Api\ApiGuestRegController;
use Illuminate\Support\Facades\Route;

//use Illuminate\Http\Request;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

// Holen der Aktivitätenliste für den aktuellen Webuser
Route::post('list', ApiListController::class)->name('api.list');

// Holen der Detail-Informationen einer Aktivität und der Anmelde-Informationen für das Anmelde-Formular
Route::get('details/{webid}/{actionid}', ApiDetailsController::class)->name('api.details');

// Teilnahme an einer Aktivität anmelden
Route::post('rlreg', ApiRegController::class)->name('api.rlreg');

// Teilnahme an einer Aktivität anmelden
Route::post('gstreg', ApiGuestRegController::class)->name('api.gst_reg');

// Teilnahme an einer Aktivität anmelden
Route::get('cron', ApiCronController::class)->name('api.cron');
