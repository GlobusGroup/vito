<?php

use App\Models\Secret;
use Illuminate\Support\Facades\Artisan;

// Delete expired secrets
Artisan::command('delete-expired-secrets', function () {
    Secret::where('created_at', '<', now()->subMinutes((int) config('app.secrets_lifetime')))
    ->delete();
})->hourly();
