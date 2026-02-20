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
        $absensi = DB::table('absensi_kelas as ak')
            ->leftJoin('guru as g', 'ak.guru_id', '=', 'g.id')
            ->leftJoin('jam_belajar as jb', 'ak.jam_belajar_id', '=', 'jb.id')
            ->where('ak.kelas_id', $kelasBinaan->id)
            ->when($tahunAjaran, function ($query) use ($tahunAjaran) {
                $query->where('ak.tahun_ajaran_id', $tahunAjaran->id);
            })
            ->when($semester, function ($query) use ($semester) {
                $query->where('ak.semester_id', $semester->id);
            })
            ->select(
                'ak.*',
                'g.nama as guru_nama',
                'jb.jam_mulai',
                'jb.jam_selesai'
            )
            ->orderBy('ak.tanggal', 'desc')
            ->get();

        $rekapCounts = collect();
        if ($absensi->isNotEmpty()) {
            $rekapCounts = DB::table('absensi_siswa')
                ->select(
                    'absensi_kelas_id',
                    DB::raw("SUM(CASE WHEN LOWER(status) = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                    DB::raw("SUM(CASE WHEN LOWER(status) = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                    DB::raw("SUM(CASE WHEN LOWER(status) IN ('izin', 'ijin') THEN 1 ELSE 0 END) as izin"),
                    DB::raw("SUM(CASE WHEN LOWER(status) IN ('alpha', 'alfa') THEN 1 ELSE 0 END) as alpha")
                )
                ->whereIn('absensi_kelas_id', $absensi->pluck('id'))
                ->groupBy('absensi_kelas_id')
                ->get()
                ->keyBy('absensi_kelas_id');
        }
        
        return view('wali_kelas.absensi', compact('kelasBinaan', 'absensi', 'rekapCounts', 'guru', 'tahunAjaran', 'semester'));
    }

    public function laporanGuru()
    {
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return redirect()->route('home')->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        $kelasBinaan = DB::table('kelas')
            ->where('wali_kelas_id', $guru->id)
            ->first();

        if (!$kelasBinaan) {
            return redirect()->route('home')->with('error', 'Anda tidak ditugaskan sebagai wali kelas.');
        }

        $laporanGuru = DB::table('laporan_siswa_guru as lsg')
            ->leftJoin('siswa as s', 's.id', '=', 'lsg.siswa_id')
            ->leftJoin('guru as gp', 'gp.id', '=', 'lsg.guru_pelapor_id')
            ->where('lsg.kelas_id', $kelasBinaan->id)
            ->select(
                'lsg.*',
                's.nama as nama_siswa',
                's.nis as nis_siswa',
                'gp.nama as nama_guru_pelapor'
            )
            ->orderBy('lsg.created_at', 'desc')
            ->get();

        return view('wali_kelas.laporan_guru', compact('kelasBinaan', 'laporanGuru', 'guru'));
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

    /**
     * Halaman detail nilai siswa (kelas binaan)
     */
    public function nilaiSiswa($siswaId)
    {
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return redirect()->route('home')->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        $kelasBinaan = DB::table('kelas')
            ->where('wali_kelas_id', $guru->id)
            ->first();

        if (!$kelasBinaan) {
            return redirect()->route('home')->with('error', 'Anda tidak ditugaskan sebagai wali kelas.');
        }

        $siswa = DB::table('siswa')
            ->where('id', $siswaId)
            ->where('kelas_id', $kelasBinaan->id)
            ->first();

        if (!$siswa) {
            return redirect()->route('wali_kelas.nilai')->with('error', 'Siswa tidak ditemukan di kelas binaan.');
        }

        $tahunAjaran = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        $nilai = DB::table('nilai_harian as nh')
            ->leftJoin('mata_pelajaran as mp', 'nh.mapel_id', '=', 'mp.id')
            ->leftJoin('komponen_nilai as kn', 'nh.komponen_id', '=', 'kn.id')
            ->where('nh.siswa_id', $siswaId)
            ->where('nh.kelas_id', $kelasBinaan->id)
            ->when($tahunAjaran, function ($query) use ($tahunAjaran) {
                $query->where('nh.tahun_ajaran_id', $tahunAjaran->id);
            })
            ->when($semester, function ($query) use ($semester) {
                $query->where('nh.semester_id', $semester->id);
            })
            ->select(
                'nh.tanggal',
                'nh.nilai',
                'mp.nama_mapel',
                'kn.nama_komponen'
            )
            ->orderBy('nh.tanggal', 'desc')
            ->get();

        return view('wali_kelas.nilai_detail', compact('kelasBinaan', 'siswa', 'nilai', 'tahunAjaran', 'semester', 'guru'));
    }
}
