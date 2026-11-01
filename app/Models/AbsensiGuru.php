<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsensiGuru extends Model
{
    use HasFactory;

    protected $table = 'absensi_guru';

    protected $fillable = [
        'guru_id',
        'pencatat_guru_id',
        'tanggal',
        'status',
        'keterangan',
        'bukti_path',
        'bukti_token',
        'tahun_ajaran_id',
        'semester_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function pencatat()
    {
        return $this->belongsTo(Guru::class, 'pencatat_guru_id');
    }

    public function logs()
    {
        return $this->hasMany(AbsensiGuruLog::class, 'absensi_guru_id')->orderBy('created_at');
    }
}
