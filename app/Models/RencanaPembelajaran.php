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
        'capaian_pembelajaran_id',
        'kelas_id',
        'jadwal_kbm_id',
        'judul',
        'capaian_pembelajaran',
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

    public function capaianPembelajaran()
    {
        return $this->belongsTo(CapaianPembelajaran::class);
    }

    public function komponenNilai()
    {
        return $this->belongsToMany(KomponenNilai::class, 'rencana_pembelajaran_komponen_nilai', 'rencana_pembelajaran_id', 'komponen_nilai_id');
    }
}
