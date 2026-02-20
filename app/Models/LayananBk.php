<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LayananBk extends Model
{
    use HasFactory;

    protected $table = 'layanan_bk';

    protected $fillable = [
        'kelas_id',
        'guru_bk_id',
        'siswa_id',
        'tanggal',
        'jenis_layanan',
        'deskripsi_layanan',
        'hasil_layanan',
        'rencana_tindak_lanjut',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guruBk()
    {
        return $this->belongsTo(Guru::class, 'guru_bk_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
