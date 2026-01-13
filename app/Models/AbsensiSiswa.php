<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSiswa extends Model
{
    use HasFactory;

    protected $table = 'absensi_siswa';

    protected $fillable = [
        'absensi_kelas_id',
        'siswa_id',
        'status',
        'keterangan',
    ];

    public function absensiKelas()
    {
        return $this->belongsTo(AbsensiKelas::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
