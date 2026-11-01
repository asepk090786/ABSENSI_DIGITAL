<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbsensiGuruLog extends Model
{
    protected $table = 'absensi_guru_logs';

    protected $fillable = [
        'absensi_guru_id',
        'user_id',
        'actor_name',
        'old_status',
        'new_status',
    ];

    public function absensiGuru()
    {
        return $this->belongsTo(AbsensiGuru::class, 'absensi_guru_id');
    }
}