<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgendaGuru extends Model
{
    use HasFactory;

    protected $table = 'agenda_guru';
    protected $fillable = [
        'guru_id',
        'jam_belajar_id',
        'tanggal',
        'kegiatan',
        'tahun_ajaran_id',
        'semester_id'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function guru()
    {
        return $this->belongsTo(\App\Models\Guru::class, 'guru_id');
    }

    public function jamBelajar()
    {
        return $this->belongsTo(\App\Models\JamBelajar::class, 'jam_belajar_id');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(\App\Models\TahunAjaran::class, 'tahun_ajaran_id');
    }

    public function semester()
    {
        return $this->belongsTo(\App\Models\Semester::class, 'semester_id');
    }

    /**
     * Get all attendance records linked to this agenda guru
     * Through agenda_kelas relationship
     */
    public function absensiKelas()
    {
        return \Illuminate\Support\Facades\DB::table('absensi_kelas')
            ->where('guru_id', $this->guru_id)
            ->where('jam_belajar_id', $this->jam_belajar_id)
            ->whereDate('tanggal', $this->tanggal)
            ->where('tahun_ajaran_id', $this->tahun_ajaran_id)
            ->where('semester_id', $this->semester_id);
    }

    /**
     * Get summary of attendance for this time slot
     */
    public function getAbsensiSummary()
    {
        $absensi = $this->absensiKelas()->get();
        
        $totalSiswa = 0;
        $siswaHadir = 0;
        $siswaAbsen = 0;
        $siswaIzin = 0;
        $siswaSakit = 0;
        
        foreach ($absensi as $a) {
            $absensiSiswa = \App\Models\AbsensiSiswa::where('absensi_kelas_id', $a->id)->get();
            $totalSiswa += $absensiSiswa->count();
            
            foreach ($absensiSiswa as $as) {
                if ($as->status === 'Hadir') $siswaHadir++;
                elseif ($as->status === 'Absen') $siswaAbsen++;
                elseif ($as->status === 'Izin') $siswaIzin++;
                elseif ($as->status === 'Sakit') $siswaSakit++;
            }
        }
        
        return [
            'total' => $totalSiswa,
            'hadir' => $siswaHadir,
            'absen' => $siswaAbsen,
            'izin' => $siswaIzin,
            'sakit' => $siswaSakit,
        ];
    }
}
