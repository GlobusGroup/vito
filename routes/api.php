<?php

use App\Http\Controllers\Api\SecretApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/secrets', [SecretApiController::class, 'store'])->middleware('throttle:10,1');
});