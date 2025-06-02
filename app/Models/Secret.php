<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Secret extends Model
{
    use HasFactory;

    protected $fillable = [
        'encrypted_content',
        'requires_password',
        'expires_at',
    ];

    protected $casts = [
        'requires_password' => 'boolean',
        'expires_at' => 'datetime',
    ];

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
            
            // Set default expiry time if not provided
            if (!$model->expires_at) {
                $model->expires_at = now()->addMinutes((int) config('app.secrets_lifetime'));
            }
        });
    }

    public function isExpired()
    {
        return $this->expires_at < now();
    }
}
