<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulBuktiKegiatan extends Model
{
    use HasFactory;

    protected $table = 'ekskul_bukti_kegiatan';

    protected $fillable = [
        'ekstrakurikuler_id',
        'ekskul_agenda_id',
        'judul',
        'deskripsi',
        'file_path',
        'file_type',
        'diupload_oleh',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'file_type' => 'string',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function ekskulAgenda()
    {
        return $this->belongsTo(EkskulAgenda::class);
    }

    public function diuploadOleh()
    {
        return $this->belongsTo(Guru::class, 'diupload_oleh');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
