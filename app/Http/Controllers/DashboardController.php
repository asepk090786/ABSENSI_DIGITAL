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
            return view('dashboard.guru', compact('guru','siswa','kelas','absensi','tahunAjaran','semestrName'));
        } elseif ($role === 'siswa') {
            return view('dashboard.siswa', compact('guru','siswa','kelas','absensi','tahunAjaran','semestrName'));
        } elseif ($role === 'kepala_sekolah') {
            return view('dashboard.kepala', compact('guru','siswa','kelas','absensi','tahunAjaran','semestrName'));
        } else {
            return view('home', compact('guru','siswa','kelas','absensi','tahunAjaran','semestrName'));
        }
    }
}
