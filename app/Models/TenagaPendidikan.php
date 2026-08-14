<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TenagaPendidikan extends Model
{
    use HasFactory;

    protected $table = 'tenaga_pendidikan';

    protected $fillable = [
        'nama',
        'nip',
        'jabatan',
        'telepon',
        'alamat',
        'tanggal_lahir',
        'jenis_kelamin',
        'email',
        'foto',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->hasOne(User::class);
    }
}
