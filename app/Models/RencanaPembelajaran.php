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
        'alokasi_waktu',
        'dimensi_lulusan',
        'praktik_pedagogis',
        'lingkungan_pembelajaran',
        'pemanfaatan_digital',
        'pengalaman_pembelajaran',
        'refleksi_pembelajaran',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'html_content',
        'original_docx_path',
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

    public function document()
    {
        return $this->hasOne(ModulAjarDocument::class, 'modul_ajar_id');
    }

    public function documentVersions()
    {
        return $this->hasMany(ModulAjarDocumentVersion::class, 'modul_ajar_id')->orderByDesc('version');
    }
}
