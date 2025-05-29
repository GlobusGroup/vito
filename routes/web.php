<?php

use App\Http\Controllers\SecretController;
use Illuminate\Support\Facades\Route;


Route::redirect('/', '/secrets/create');
Route::get('/secrets/create', [SecretController::class, 'create'])->name('secrets.create');
Route::post('/secrets', [SecretController::class, 'store'])->name('secrets.store');
Route::get('/secrets/share', [SecretController::class, 'share'])->name('secrets.share');
Route::get('/secrets/show', [SecretController::class, 'show'])->name('secrets.show');

Route::post('/secrets/{secret}', [SecretController::class, 'decrypt'])->name('secrets.decrypt')->middleware('throttle:20,1');
