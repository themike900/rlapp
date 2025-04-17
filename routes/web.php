<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\RlActionsController;
use App\Http\Controllers\RlMembersController;
use App\Http\Controllers\ProfileController;
use \App\Http\Controllers\FartenblattPdf;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Livewire\Pages\RlActionsList;

// Route::get('/welcome', function () {
//    return view('welcome');
// });

// Route::get('/test', function () { return view('layouts.testapp'); });

Route::redirect('/', '/rl/pages');

//Route::get('/dashboard', function () {
//    return view('dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
//    Route::match(['get', 'post'], '/actions/index', [ActionController::class, 'index'])->name('actions.index');
//    Route::match(['get', 'post'], '/actions/create', [ActionController::class, 'create'])->name('actions.create');
//    Route::match(['get', 'post'], '/actions/store', [ActionController::class, 'store'])->name('actions.store');
//    Route::match(['get', 'post'], '/actions/show', [ActionController::class, 'show'])->name('actions.show');
//    Route::match(['get', 'post'], '/actions/edit', [ActionController::class, 'edit'])->name('actions.edit');
//    Route::match(['get', 'post'], '/actions/update', [ActionController::class, 'update'])->name('actions.update');

    //Route::match(['get', 'post'], '/rl/actions-list', [RlActionsController::class, 'RlActionList'])->name('rl-action-list');
    Route::get('/rl/pages', RlActionsList::class)->name('rl-pages');
});

Route::get('/members/import', function () {
        return view('members.members_import');
    })->middleware(['auth', 'verified'])
    ->name('members.import');

Route::post('/import', [ImportController::class, 'importMembers'])->name('import');

Route::get('/rl/fahrtenblatt/{actionId}', [FartenblattPdf::class, 'generatePdf'])->name('rl-fahrtenblatt');


require __DIR__.'/auth.php';
require __DIR__.'/api.php';
