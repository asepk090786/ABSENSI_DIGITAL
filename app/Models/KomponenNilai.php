<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CapaianPembelajaran;
use App\Models\RencanaPembelajaran;
use App\Models\MataPelajaran;

class KomponenNilai extends Model
{
    use HasFactory;

    protected $table = 'komponen_nilai';

    protected $fillable = [
        'guru_id',
        'mata_pelajaran_id',
        'kelas_id',
        'capaian_pembelajaran_id',
        'nama_komponen',
        'bobot',
        'domain',
        'capaian_pembelajaran',
        'tujuan_pembelajaran',
        'alur_tujuan_pembelajaran',
        'indikator_kriteria',
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

    public function kelasMany()
    {
        return $this->belongsToMany(Kelas::class, 'komponen_nilai_kelas', 'komponen_nilai_id', 'kelas_id');
    }

    public function capaianPembelajaran()
    {
        return $this->belongsTo(CapaianPembelajaran::class);
    }

    public function rencanaPembelajaran()
    {
        return $this->belongsToMany(RencanaPembelajaran::class, 'rencana_pembelajaran_komponen_nilai', 'komponen_nilai_id', 'rencana_pembelajaran_id');
    }
}
