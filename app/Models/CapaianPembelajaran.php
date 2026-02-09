<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CapaianPembelajaran extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_capaian_pembelajaran',
        'deskripsi',
        'fase',
        'tahun_ajaran_id',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }

    public function komponenNilai()
    {
        return $this->hasMany(KomponenNilai::class);
    }
}
