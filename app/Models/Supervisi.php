<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supervisi extends Model
{
    use HasFactory;

    protected $table = 'supervisi';

    protected $fillable = [
        'guru_id',
        'mata_pelajaran_id',
        'kelas_id',
        'jadwal_kbm_id',
        'tanggal',
        'jam_ke',
        'tujuan',
        'fokus',
        'supervisor_id',
        'status',
        'keterangan',
        'catatan_objektif',
        'refleksi_guru',
        'refleksi_supervisor',
        'umpan_balik',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(Guru::class, 'supervisor_id');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function jadwalKbm()
    {
        return $this->belongsTo(JadwalKbm::class);
    }

    public function observationItems()
    {
        return $this->hasMany(ObservationItem::class, 'supervisi_id');
    }

    public function evidences()
    {
        return $this->hasMany(ObservationEvidence::class, 'supervisi_id');
    }

    public function postConference()
    {
        return $this->hasOne(PostConference::class, 'supervisi_id');
    }
}
