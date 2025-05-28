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
        'valid_until',
        'requires_password',
    ];

    protected $casts = [
        'valid_until' => 'datetime',
        'requires_password' => 'boolean',
    ];

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function isValid(): bool
    {
        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        return true;
    }
}
