<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiKelas extends Model
{
    use HasFactory;

    protected $table = 'absensi_kelas';

    protected $fillable = [
        'kelas_id',
        'guru_id',
        'jam_belajar_id',
        'tanggal',
        'status_kelas',
        'tahun_ajaran_id',
        'semester_id',
        'verifikasi_aktif',
        'kode_verifikasi',
        'kode_verifikasi_expires_at',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'verifikasi_aktif' => 'boolean',
        'kode_verifikasi_expires_at' => 'datetime',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function jamBelajar()
    {
        return $this->belongsTo(JamBelajar::class);
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class);
    }

    public function absensiSiswa()
    {
        return $this->hasMany(AbsensiSiswa::class);
    }

    protected static function booted()
    {
        static::deleting(function (AbsensiKelas $absensiKelas) {
            $absensiKelas->absensiSiswa()->delete();
        });
    }
}
