<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengembanganSertifikat extends Model
{
    protected $table = 'pengembangan_sertifikats';
    protected $fillable = ['pengembangan_id','peserta_type','peserta_id','peserta_name','instansi','file_path','barcode','nomor_sertifikat','template_id','verified_at','is_visible','bukti_dukung_daftar_hadir','bukti_dukung_dokumentasi','bukti_dukung_materi'];
    protected $casts = [
        'verified_at' => 'datetime',
        'is_visible' => 'boolean',
        'bukti_dukung_dokumentasi' => 'array',
        'bukti_dukung_materi' => 'array',
    ];

    public function pengembangan()
    {
        return $this->belongsTo(\App\Models\Pengembangan::class, 'pengembangan_id');
    }
}
