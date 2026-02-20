<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;


class Guru extends Model implements AuthenticatableContract
{
    use HasFactory, Authenticatable;

    protected $table = 'guru';


    protected $fillable = [
        'guru_id',
        'nama',
        'nip',
        'kode_guru',
        'username',
        'password',
        'telepon',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
        'is_active',
        'email',
        'foto',
        'jenis_tugas_wakil',
        'hari_piket',
    ];

    protected $casts = [
        'hari_piket' => 'array',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function kepalaSekolah()
    {
        return $this->hasOne(KepalaSekolah::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'guru_id');
    }

    public function tugasGuru()
    {
        return $this->hasMany(TugasGuru::class);
    }

    public function kelasBinaanBk()
    {
        return $this->hasMany(Kelas::class, 'guru_bk_id');
    }
}
