<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Semester extends Model
{
    use HasFactory;

    protected $table = 'semester';
    protected $fillable = ['tahun_ajaran_id', 'nama_semester', 'is_active'];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class);
    }
}
