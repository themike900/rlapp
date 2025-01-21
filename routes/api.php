<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

// Holen der Aktivitätenliste für den aktuellen Webuser
Route::post('list', [ApiController::class, 'list'])->name('api.list');

// Holen der Detail-Informationen einer Aktivität und der Anmelde-Informationen für das Anmelde-Formular
Route::get('details/{webid}/{actionid}', [ApiController::class, 'details'])->name('api.details');

// Teilnahme an einer Aktivität anmelden
Route::post('rlreg', [ApiController::class, 'rlReg'])->name('api.rlreg');
