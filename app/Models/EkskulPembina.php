<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulPembina extends Model
{
    use HasFactory;

    protected $table = 'ekskul_pembina';

    protected $fillable = [
        'ekstrakurikuler_id',
        'guru_id',
        'jabatan',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function ekstrakurikuler()
    {
        return $this->belongsTo(Ekstrakurikuler::class);
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }
}
