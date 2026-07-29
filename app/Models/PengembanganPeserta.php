<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengembanganPeserta extends Model
{
    protected $table = 'pengembangan_peserta';
    protected $fillable = ['pengembangan_id','peserta_type','peserta_id'];
}
