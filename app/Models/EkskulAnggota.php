<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulAnggota extends Model
{
    use HasFactory;

    protected $table = 'ekskul_anggota';

    protected $fillable = [
        'ekstrakurikuler_id',
        'siswa_id',
        'status_pendaftaran',
        'tanggal_daftar',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_daftar' => 'date',
        'status_pendaftaran' => 'string',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
