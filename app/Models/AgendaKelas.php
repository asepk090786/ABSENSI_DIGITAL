<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgendaKelas extends Model
{
    use HasFactory;

    protected $table = 'agenda_kelas';
    protected $fillable = [
        'kelas_id',
        'guru_id',
        'jenis_kegiatan',
        'jam_belajar_id',
        'tanggal',
        'kegiatan',
        'nama_kegiatan',
        'tujuan_pembelajaran',
        'strategi_pembelajaran',
        'media_pembelajaran',
        'sumber_belajar',
        'penilaian',
        'catatan_tambahan',
        'tahun_ajaran_id',
        'semester_id'
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function kelas()
    {
        return $this->belongsTo(\App\Models\Kelas::class, 'kelas_id');
    }

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

    public function getAbsensiSummary()
    {
        $absensiKelas = \App\Models\AbsensiKelas::where('kelas_id', $this->kelas_id)
            ->where('guru_id', $this->guru_id)
            ->where('jam_belajar_id', $this->jam_belajar_id)
            ->whereDate('tanggal', $this->tanggal)
            ->where('tahun_ajaran_id', $this->tahun_ajaran_id)
            ->where('semester_id', $this->semester_id)
            ->first();

        if (!$absensiKelas) {
            return [
                'total' => 0,
                'hadir' => 0,
                'absen' => 0,
                'izin' => 0,
                'sakit' => 0,
            ];
        }

        $absensiSiswa = \App\Models\AbsensiSiswa::where('absensi_kelas_id', $absensiKelas->id)->get();

        $totalSiswa = $absensiSiswa->count();
        $statusCounts = [
            'hadir' => 0,
            'absen' => 0,
            'izin' => 0,
            'sakit' => 0,
        ];

        foreach ($absensiSiswa as $item) {
            $status = strtolower(trim((string) $item->status));

            if ($status === 'hadir') {
                $statusCounts['hadir']++;
            } elseif (in_array($status, ['absen', 'alpa', 'alpha'], true)) {
                $statusCounts['absen']++;
            } elseif ($status === 'izin') {
                $statusCounts['izin']++;
            } elseif ($status === 'sakit') {
                $statusCounts['sakit']++;
            }
        }

        return [
            'total' => $totalSiswa,
            'hadir' => $statusCounts['hadir'],
            'absen' => $statusCounts['absen'],
            'izin' => $statusCounts['izin'],
            'sakit' => $statusCounts['sakit'],
        ];
    }
}
