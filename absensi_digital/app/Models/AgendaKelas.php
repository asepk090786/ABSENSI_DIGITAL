<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgendaKelas extends Model
{
    use HasFactory;

    protected $table = 'agenda_kelas';
    protected $fillable = ['kelas_id','guru_id','jam_belajar_id','tanggal','kegiatan','tahun_ajaran_id','semester_id'];
}
