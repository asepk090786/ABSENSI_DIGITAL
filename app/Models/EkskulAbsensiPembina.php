<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulAbsensiPembina extends Model
{
    use HasFactory;

    protected $table = 'ekskul_absensi_pembina';

    protected $fillable = [
        'ekstrakurikuler_id',
        'ekskul_agenda_id',
        'guru_id',
        'tanggal',
        'jam_checkin',
        'foto',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_checkin' => 'string',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function ekskulAgenda()
    {
        return $this->belongsTo(EkskulAgenda::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
