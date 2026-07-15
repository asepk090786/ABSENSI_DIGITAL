<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class JamBelajarPola extends Model
{
    use HasFactory;

    protected $table = 'jam_belajar_pola';
    
    protected $fillable = [
        'nama_pola',
        'deskripsi',
        'jam_data',
        'is_active'
    ];

    protected $casts = [
        'jam_data' => 'array',
        'is_active' => 'boolean'
    ];

    /**
     * Scope untuk ambil pola aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get jam data as collection
     */
    public function getJamCollection()
    {
        return collect($this->jam_data);
    }
}
