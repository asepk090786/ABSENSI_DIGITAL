<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KepalaSekolah extends Model
{
    use HasFactory;

    protected $table = 'kepala_sekolah';

    protected $fillable = [
        'guru_id',
        'nama',
        'nip',
        'pangkat_golongan',
        'tanggal_mulai_jabatan',
        'tanggal_selesai_jabatan',
        'status',
        'alamat',
        'telepon',
        'email',
        'foto',
    ];

    protected $casts = [
        'tanggal_mulai_jabatan' => 'date',
        'tanggal_selesai_jabatan' => 'date',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
