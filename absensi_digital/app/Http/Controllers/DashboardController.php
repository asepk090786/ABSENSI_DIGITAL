<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Be defensive: tests may run on a DB without all tables migrated
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

        return view('home', compact('guru','siswa','kelas','absensi','tahunAjaran','semestrName'));
    }
}
