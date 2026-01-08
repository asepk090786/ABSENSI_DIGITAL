<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Role;
use App\Models\Guru;
use App\Models\KepalaSekolah;
use App\Models\Siswa;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'name',
        'nip',
        'username',
        'email',
        'password',
        'jenis_kelamin',
        'is_active',
        'role_id',
        'guru_id',
        'kepala_sekolah_id',
        'siswa_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function kepalaSekolah()
    {
        return $this->belongsTo(KepalaSekolah::class);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }
}
