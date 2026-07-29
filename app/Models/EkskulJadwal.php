<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulJadwal extends Model
{
    use HasFactory;

    protected $table = 'ekskul_jadwal';

    protected $fillable = [
        'ekstrakurikuler_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
        'lokasi',
    ];

    protected $casts = [
        'jam_mulai' => 'string',
        'jam_selesai' => 'string',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }
}
