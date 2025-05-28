<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Secret extends Model
{
    use HasFactory;

    protected $fillable = [
        'encrypted_content',
        'valid_until',
        'is_used',
    ];

    protected $casts = [
        'valid_until' => 'datetime',
        'is_used' => 'boolean',
    ];

    public function isValid(): bool
    {
        if ($this->is_used) {
            return false;
        }

        if ($this->valid_until && $this->valid_until->isPast()) {
            return false;
        }

        return true;
    }

    public function markAsUsed(): void
    {
        $this->update(['is_used' => true]);
    }
}
