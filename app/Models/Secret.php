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
    ];

    protected $casts = [
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
}
