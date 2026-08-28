<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Guru;
use App\Models\Siswa;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'kode_kelas',
        'tingkat_kelas',
        'jurusan',
        'wali_kelas_id',
        'guru_bk_id',
    ];

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function guruBk()
    {
        return $this->belongsTo(Guru::class, 'guru_bk_id');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    public function tugasGuru()
    {
        return $this->hasMany(TugasGuru::class);
    }
}
