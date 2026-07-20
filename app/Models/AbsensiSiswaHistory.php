<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiSiswaHistory extends Model
{
    use HasFactory;

    protected $table = 'absensi_siswa_history';

    protected $fillable = [
        'absensi_siswa_id',
        'absensi_kelas_id',
        'siswa_id',
        'previous_status',
        'new_status',
        'previous_keterangan',
        'new_keterangan',
        'changed_by',
    ];
}
