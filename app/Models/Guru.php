<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'nama',
        'nip',
        'email',
        'telepon',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
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
