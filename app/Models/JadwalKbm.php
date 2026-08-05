<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalKbm extends Model
{
    use HasFactory;

    protected $table = 'jadwal_kbm';

    protected $fillable = [
        'kelas_id',
        'guru_id',
        'mata_pelajaran_id',
        'jam_belajar_id',
        'hari',
        'jam_ke',
        'tahun_ajaran_id',
        'semester_id',
    ];

    /**
     * Relasi ke model Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

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
     * Relasi ke model JamBelajar
     */
    public function jamBelajar()
    {
        return $this->belongsTo(JamBelajar::class);
    }

    /**
     * Relasi ke model TahunAjaran
     */
    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    /**
     * Relasi ke model Semester
     */
    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    /**
     * Scope untuk filter berdasarkan hari
     */
    public function scopeByHari($query, $hari)
    {
        return $query->where('hari', $hari);
    }

    /**
     * Scope untuk filter berdasarkan kelas
     */
    public function scopeByKelas($query, $kelasId)
    {
        return $query->where('kelas_id', $kelasId);
    }

    /**
     * Scope untuk filter berdasarkan guru
     */
    public function scopeByGuru($query, $guruId)
    {
        return $query->where('guru_id', $guruId);
    }

    /**
     * Scope untuk ordering berdasarkan hari dan jam
     */
    public function scopeOrderBySchedule($query)
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $case = collect($days)
            ->map(fn($day, $index) => "WHEN hari = '{$day}' THEN {$index}")
            ->implode(' ');

        return $query->orderByRaw("CASE {$case} ELSE 999 END")->orderBy('jam_ke');
    }
}
