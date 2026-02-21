<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('Admin');
        $isGuruPanel = $user->hasAnyRole(['Guru', 'Guru Mapel', 'Guru Kelas', 'Wali Kelas', 'Guru BK', 'Guru Piket']);
        $isSiswa = $user->hasRole('Siswa');
        $isKepalaSekolah = $user->hasRole('Kepala Sekolah');

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
        if ($isAdmin) {
            $rekapNilaiKelasMapel = collect();
            $rekapKehadiranSiswaPerKelas = collect();
            $rekapKehadiranGuruHarian = collect();
            $tanggalHariIni = now()->toDateString();
            $kelasLaporanOptions = collect();

            $statistikKehadiranSiswaHarian = (object) [
                'tanggal' => $tanggalHariIni,
                'total_entri' => 0,
                'hadir' => 0,
                'terlambat' => 0,
                'izin' => 0,
                'sakit' => 0,
                'alpha' => 0,
                'persentase_hadir' => 0,
            ];

            $statistikKehadiranGuruHarian = (object) [
                'tanggal' => $tanggalHariIni,
                'total_entri' => 0,
                'hadir' => 0,
                'izin' => 0,
                'sakit' => 0,
                'tidak_hadir' => 0,
                'persentase_hadir' => 0,
            ];

            if (
                \Illuminate\Support\Facades\Schema::hasTable('nilai_harian') &&
                \Illuminate\Support\Facades\Schema::hasTable('kelas') &&
                \Illuminate\Support\Facades\Schema::hasTable('mata_pelajaran')
            ) {
                $rekapNilaiQuery = DB::table('nilai_harian as nh')
                    ->leftJoin('kelas as k', 'nh.kelas_id', '=', 'k.id')
                    ->leftJoin('mata_pelajaran as mp', 'nh.mapel_id', '=', 'mp.id')
                    ->whereNotNull('nh.nilai')
                    ->whereNotNull('nh.kelas_id')
                    ->whereNotNull('nh.mapel_id')
                    ->select(
                        'nh.kelas_id',
                        'nh.mapel_id',
                        DB::raw("COALESCE(k.nama_kelas, '-') as nama_kelas"),
                        DB::raw("COALESCE(mp.nama_mapel, '-') as nama_mapel"),
                        DB::raw('COUNT(nh.id) as total_input_nilai'),
                        DB::raw('COUNT(DISTINCT nh.siswa_id) as total_siswa_terinput'),
                        DB::raw('COUNT(DISTINCT nh.guru_id) as total_guru_penginput'),
                        DB::raw('AVG(nh.nilai) as rata_rata_nilai'),
                        DB::raw('MAX(nh.updated_at) as update_terakhir')
                    )
                    ->groupBy('nh.kelas_id', 'nh.mapel_id', 'k.nama_kelas', 'mp.nama_mapel')
                    ->orderBy('k.nama_kelas')
                    ->orderBy('mp.nama_mapel');

                if ($tahun) {
                    $rekapNilaiQuery->where('nh.tahun_ajaran_id', $tahun->id);
                }

                if ($semester) {
                    $rekapNilaiQuery->where('nh.semester_id', $semester->id);
                }

                $rekapNilaiKelasMapel = $rekapNilaiQuery->get();
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('kelas')) {
                $kelasLaporanOptions = DB::table('kelas')
                    ->select('id', 'nama_kelas')
                    ->orderBy('nama_kelas')
                    ->get();
            }

            if (
                \Illuminate\Support\Facades\Schema::hasTable('absensi_siswa') &&
                \Illuminate\Support\Facades\Schema::hasTable('absensi_kelas') &&
                \Illuminate\Support\Facades\Schema::hasTable('kelas')
            ) {
                $statSiswaQuery = DB::table('absensi_siswa as abs_s')
                    ->join('absensi_kelas as abs_k', 'abs_s.absensi_kelas_id', '=', 'abs_k.id')
                    ->whereDate('abs_k.tanggal', $tanggalHariIni)
                    ->select(
                        DB::raw('COUNT(abs_s.id) as total_entri'),
                        DB::raw("SUM(CASE WHEN LOWER(abs_s.status) = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                        DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('terlambat','telat') THEN 1 ELSE 0 END) as terlambat"),
                        DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('izin','ijin') THEN 1 ELSE 0 END) as izin"),
                        DB::raw("SUM(CASE WHEN LOWER(abs_s.status) = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                        DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 1 ELSE 0 END) as alpha")
                    );

                $rekapSiswaPerKelasQuery = DB::table('absensi_siswa as abs_s')
                    ->join('absensi_kelas as abs_k', 'abs_s.absensi_kelas_id', '=', 'abs_k.id')
                    ->join('kelas as k', 'abs_k.kelas_id', '=', 'k.id')
                    ->select(
                        'k.id as kelas_id',
                        'k.nama_kelas',
                        DB::raw('COUNT(abs_s.id) as total_entri'),
                        DB::raw("SUM(CASE WHEN LOWER(abs_s.status) = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                        DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('terlambat','telat') THEN 1 ELSE 0 END) as terlambat"),
                        DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('izin','ijin') THEN 1 ELSE 0 END) as izin"),
                        DB::raw("SUM(CASE WHEN LOWER(abs_s.status) = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                        DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 1 ELSE 0 END) as alpha"),
                        DB::raw('MAX(abs_k.tanggal) as tanggal_terakhir')
                    )
                    ->groupBy('k.id', 'k.nama_kelas')
                    ->orderBy('k.nama_kelas');

                if ($tahun) {
                    $statSiswaQuery->where('abs_k.tahun_ajaran_id', $tahun->id);
                    $rekapSiswaPerKelasQuery->where('abs_k.tahun_ajaran_id', $tahun->id);
                }

                if ($semester) {
                    $statSiswaQuery->where('abs_k.semester_id', $semester->id);
                    $rekapSiswaPerKelasQuery->where('abs_k.semester_id', $semester->id);
                }

                $statSiswaRaw = $statSiswaQuery->first();
                if ($statSiswaRaw) {
                    $totalSiswaEnt = (int) ($statSiswaRaw->total_entri ?? 0);
                    $hadirSiswa = (int) ($statSiswaRaw->hadir ?? 0);
                    $statistikKehadiranSiswaHarian = (object) [
                        'tanggal' => $tanggalHariIni,
                        'total_entri' => $totalSiswaEnt,
                        'hadir' => $hadirSiswa,
                        'terlambat' => (int) ($statSiswaRaw->terlambat ?? 0),
                        'izin' => (int) ($statSiswaRaw->izin ?? 0),
                        'sakit' => (int) ($statSiswaRaw->sakit ?? 0),
                        'alpha' => (int) ($statSiswaRaw->alpha ?? 0),
                        'persentase_hadir' => $totalSiswaEnt > 0 ? round(($hadirSiswa / $totalSiswaEnt) * 100, 2) : 0,
                    ];
                }

                $rekapKehadiranSiswaPerKelas = $rekapSiswaPerKelasQuery->get();
            }

            if (
                \Illuminate\Support\Facades\Schema::hasTable('absensi_guru') &&
                \Illuminate\Support\Facades\Schema::hasTable('guru')
            ) {
                $statGuruQuery = DB::table('absensi_guru as ag')
                    ->whereDate('ag.tanggal', $tanggalHariIni)
                    ->select(
                        DB::raw('COUNT(ag.id) as total_entri'),
                        DB::raw("SUM(CASE WHEN ag.status = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                        DB::raw("SUM(CASE WHEN ag.status = 'izin' THEN 1 ELSE 0 END) as izin"),
                        DB::raw("SUM(CASE WHEN ag.status = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                        DB::raw("SUM(CASE WHEN ag.status = 'tidak_hadir' THEN 1 ELSE 0 END) as tidak_hadir")
                    );

                $rekapGuruHarianQuery = DB::table('absensi_guru as ag')
                    ->select(
                        'ag.tanggal',
                        DB::raw('COUNT(ag.id) as total_entri'),
                        DB::raw("SUM(CASE WHEN ag.status = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                        DB::raw("SUM(CASE WHEN ag.status = 'izin' THEN 1 ELSE 0 END) as izin"),
                        DB::raw("SUM(CASE WHEN ag.status = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                        DB::raw("SUM(CASE WHEN ag.status = 'tidak_hadir' THEN 1 ELSE 0 END) as tidak_hadir")
                    )
                    ->groupBy('ag.tanggal')
                    ->orderByDesc('ag.tanggal')
                    ->limit(14);

                if ($tahun) {
                    $statGuruQuery->where(function ($q) use ($tahun) {
                        $q->where('ag.tahun_ajaran_id', $tahun->id)
                            ->orWhereNull('ag.tahun_ajaran_id');
                    });
                    $rekapGuruHarianQuery->where(function ($q) use ($tahun) {
                        $q->where('ag.tahun_ajaran_id', $tahun->id)
                            ->orWhereNull('ag.tahun_ajaran_id');
                    });
                }

                if ($semester) {
                    $statGuruQuery->where(function ($q) use ($semester) {
                        $q->where('ag.semester_id', $semester->id)
                            ->orWhereNull('ag.semester_id');
                    });
                    $rekapGuruHarianQuery->where(function ($q) use ($semester) {
                        $q->where('ag.semester_id', $semester->id)
                            ->orWhereNull('ag.semester_id');
                    });
                }

                $statGuruRaw = $statGuruQuery->first();
                if ($statGuruRaw) {
                    $totalGuruEnt = (int) ($statGuruRaw->total_entri ?? 0);
                    $hadirGuru = (int) ($statGuruRaw->hadir ?? 0);
                    $statistikKehadiranGuruHarian = (object) [
                        'tanggal' => $tanggalHariIni,
                        'total_entri' => $totalGuruEnt,
                        'hadir' => $hadirGuru,
                        'izin' => (int) ($statGuruRaw->izin ?? 0),
                        'sakit' => (int) ($statGuruRaw->sakit ?? 0),
                        'tidak_hadir' => (int) ($statGuruRaw->tidak_hadir ?? 0),
                        'persentase_hadir' => $totalGuruEnt > 0 ? round(($hadirGuru / $totalGuruEnt) * 100, 2) : 0,
                    ];
                }

                $rekapKehadiranGuruHarian = $rekapGuruHarianQuery->get();
            }

            return view('dashboard.admin', compact(
                'guru',
                'siswa',
                'kelas',
                'absensi',
                'tahunAjaran',
                'semestrName',
                'rekapNilaiKelasMapel',
                'rekapKehadiranSiswaPerKelas',
                'rekapKehadiranGuruHarian',
                'statistikKehadiranSiswaHarian',
                'statistikKehadiranGuruHarian',
                'kelasLaporanOptions'
            ));
        } elseif ($isGuruPanel) {
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

            $isGuruBk = $user->hasRole('Guru BK');
            $kelasBinaanBk = collect();
            if (
                $isGuruBk &&
                $guruData &&
                \Illuminate\Support\Facades\Schema::hasTable('kelas') &&
                \Illuminate\Support\Facades\Schema::hasColumn('kelas', 'guru_bk_id')
            ) {
                $kelasBinaanBk = DB::table('kelas')
                    ->leftJoin('siswa', 'siswa.kelas_id', '=', 'kelas.id')
                    ->where('kelas.guru_bk_id', $guruData->id)
                    ->select(
                        'kelas.id',
                        'kelas.nama_kelas',
                        'kelas.tingkat_kelas',
                        DB::raw('COUNT(siswa.id) as total_siswa')
                    )
                    ->groupBy('kelas.id', 'kelas.nama_kelas', 'kelas.tingkat_kelas')
                    ->orderBy('kelas.nama_kelas')
                    ->get();
            }
            
            return view('dashboard.guru', compact(
                'guru','siswa','kelas','absensi','tahunAjaran','semestrName',
                'totalJadwal','jadwalHariIni','totalAbsensiGuru','absensiHariIni',
                'totalAgenda','agendaMingguIni','totalNilai','kelasYangDiajar',
                'isGuruBk','kelasBinaanBk'
            ));
        } elseif ($isSiswa) {
            return view('dashboard.siswa', compact('guru','siswa','kelas','absensi','tahunAjaran','semestrName'));
        } elseif ($isKepalaSekolah) {
            $kelasLaporanOptions = collect();
            if (\Illuminate\Support\Facades\Schema::hasTable('kelas')) {
                $kelasLaporanOptions = DB::table('kelas')
                    ->select('id', 'nama_kelas')
                    ->orderBy('nama_kelas')
                    ->get();
            }

            return view('dashboard.kepala', compact('guru','siswa','kelas','absensi','tahunAjaran','semestrName', 'kelasLaporanOptions'));
        } else {
            return view('dashboard.user');
        }
    }
}
