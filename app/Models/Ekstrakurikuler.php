<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ekstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'ekstrakurikuler';

    protected $fillable = [
        'nama',
        'deskripsi',
        'lokasi',
        'logo',
        'kuota_max',
        'status',
        'guru_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function pembina()
    {
        return $this->hasMany(EkskulPembina::class);
    }

    public function anggota()
    {
        return $this->hasMany(EkskulAnggota::class);
    }

    public function jadwal()
    {
        return $this->hasMany(EkskulJadwal::class);
    }

    public function agenda()
    {
        return $this->hasMany(EkskulAgenda::class);
    }

    public function absensi()
    {
        return $this->hasMany(EkskulAbsensi::class);
    }

    public function absensiPembina()
    {
        return $this->hasMany(EkskulAbsensiPembina::class);
    }

    public function buktiKegiatan()
    {
        return $this->hasMany(EkskulBuktiKegiatan::class);
    }
}
