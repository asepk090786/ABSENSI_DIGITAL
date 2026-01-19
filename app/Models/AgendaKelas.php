<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgendaKelas extends Model
{
    use HasFactory;

    protected $table = 'agenda_kelas';
    protected $fillable = [
        'kelas_id',
        'guru_id',
        'jam_belajar_id',
        'tanggal',
        'kegiatan',
        'tujuan_pembelajaran',
        'strategi_pembelajaran',
        'media_pembelajaran',
        'sumber_belajar',
        'penilaian',
        'catatan_tambahan',
        'tahun_ajaran_id',
        'semester_id'
    ];

    public function kelas()
    {
        return $this->belongsTo(\App\Models\Kelas::class, 'kelas_id');
    }

    public function guru()
    {
        return $this->belongsTo(\App\Models\Guru::class, 'guru_id');
    }

    public function jamBelajar()
    {
        return $this->belongsTo(\App\Models\JamBelajar::class, 'jam_belajar_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(\App\Models\TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function semester()
    {
        return $this->belongsTo(\App\Models\Semester::class, 'semester_id');
    }
}
