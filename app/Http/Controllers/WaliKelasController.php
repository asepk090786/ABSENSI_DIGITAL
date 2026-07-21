<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
     * Update jabatan_kelas for a siswa in the kelas binaan
     */
    public function updateJabatan(Request $request, $siswaId)
    {
        $user = Auth::user();
        $guru = $user->guru;

        if (! $guru) {
            return redirect()->route('home')->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        $kelasBinaan = DB::table('kelas')
            ->where('wali_kelas_id', $guru->id)
            ->first();

        if (! $kelasBinaan) {
            return redirect()->route('home')->with('error', 'Anda tidak ditugaskan sebagai wali kelas.');
        }

        $validated = $request->validate([
            'jabatan_kelas' => 'nullable|in:ketua,wakil,sekretaris'
        ]);

        $siswa = DB::table('siswa')
            ->where('id', $siswaId)
            ->where('kelas_id', $kelasBinaan->id)
            ->first();

        if (! $siswa) {
            return redirect()->back()->with('error', 'Siswa tidak ditemukan di kelas binaan Anda.');
        }

        DB::table('siswa')->where('id', $siswaId)->update([
            'jabatan_kelas' => $validated['jabatan_kelas'] ?? null,
            'updated_at' => now()
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Jabatan kelas siswa berhasil diperbarui.',
                'jabatan' => $validated['jabatan_kelas'] ?? null,
            ]);
        }

        return redirect()->route('wali_kelas.siswa')->with('success', 'Jabatan kelas siswa berhasil diperbarui.');
    }
    
    /**
     * Halaman absensi kelas binaan
     */
    public function absensi(Request $request)
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

        $selectedTanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $hariIndonesia = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];
        $hariQuery = $hariIndonesia[Carbon::parse($selectedTanggal)->format('l')] ?? Carbon::parse($selectedTanggal)->format('l');
        
        // Get data absensi untuk kelas binaan pada tanggal terpilih
        $absensi = DB::table('absensi_kelas as ak')
            ->leftJoin('guru as g', 'ak.guru_id', '=', 'g.id')
            ->leftJoin('jam_belajar as jb', 'ak.jam_belajar_id', '=', 'jb.id')
            ->where('ak.kelas_id', $kelasBinaan->id)
            ->whereDate('ak.tanggal', $selectedTanggal)
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
                'jb.jam_selesai',
                'jb.urutan as jam_urutan'
            )
            ->orderBy('jb.urutan')
            ->get();

        $jadwalMap = collect();
        if ($tahunAjaran && $semester) {
            $jadwalMap = DB::table('jadwal_kbm as jk')
                ->leftJoin('mata_pelajaran as mp', 'jk.mata_pelajaran_id', '=', 'mp.id')
                ->leftJoin('guru as g2', 'jk.guru_id', '=', 'g2.id')
                ->where('jk.kelas_id', $kelasBinaan->id)
                ->where('jk.hari', $hariQuery)
                ->where('jk.tahun_ajaran_id', $tahunAjaran->id)
                ->where('jk.semester_id', $semester->id)
                ->select(
                    'jk.jam_belajar_id',
                    DB::raw('COALESCE(mp.nama_mapel, \'-\') as mapel_nama'),
                    DB::raw('COALESCE(g2.nama, \'-\') as mapel_guru'),
                    'jk.jam_ke'
                )
                ->get()
                ->keyBy('jam_belajar_id');
        }

        $absensi = $absensi->map(function ($row) use ($jadwalMap) {
            if ($jadwalMap->has($row->jam_belajar_id)) {
                $schedule = $jadwalMap->get($row->jam_belajar_id);
                $row->mapel_nama = $schedule->mapel_nama;
                $row->mapel_guru = $schedule->mapel_guru;
                $row->jam_ke = $schedule->jam_ke;
            } else {
                $row->mapel_nama = '-';
                $row->mapel_guru = '-';
                $row->jam_ke = null;
            }
            return $row;
        });

        $startMonth = now()->startOfMonth()->toDateString();
        $endMonth = now()->endOfMonth()->toDateString();

        $akumulasiTerlambatBulanan = DB::table('pelanggaran_siswa as ps')
            ->join('siswa as s', 's.id', '=', 'ps.siswa_id')
            ->where('ps.kelas_id', $kelasBinaan->id)
            ->whereDate('ps.tanggal', '>=', $startMonth)
            ->whereDate('ps.tanggal', '<=', $endMonth)
            ->whereIn(DB::raw('LOWER(ps.status_absensi)'), ['terlambat', 'telat'])
            ->select(
                'ps.siswa_id',
                's.nama as nama_siswa',
                's.nis as nis_siswa',
                DB::raw('COUNT(ps.id) as total_terlambat'),
                DB::raw('SUM(ps.terlambat_menit) as total_menit_terlambat')
            )
            ->groupBy('ps.siswa_id', 's.nama', 's.nis')
            ->orderByDesc('total_terlambat')
            ->orderByDesc('total_menit_terlambat')
            ->get();

        $rekapCounts = collect();
        if ($absensi->isNotEmpty()) {
            $rekapCounts = DB::table('absensi_siswa')
                ->select(
                    'absensi_kelas_id',
                    DB::raw("SUM(CASE WHEN LOWER(status) = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                    DB::raw("SUM(CASE WHEN LOWER(status) = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                    DB::raw("SUM(CASE WHEN LOWER(status) IN ('izin', 'ijin') THEN 1 ELSE 0 END) as izin"),
                    DB::raw("SUM(CASE WHEN LOWER(status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 1 ELSE 0 END) as alpa")
                )
                ->whereIn('absensi_kelas_id', $absensi->pluck('id'))
                ->groupBy('absensi_kelas_id')
                ->get()
                ->keyBy('absensi_kelas_id');
        }

        $absensiSummary = [
            'terlambat' => 0,
            'tidak_masuk' => 0,
        ];
        if ($absensi->isNotEmpty()) {
            $absensiSiswa = DB::table('absensi_siswa')
                ->whereIn('absensi_kelas_id', $absensi->pluck('id'))
                ->select('siswa_id', DB::raw('LOWER(status) as status'))
                ->get();

            $absensiSummary['terlambat'] = $absensiSiswa
                ->filter(fn ($row) => in_array($row->status, ['terlambat', 'telat'], true))
                ->unique('siswa_id')
                ->count();

            $absensiSummary['tidak_masuk'] = $absensiSiswa
                ->filter(fn ($row) => in_array($row->status, ['alpa', 'alpha', 'alfa', 'absen'], true))
                ->unique('siswa_id')
                ->count();
        }
        
        return view('wali_kelas.absensi', compact('kelasBinaan', 'absensi', 'rekapCounts', 'guru', 'tahunAjaran', 'semester', 'akumulasiTerlambatBulanan', 'selectedTanggal', 'absensiSummary'));
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

        $siswaList = DB::table('siswa')
            ->where('kelas_id', $kelasBinaan->id)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nis']);

        $laporanGuru = DB::table('laporan_siswa_guru as lsg')
            ->leftJoin('siswa as s', 's.id', '=', 'lsg.siswa_id')
            ->leftJoin('guru as gp', 'gp.id', '=', 'lsg.guru_pelapor_id')
            ->where('lsg.kelas_id', $kelasBinaan->id)
            ->select(
                'lsg.*',
                's.nama as nama_siswa',
                's.nis as nis_siswa',
                'gp.nama as nama_guru_pelapor',
                DB::raw('CASE WHEN lsg.absensi_kelas_id IS NULL THEN 1 ELSE 0 END as is_laporan_wali')
            )
            ->orderBy('lsg.created_at', 'desc')
            ->get();

        return view('wali_kelas.laporan_guru', compact('kelasBinaan', 'laporanGuru', 'guru', 'siswaList'));
    }

    public function storeLaporanGuru(Request $request)
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

        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'deskripsi_permasalahan' => 'required|string|min:5',
        ]);

        $siswa = DB::table('siswa')
            ->where('id', $validated['siswa_id'])
            ->where('kelas_id', $kelasBinaan->id)
            ->first();

        if (!$siswa) {
            return redirect()->back()->withErrors(['siswa_id' => 'Siswa tidak berada di kelas binaan Anda.'])->withInput();
        }

        DB::table('laporan_siswa_guru')->insert([
            'absensi_kelas_id' => null,
            'kelas_id' => $kelasBinaan->id,
            'siswa_id' => $validated['siswa_id'],
            'guru_pelapor_id' => $guru->id,
            'wali_kelas_id' => $kelasBinaan->wali_kelas_id,
            'guru_bk_id' => $kelasBinaan->guru_bk_id,
            'deskripsi_permasalahan' => $validated['deskripsi_permasalahan'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('wali_kelas.laporan_guru')
            ->with('success', 'Laporan wali kelas berhasil dikirim ke pembinaan Guru BK.');
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
