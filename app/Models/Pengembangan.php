<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengembangan extends Model
{
    protected $table = 'pengembangan_diri';
    protected $fillable = ['nama_kegiatan','jenis_kegiatan','deskripsi','pemateri','tanggal_mulai','tanggal_selesai'];

    public function peserta(): HasMany
    {
        return $this->hasMany(PengembanganPeserta::class, 'pengembangan_id');
    }

    public function sertifikats(): HasMany
    {
        return $this->hasMany(PengembanganSertifikat::class, 'pengembangan_id');
    }
}
