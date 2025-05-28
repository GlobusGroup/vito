<?php

use App\Http\Controllers\Api\SecretController;
use Illuminate\Support\Facades\Route;

Route::post('/secrets/{secret}', [SecretController::class, 'decrypt'])->name('secrets.decrypt')->middleware('throttle:20,1');
