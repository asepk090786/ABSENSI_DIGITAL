<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajaran';
    protected $fillable = ['nama_tahun', 'is_active'];

    public function semesters()
    {
        return $this->hasMany(Semester::class);
    }
}
