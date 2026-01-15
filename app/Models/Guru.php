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
    ];

    public function kepalaSekolah()
    {
        return $this->hasOne(KepalaSekolah::class);
    }

    public function user()
    {
        return $this->hasOne(User::class, 'guru_id');
    }
}
