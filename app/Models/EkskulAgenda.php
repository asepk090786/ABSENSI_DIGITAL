<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulAgenda extends Model
{
    use HasFactory;

    protected $table = 'ekskul_agenda';

    protected $fillable = [
        'ekstrakurikuler_id',
        'judul',
        'deskripsi',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
        'jenis',
        'materi',
        'status',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'jam_mulai' => 'string',
        'jam_selesai' => 'string',
        'jenis' => 'string',
        'status' => 'string',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function dibuatOleh()
    {
        return $this->belongsTo(Guru::class, 'dibuat_oleh');
    }
}
