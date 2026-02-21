<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TindakLanjutBk extends Model
{
    use HasFactory;

    protected $table = 'tindak_lanjut_bk';

    protected $fillable = [
        'kelas_id',
        'siswa_id',
        'guru_bk_id',
        'nama_siswa',
        'nama_kelas',
        'nis',
        'nisn',
        'nama_wali_kelas',
        'nama_guru_bk',
        'waktu',
        'nama_penyusun',
        'rencana_items',
    ];

    protected $casts = [
        'rencana_items' => 'array',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function guruBk()
    {
        return $this->belongsTo(Guru::class, 'guru_bk_id');
    }
}
