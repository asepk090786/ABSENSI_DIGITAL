<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = strtolower(str_replace(' ', '_', $user->role->role_name ?? ''));

        // Data umum untuk dashboard
        $guru = \Illuminate\Support\Facades\Schema::hasTable('guru') ? DB::table('guru')->count() : 0;
        $siswa = \Illuminate\Support\Facades\Schema::hasTable('siswa') ? DB::table('siswa')->count() : 0;
        $kelas = \Illuminate\Support\Facades\Schema::hasTable('kelas') ? DB::table('kelas')->count() : 0;
        $tahun = DB::table('tahun_ajaran')->where('is_active',1)->first();
        $semester = DB::table('semester')->where('is_active',1)->first();
        $tahunAjaran = $tahun ? $tahun->nama_tahun : null;
        $semestrName = $semester ? $semester->nama_semester : null;
        $absensi = 0;
        if (\Illuminate\Support\Facades\Schema::hasTable('absensi_kelas')) {
            if ($tahun && $semester) {
                $absensi = DB::table('absensi_kelas')
                    ->where('tahun_ajaran_id', $tahun->id)
                    ->where('semester_id', $semester->id)
                    ->count();
            } else {
                $absensi = 0;
            }
        }

        // Routing dashboard sesuai role
        if ($role === 'admin') {
            return view('dashboard.admin', compact('guru','siswa','kelas','absensi','tahunAjaran','semestrName'));
        } elseif (in_array($role, ['guru_mapel','guru_kelas','wali_kelas','guru_bk','guru_piket'])) {
            // Data khusus untuk dashboard guru
            $guruData = null;
            if ($user->guru_id) {
                $guruData = DB::table('guru')->where('id', $user->guru_id)->first();
            }
            
            // Statistik Jadwal Mengajar
            $totalJadwal = 0;
            $jadwalHariIni = 0;
            if ($guruData && \Illuminate\Support\Facades\Schema::hasTable('jadwal_kbm')) {
                $totalJadwal = DB::table('jadwal_kbm')
                    ->where('guru_id', $guruData->id)
                    ->count();
                
                $hariIni = date('l');
                $hariIndonesia = [
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                    'Sunday' => 'Minggu'
                ];
                $jadwalHariIni = DB::table('jadwal_kbm')
                    ->where('guru_id', $guruData->id)
                    ->where('hari', $hariIndonesia[$hariIni] ?? $hariIni)
                    ->count();
            }
            
            // Statistik Absensi
            $totalAbsensiGuru = 0;
            $absensiHariIni = 0;
            if ($guruData && \Illuminate\Support\Facades\Schema::hasTable('absensi_kelas')) {
                $totalAbsensiGuru = DB::table('absensi_kelas')
                    ->where('guru_id', $guruData->id)
                    ->count();
                    
                $absensiHariIni = DB::table('absensi_kelas')
                    ->where('guru_id', $guruData->id)
                    ->whereDate('tanggal', date('Y-m-d'))
                    ->count();
            }
            
            // Statistik Agenda Pembelajaran
            $totalAgenda = 0;
            $agendaMingguIni = 0;
            if ($guruData && \Illuminate\Support\Facades\Schema::hasTable('agenda')) {
                $totalAgenda = DB::table('agenda')
                    ->where('guru_id', $guruData->id)
                    ->count();
                    
                $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
                $agendaMingguIni = DB::table('agenda')
                    ->where('guru_id', $guruData->id)
                    ->whereBetween('tanggal', [$startOfWeek, $endOfWeek])
                    ->count();
            }
            
            // Statistik Nilai
            $totalNilai = 0;
            $kelasYangDiajar = 0;
            if ($guruData && \Illuminate\Support\Facades\Schema::hasTable('nilai_harian')) {
                $totalNilai = DB::table('nilai_harian')
                    ->where('guru_id', $guruData->id)
                    ->count();
                    
                $kelasYangDiajar = DB::table('jadwal_kbm')
                    ->where('guru_id', $guruData->id)
                    ->distinct('kelas_id')
                    ->count('kelas_id');
            }
            
            return view('dashboard.guru', compact(
                'guru','siswa','kelas','absensi','tahunAjaran','semestrName',
                'totalJadwal','jadwalHariIni','totalAbsensiGuru','absensiHariIni',
                'totalAgenda','agendaMingguIni','totalNilai','kelasYangDiajar'
            ));
        } elseif ($role === 'siswa') {
            return view('dashboard.siswa', compact('guru','siswa','kelas','absensi','tahunAjaran','semestrName'));
        } elseif ($role === 'kepala_sekolah') {
            return view('dashboard.kepala', compact('guru','siswa','kelas','absensi','tahunAjaran','semestrName'));
        } else {
            return view('dashboard.user');
        }
    }
}
