<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriPembelajaran extends Model
{
    use HasFactory;

    protected $table = 'materi_pembelajarans';
    
    protected $fillable = [
        'guru_id',
        'rencana_pembelajaran_id',
        'nama_kegiatan',
        'materi_pembelajaran',
        'link_pembelajaran_daring',
        'bukti_pembelajaran',
        'status',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function rencanaPembelajaran()
    {
        return $this->belongsTo(RencanaPembelajaran::class);
    }
}
