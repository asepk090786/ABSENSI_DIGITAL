<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KomponenNilai extends Model
{
    use HasFactory;

    protected $table = 'komponen_nilai';

    protected $fillable = [
        'nama_komponen',
        'bobot',
    ];
}
