<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservationEvidence extends Model
{
    use HasFactory;

    protected $table = 'observation_evidences';

    protected $fillable = [
        'supervisi_id',
        'jenis',
        'file_path',
        'nama_file',
        'keterangan',
    ];

    public function supervisi()
    {
        return $this->belongsTo(Supervisi::class);
    }
}
