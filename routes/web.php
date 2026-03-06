<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\Pdfs\FartenlistePdf;
use App\Http\Controllers\RlActionsController;
use App\Http\Controllers\RlMembersController;
use App\Http\Controllers\ProfileController;
use \App\Http\Controllers\FartenblattPdf;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Livewire\Pages\RlActionsList;


Route::redirect('/', '/rl/pages');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/rl/pages', RlActionsList::class)->name('rl-pages');
});

Route::get('/members/import', function () {
        return view('members.members_import');
    })->middleware(['auth', 'verified'])
    ->name('members.import');

Route::post('/import', [ImportController::class, 'importMembers'])->name('import');

Route::get('/rl/fahrtenblatt/{actionId}', [FartenblattPdf::class, 'generatePdf'])->name('rl-fahrtenblatt');
Route::get('/rl/fahrtenliste/{webId}', [FartenlistePdf::class, 'generatePdf'])->name('rl-fahrtenliste');

Route::fallback(function () {
    return redirect('/login');
});

// Cron-Endpoints (gesichert mit Secret-Token)
Route::get('/cron/schedule/{token}', function (string $token) {
    if ($token !== config('app.cron_secret')) abort(403);
    Artisan::call('schedule:run');
    return 'OK';
});

Route::get('/cron/queue/{token}', function (string $token) {
    if ($token !== config('app.cron_secret')) abort(403);
    Artisan::call('queue:work', ['--stop-when-empty' => true]);
    return 'OK';
});

require __DIR__.'/auth.php';
# require __DIR__.'/api.php';
