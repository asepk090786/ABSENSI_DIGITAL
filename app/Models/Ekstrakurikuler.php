<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ekstrakurikuler extends Model
{
    use HasFactory;

    protected $table = 'ekstrakurikuler';

    protected $fillable = [
        'nama_ekskul',
        'deskripsi',
        'pembina_id',
    ];

    public function pembina()
    {
        return $this->belongsTo(Guru::class, 'pembina_id');
    }
}
