<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulAbsensi extends Model
{
    use HasFactory;

    protected $table = 'ekskul_absensi';

    protected $fillable = [
        'ekstrakurikuler_id',
        'ekskul_agenda_id',
        'siswa_id',
        'tanggal',
        'status',
        'keterangan',
        'dibukukan_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'status' => 'string',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function ekskulAgenda()
    {
        return $this->belongsTo(EkskulAgenda::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function dibukukanOleh()
    {
        return $this->belongsTo(Guru::class, 'dibukukan_oleh');
    }
}
