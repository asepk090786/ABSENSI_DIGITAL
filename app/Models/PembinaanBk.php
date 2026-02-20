<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembinaanBk extends Model
{
    use HasFactory;

    protected $table = 'pembinaan_bk';

    protected $fillable = [
        'kelas_id',
        'guru_bk_id',
        'siswa_id',
        'wali_kelas_nama',
        'hadir',
        'sakit',
        'izin',
        'alpa',
        'terlambat',
        'deskripsi_permasalahan',
        'penanganan',
        'tindak_lanjut',
        'bukti_dukung_absensi',
        'laporan_guru',
        'laporan_wali_kelas',
        'bukti_dukung_files',
    ];

    protected $casts = [
        'bukti_dukung_files' => 'array',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function guruBk()
    {
        return $this->belongsTo(Guru::class, 'guru_bk_id');
    }
}
