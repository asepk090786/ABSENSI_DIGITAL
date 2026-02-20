<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanSiswaGuru extends Model
{
    use HasFactory;

    protected $table = 'laporan_siswa_guru';

    protected $fillable = [
        'absensi_kelas_id',
        'kelas_id',
        'siswa_id',
        'guru_pelapor_id',
        'wali_kelas_id',
        'guru_bk_id',
        'deskripsi_permasalahan',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guruPelapor()
    {
        return $this->belongsTo(Guru::class, 'guru_pelapor_id');
    }
}
