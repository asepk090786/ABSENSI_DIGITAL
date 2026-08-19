<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupervisionInstrument extends Model
{
    use HasFactory;

    protected $table = 'supervision_instruments';

    protected $fillable = [
        'nama',
        'deskripsi',
        'kategori',
        'tipe',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function indicators()
    {
        return $this->hasMany(SupervisionIndicator::class, 'instrument_id');
    }
}
