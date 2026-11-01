<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EkskulSertifikat extends Model
{
    protected $table = 'ekskul_sertifikats';

    protected $fillable = [
        'ekstrakurikuler_id',
        'ekskul_agenda_id',
        'siswa_id',
        'file_path',
        'barcode',
        'nomor_sertifikat',
        'template_id',
        'verified_at',
        'is_visible',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'is_visible' => 'boolean',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function ekskulAgenda()
    {
        return $this->belongsTo(EkskulAgenda::class, 'ekskul_agenda_id');
    }
}