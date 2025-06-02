<?php

use App\Models\Secret;
use Illuminate\Support\Facades\Artisan;

// Delete expired secrets
Artisan::command('delete-expired-secrets', function () {
    $deletedCount = Secret::where('expires_at', '<', now())->delete();
    $this->info("Deleted {$deletedCount} expired secrets.");
})->everyMinute();
