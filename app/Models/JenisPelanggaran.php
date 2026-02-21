<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisPelanggaran extends Model
{
    use HasFactory;

    protected $table = 'jenis_pelanggaran';

    protected $fillable = [
        'kode',
        'nama',
        'poin_default',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
