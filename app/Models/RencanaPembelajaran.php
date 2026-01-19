<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RencanaPembelajaran extends Model
{
    use HasFactory;

    protected $table = 'rencana_pembelajarans';
    
    protected $fillable = [
        'guru_id',
        'mata_pelajaran_id',
        'kelas_id',
        'jadwal_kbm_id',
        'judul',
        'deskripsi',
        'tujuan',
        'metode',
        'media',
        'sumber',
        'penilaian',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jadwalKbm()
    {
        return $this->belongsTo(JadwalKbm::class);
    }
}
