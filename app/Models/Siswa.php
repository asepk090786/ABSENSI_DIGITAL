<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Kelas;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'nis',
        'nisn',
        'nama',
        'jenis_kelamin',
        'kelas_id',
        'email',
        'status_aktif',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
