<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class ApiKey extends Model
{
    protected $fillable = [
        'name',
        'key_prefix',
        'key_hash',
        'encrypted_key',
        'generated_by',
        'last_used_at',
        'revoked_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function getPlainKeyAttribute(): ?string
    {
        if (! $this->encrypted_key) {
            return null;
        }

        try {
            return Crypt::decryptString($this->encrypted_key);
        } catch (\Throwable) {
            return null;
        }
    }
}
