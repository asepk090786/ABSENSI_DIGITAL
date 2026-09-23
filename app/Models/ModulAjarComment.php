<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulAjarComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'modul_ajar_id',
        'pengawas_user_id',
        'komentar',
    ];

    public function modulAjar()
    {
        return $this->belongsTo(RencanaPembelajaran::class, 'modul_ajar_id');
    }

    public function pengawas()
    {
        return $this->belongsTo(User::class, 'pengawas_user_id');
    }
}
