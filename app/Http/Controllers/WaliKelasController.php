<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class WaliKelasController extends Controller
{
    /**
     * Halaman dashboard wali kelas
     */
    public function index()
    {
        $user = Auth::user();
        $guru = $user->guru;
        
        if (!$guru) {
            return redirect()->route('home')->with('error', 'Anda tidak terdaftar sebagai guru.');
        }
        
        // Get kelas binaan
        $kelasBinaan = DB::table('kelas')
            ->where('wali_kelas_id', $guru->id)
            ->first();
        
        if (!$kelasBinaan) {
            return redirect()->route('home')->with('error', 'Anda tidak ditugaskan sebagai wali kelas.');
        }
        
        // Get jumlah siswa di kelas binaan
        $jumlahSiswa = DB::table('siswa')
            ->where('kelas_id', $kelasBinaan->id)
            ->count();
        
        // Get tahun ajaran dan semester aktif
        $tahunAjaran = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        
        return view('wali_kelas.index', compact('kelasBinaan', 'jumlahSiswa', 'tahunAjaran', 'semester', 'guru'));
    }
    
    /**
     * Halaman data siswa kelas binaan
     */
    public function siswa()
    {
        $user = Auth::user();
        $guru = $user->guru;
        
        if (!$guru) {
            return redirect()->route('home')->with('error', 'Anda tidak terdaftar sebagai guru.');
        }
        
        // Get kelas binaan
        $kelasBinaan = DB::table('kelas')
            ->where('wali_kelas_id', $guru->id)
            ->first();
        
        if (!$kelasBinaan) {
            return redirect()->route('home')->with('error', 'Anda tidak ditugaskan sebagai wali kelas.');
        }
        
        // Get siswa di kelas binaan
        $siswa = DB::table('siswa')
            ->where('kelas_id', $kelasBinaan->id)
            ->orderBy('nama')
            ->get();
        
        return view('wali_kelas.siswa', compact('kelasBinaan', 'siswa', 'guru'));
    }
    
    /**
     * Halaman absensi kelas binaan
     */
    public function absensi()
    {
        $user = Auth::user();
        $guru = $user->guru;
        
        if (!$guru) {
            return redirect()->route('home')->with('error', 'Anda tidak terdaftar sebagai guru.');
        }
        
        // Get kelas binaan
        $kelasBinaan = DB::table('kelas')
            ->where('wali_kelas_id', $guru->id)
            ->first();
        
        if (!$kelasBinaan) {
            return redirect()->route('home')->with('error', 'Anda tidak ditugaskan sebagai wali kelas.');
        }
        
        // Get tahun ajaran dan semester aktif
        $tahunAjaran = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        
        // Get data absensi untuk kelas binaan
        $absensi = DB::table('absensi')
            ->where('kelas_id', $kelasBinaan->id)
            ->where('tahun_ajaran_id', $tahunAjaran->id ?? 0)
            ->where('semester_id', $semester->id ?? 0)
            ->orderBy('tanggal', 'desc')
            ->get();
        
        return view('wali_kelas.absensi', compact('kelasBinaan', 'absensi', 'guru', 'tahunAjaran', 'semester'));
    }
    
    /**
     * Halaman nilai siswa kelas binaan
     */
    public function nilai()
    {
        $user = Auth::user();
        $guru = $user->guru;
        
        if (!$guru) {
            return redirect()->route('home')->with('error', 'Anda tidak terdaftar sebagai guru.');
        }
        
        // Get kelas binaan
        $kelasBinaan = DB::table('kelas')
            ->where('wali_kelas_id', $guru->id)
            ->first();
        
        if (!$kelasBinaan) {
            return redirect()->route('home')->with('error', 'Anda tidak ditugaskan sebagai wali kelas.');
        }
        
        // Get tahun ajaran dan semester aktif
        $tahunAjaran = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        
        // Get siswa di kelas binaan
        $siswa = DB::table('siswa')
            ->where('kelas_id', $kelasBinaan->id)
            ->orderBy('nama')
            ->get();
        
        return view('wali_kelas.nilai', compact('kelasBinaan', 'siswa', 'guru', 'tahunAjaran', 'semester'));
    }
}
