<?php

use App\Http\Controllers\ActionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;

// Route::get('/welcome', function () {
//    return view('welcome');
// });

//Route::get('/', function () {
//    return view('start');
//});

Route::redirect('/', '/actions/index');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::match(['get', 'post'], '/actions/index', [ActionController::class, 'index'])->name('actions.index');
    Route::match(['get', 'post'], '/actions/create', [ActionController::class, 'create'])->name('actions.create');
    Route::match(['get', 'post'], '/actions/store', [ActionController::class, 'store'])->name('actions.store');
    Route::match(['get', 'post'], '/actions/show', [ActionController::class, 'show'])->name('actions.show');
    Route::match(['get', 'post'], '/actions/edit', [ActionController::class, 'edit'])->name('actions.edit');
    Route::match(['get', 'post'], '/actions/update', [ActionController::class, 'update'])->name('actions.update');
});

Route::get('/members/import', function () {
        return view('members.members_import');
    })->middleware(['auth', 'verified'])->name('members.import');
Route::post('/import', [ImportController::class, 'importMembers'])->name('import');


//Route::resource('actions', ActionController::class)
//    ->only(['index', 'create', 'store', 'show', 'edit', 'update'])
//    ->middleware(['auth', 'verified']);

require __DIR__.'/auth.php';
require __DIR__.'/api.php';
