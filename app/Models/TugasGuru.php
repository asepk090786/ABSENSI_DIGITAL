<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TugasGuru extends Model
{
    use HasFactory;

    protected $table = 'tugas_guru';

    protected $fillable = [
        'guru_id',
        'mata_pelajaran_id',
        'tingkat_kelas',
        'kelas_id',
        'is_active',
        'keterangan',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relasi ke model Guru
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    /**
     * Relasi ke model MataPelajaran
     */
    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    /**
     * Relasi ke model Kelas (optional)
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Scope untuk filter berdasarkan guru
     */
    public function scopeByGuru($query, $guruId)
    {
        return $query->where('guru_id', $guruId);
    }

    /**
     * Scope untuk filter berdasarkan mata pelajaran
     */
    public function scopeByMataPelajaran($query, $mataPelajaranId)
    {
        return $query->where('mata_pelajaran_id', $mataPelajaranId);
    }

    /**
     * Scope untuk filter berdasarkan tingkat kelas
     */
    public function scopeByTingkat($query, $tingkat)
    {
        return $query->where('tingkat_kelas', $tingkat);
    }

    /**
     * Scope untuk filter hanya yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
