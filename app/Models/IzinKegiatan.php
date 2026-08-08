<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IzinKegiatan extends Model
{
    use HasFactory;

    protected $table = 'izin_kegiatan';

    protected $fillable = [
        'kelas_id',
        'siswa_id',
        'jenis_kegiatan',
        'keterangan_kegiatan',
        'surat_tugas',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
