<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceVerificationCode extends Model
{
    protected $table = 'attendance_verification_codes';

    protected $fillable = [
        'guru_id', 'kelas_id', 'jam_belajar_id', 'tanggal', 'kode', 'expires_at'
    ];

    protected $casts = [
        'tanggal' => 'date',
        'expires_at' => 'datetime',
    ];

    protected $dates = ['tanggal', 'expires_at', 'created_at', 'updated_at'];
}
