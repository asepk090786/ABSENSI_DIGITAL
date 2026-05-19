<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AbsensiKelas;
use App\Models\AgendaGuru;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\JamBelajar;
use App\Models\Siswa;
use App\Models\AbsensiSiswa;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\JadwalKbm;
use App\Models\LaporanSiswaGuru;
use App\Exports\AbsensiBkMonitoringExport;
use App\Exports\AbsensiLaporanSiswaHarianExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class AbsensiController extends Controller
{
    public function index(Request $request)
    {
        $tahun = DB::table('tahun_ajaran')->where('is_active',1)->first();
        $semester = DB::table('semester')->where('is_active',1)->first();

        $selectedTanggal = $request->get('tanggal');
        if (empty($selectedTanggal)) {
            $selectedTanggal = Carbon::today()->format('Y-m-d');
        }

        if (! $tahun || ! $semester) {
            $items = collect();
            return view('absensi.index', compact('items'))
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $user = auth()->user();
        $isAdminOrKepala = $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $isGuruPiket = $user->hasRole('Guru Piket') || !empty((array) ($user->guru->hari_piket ?? []));
        $isGuruBk = $user->hasRole('Guru BK');
        $hasGuruBkKelasColumn = Schema::hasTable('kelas') && Schema::hasColumn('kelas', 'guru_bk_id');

        $kelasAktifDijadwalIds = JadwalKbm::query()
            ->when($tahun, function ($query) use ($tahun) {
                $query->where('tahun_ajaran_id', $tahun->id);
            })
            ->when($semester, function ($query) use ($semester) {
                $query->where('semester_id', $semester->id);
            })
            ->select('kelas_id')
            ->distinct()
            ->pluck('kelas_id');

        $kelasAktifDiabsensiIds = AbsensiKelas::query()
            ->where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id)
            ->select('kelas_id')
            ->distinct()
            ->pluck('kelas_id');

        $kelasAktifDigunakanIds = $kelasAktifDijadwalIds
            ->merge($kelasAktifDiabsensiIds)
            ->unique()
            ->values();

        $query = AbsensiKelas::with(['kelas', 'guru', 'jamBelajar', 'tahunAjaran', 'semester', 'absensiSiswa'])
            ->where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id);

        if ($isAdminOrKepala) {
            if ($kelasAktifDigunakanIds->isNotEmpty()) {
                $query->whereIn('kelas_id', $kelasAktifDigunakanIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        } elseif ($user->guru_id && $isGuruBk && $hasGuruBkKelasColumn) {
            $query->whereHas('kelas', function ($kelasQuery) use ($user) {
                $kelasQuery->where('guru_bk_id', $user->guru_id);
            });
        }
        
        // Filter by guru_id if user is a teacher (not admin or kepala sekolah)
        if ($user->guru_id && !$isAdminOrKepala && !$isGuruPiket && !$isGuruBk) {
            $query->where('guru_id', $user->guru_id);
        }
        
        $items = $query->orderBy('tanggal', 'desc')->get();
        
        // Get quick access classes for teacher
        $kelasQuickAccess = collect();
        $rekapPerKelas = collect();
        $siswaPerluPerhatian = collect();

        if ($user->guru_id && $isGuruBk && $hasGuruBkKelasColumn) {
            $kelasQuickAccess = Kelas::with('waliKelas')
                ->where('guru_bk_id', $user->guru_id)
                ->orderBy('nama_kelas')
                ->get();

            $siswaPerluPerhatian = $this->getGuruBkMonitoringData($user->guru_id, $selectedTanggal);
        } elseif ($user->guru_id && !$isAdminOrKepala && !$isGuruPiket) {
            // Get all classes taught by this teacher in current semester
            $kelasQuickAccess = JadwalKbm::with(['kelas'])
                ->where('guru_id', $user->guru_id)
                ->where('tahun_ajaran_id', $tahun->id)
                ->where('semester_id', $semester->id)
                ->get()
                ->pluck('kelas')
                ->unique('id')
                ->sortBy('nama_kelas')
                ->values();
        } elseif ($isAdminOrKepala || $isGuruPiket) {
            $kelasQuickAccess = Kelas::with('waliKelas')
                ->whereIn('id', $kelasAktifDigunakanIds)
                ->orderBy('nama_kelas')
                ->get();

            if ($kelasQuickAccess->isNotEmpty()) {
                $kelasIds = $kelasQuickAccess->pluck('id');

                $rekapHarianPerKelas = $this->getDailyAttendanceSummaryPerClass(
                    $kelasIds,
                    $tahun->id,
                    $semester->id,
                    $selectedTanggal
                )->keyBy('kelas_id');

                $totalPertemuanPerKelas = AbsensiKelas::query()
                    ->whereIn('kelas_id', $kelasIds)
                    ->where('tahun_ajaran_id', $tahun->id)
                    ->where('semester_id', $semester->id)
                    ->select('kelas_id', DB::raw('COUNT(DISTINCT tanggal) as total_pertemuan'))
                    ->groupBy('kelas_id')
                    ->pluck('total_pertemuan', 'kelas_id');

                $totalSiswaAktifPerKelas = DB::table('siswa')
                    ->whereIn('kelas_id', $kelasIds)
                    ->where('status_aktif', 1)
                    ->select('kelas_id', DB::raw('COUNT(id) as total_siswa'))
                    ->groupBy('kelas_id')
                    ->pluck('total_siswa', 'kelas_id');

                $rekapPerKelas = $kelasQuickAccess->map(function ($kelas) use ($rekapHarianPerKelas, $totalPertemuanPerKelas, $totalSiswaAktifPerKelas) {
                    $summary = $rekapHarianPerKelas->get($kelas->id);

                    return (object) [
                        'kelas' => $kelas,
                        'total_pertemuan' => (int) ($totalPertemuanPerKelas[$kelas->id] ?? 0),
                        'total_hadir' => (int) ($summary->total_hadir ?? 0),
                        'total_terlambat' => (int) ($summary->total_terlambat ?? 0),
                        'total_sakit' => (int) ($summary->total_sakit ?? 0),
                        'total_izin' => (int) ($summary->total_izin ?? 0),
                        'total_alpha' => (int) ($summary->total_alpha ?? 0),
                        'total_data_siswa' => (int) ($totalSiswaAktifPerKelas[$kelas->id] ?? 0),
                    ];
                })->values();
            }
        }
        
        return view('absensi.index', compact('items', 'kelasQuickAccess', 'rekapPerKelas', 'selectedTanggal', 'isGuruPiket', 'isGuruBk', 'siswaPerluPerhatian'));
    }

    public function exportBkMonitoring(Request $request)
    {
        $user = auth()->user();
        $selectedTanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));

        if (! $user || ! $user->hasRole('Guru BK') || empty($user->guru_id)) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Guru BK.');
        }

        if (! Schema::hasTable('kelas') || ! Schema::hasColumn('kelas', 'guru_bk_id')) {
            return redirect()->route('absensi.index')
                ->with('error', 'Kolom kelas binaan Guru BK belum tersedia. Jalankan migrasi terlebih dahulu.');
        }

        $rows = $this->getGuruBkMonitoringData($user->guru_id, $selectedTanggal);

        $filename = 'monitoring_absensi_bk_' . Carbon::parse($selectedTanggal)->format('Ymd') . '.xlsx';

        return Excel::download(new AbsensiBkMonitoringExport($rows), $filename);
    }

    public function printLaporanSiswa(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Admin dan Kepala Sekolah.');
        }

        $selectedTanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $kelasId = $request->get('kelas_id');
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        $laporanRows = $this->getDailyStudentReportRows($selectedTanggal, $kelasId, $tahun, $semester);

        $summary = [
            'hadir' => $laporanRows->filter(fn ($row) => strtolower((string) $row->status) === 'hadir')->count(),
            'terlambat' => $laporanRows->filter(fn ($row) => in_array(strtolower((string) $row->status), ['terlambat', 'telat'], true))->count(),
            'izin' => $laporanRows->filter(fn ($row) => in_array(strtolower((string) $row->status), ['izin', 'ijin'], true))->count(),
            'sakit' => $laporanRows->filter(fn ($row) => strtolower((string) $row->status) === 'sakit')->count(),
            'alpha' => $laporanRows->filter(fn ($row) => in_array(strtolower((string) $row->status), ['alpha', 'alpa', 'alfa', 'absen', 'tidak_hadir'], true))->count(),
            'total' => $laporanRows->count(),
        ];

        $sekolah = DB::table('sekolah')->first();
        $kelasLabel = null;
        if ($kelasId) {
            $kelasLabel = DB::table('kelas')->where('id', $kelasId)->value('nama_kelas');
        }

        $pdf = \PDF::loadView('absensi.reports.siswa_pdf', compact(
            'laporanRows',
            'summary',
            'selectedTanggal',
            'kelasLabel',
            'tahun',
            'semester',
            'sekolah'
        ));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Laporan-Kehadiran-Siswa-' . $selectedTanggal . '.pdf');
    }

    public function exportLaporanSiswa(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Admin dan Kepala Sekolah.');
        }

        $selectedTanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $kelasId = $request->get('kelas_id');
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        $laporanRows = $this->getDailyStudentReportRows($selectedTanggal, $kelasId, $tahun, $semester);

        $filename = 'Laporan-Kehadiran-Siswa-Harian-' . Carbon::parse($selectedTanggal)->format('Ymd') . '.xlsx';

        return Excel::download(new AbsensiLaporanSiswaHarianExport($laporanRows), $filename);
    }

    public function printLaporanGuru(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Admin dan Kepala Sekolah.');
        }

        $selectedTanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        $laporanQuery = DB::table('absensi_guru as ag')
            ->join('guru as g', 'ag.guru_id', '=', 'g.id')
            ->leftJoin('guru as pg', 'ag.pencatat_guru_id', '=', 'pg.id')
            ->whereDate('ag.tanggal', $selectedTanggal)
            ->select(
                'ag.tanggal',
                DB::raw("COALESCE(g.nama, '-') as nama_guru"),
                DB::raw("COALESCE(g.nip, '-') as nip"),
                'ag.status',
                'ag.keterangan',
                DB::raw("COALESCE(pg.nama, '-') as dicatat_oleh")
            )
            ->orderBy('g.nama');

        if ($tahun) {
            $laporanQuery->where(function ($q) use ($tahun) {
                $q->where('ag.tahun_ajaran_id', $tahun->id)
                    ->orWhereNull('ag.tahun_ajaran_id');
            });
        }

        if ($semester) {
            $laporanQuery->where(function ($q) use ($semester) {
                $q->where('ag.semester_id', $semester->id)
                    ->orWhereNull('ag.semester_id');
            });
        }

        $laporanRows = $laporanQuery->get();

        $summary = [
            'hadir' => $laporanRows->where('status', 'hadir')->count(),
            'izin' => $laporanRows->where('status', 'izin')->count(),
            'sakit' => $laporanRows->where('status', 'sakit')->count(),
            'tidak_hadir' => $laporanRows->where('status', 'tidak_hadir')->count(),
            'total' => $laporanRows->count(),
        ];

        $sekolah = DB::table('sekolah')->first();

        $pdf = \PDF::loadView('absensi.reports.guru_pdf', compact(
            'laporanRows',
            'summary',
            'selectedTanggal',
            'tahun',
            'semester',
            'sekolah'
        ));
        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream('Laporan-Kehadiran-Guru-' . $selectedTanggal . '.pdf');
    }

    public function printGuruRekap(Request $request)
    {
        $user = auth()->user();
        if (! $user || $user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk akun guru.');
        }

        $isGuruPiket = $user->hasRole('Guru Piket') || !empty((array) ($user->guru->hari_piket ?? []));
        if ($isGuruPiket) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk akun guru biasa.');
        }

        if (empty($user->guru_id)) {
            abort(403, 'Akun guru tidak ditemukan.');
        }

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        if (! $tahun || ! $semester) {
            return redirect()->route('absensi.index')
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $query = AbsensiKelas::with(['kelas', 'guru', 'jamBelajar', 'tahunAjaran', 'semester', 'absensiSiswa'])
            ->where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id);

        $isGuruBk = $user->hasRole('Guru BK');
        if ($isGuruBk && Schema::hasTable('kelas') && Schema::hasColumn('kelas', 'guru_bk_id')) {
            $query->whereHas('kelas', function ($kelasQuery) use ($user) {
                $kelasQuery->where('guru_bk_id', $user->guru_id);
            });
        } else {
            $query->where('guru_id', $user->guru_id);
        }

        $items = $query->orderBy('tanggal', 'desc')->get();

        $summary = [
            'total_sessions' => $items->count(),
            'total_hadir' => $items->sum(function ($item) {
                return $item->absensiSiswa->filter(function ($row) {
                    return in_array(strtolower((string) ($row->status ?? '')), ['hadir'], true);
                })->count();
            }),
            'total_terlambat' => $items->sum(function ($item) {
                return $item->absensiSiswa->filter(function ($row) {
                    return in_array(strtolower((string) ($row->status ?? '')), ['terlambat', 'telat'], true);
                })->count();
            }),
            'total_sakit' => $items->sum(function ($item) {
                return $item->absensiSiswa->filter(function ($row) {
                    return strtolower((string) ($row->status ?? '')) === 'sakit';
                })->count();
            }),
            'total_izin' => $items->sum(function ($item) {
                return $item->absensiSiswa->filter(function ($row) {
                    return in_array(strtolower((string) ($row->status ?? '')), ['izin', 'ijin'], true);
                })->count();
            }),
            'total_alpha' => $items->sum(function ($item) {
                return $item->absensiSiswa->filter(function ($row) {
                    return in_array(strtolower((string) ($row->status ?? '')), ['alpa', 'alpha', 'alfa', 'absen'], true);
                })->count();
            }),
            'total_siswa' => $items->sum(function ($item) {
                return $item->absensiSiswa->count();
            }),
        ];

        $sekolah = DB::table('sekolah')->first();

        $pdf = \PDF::loadView('absensi.reports.guru_kelas_pdf', compact(
            'items',
            'summary',
            'tahun',
            'semester',
            'sekolah'
        ));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap-Absensi-Guru-' . Carbon::now()->format('YmdHis') . '.pdf');
    }

    public function create(Request $request)
    {
        $tahunAjaran = TahunAjaran::where('is_active', 1)->first();
        $semester = Semester::where('is_active', 1)->first();

        if (!$tahunAjaran || !$semester) {
            return redirect()->route('absensi.index')
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $user = auth()->user();
        if ($user->hasRole('Siswa') && ! $user->hasClassPosition()) {
            return redirect()->route('home')->with('error', 'Akses ditolak. Hanya siswa dengan jabatan kelas yang bisa menginput absensi.');
        }

        $isSiswaOfficer = $user->hasRole('Siswa') && $user->hasClassPosition();
        $isAdminOrKepala = $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $isGuruPiket = $user->hasRole('Guru Piket') || !empty((array) ($user->guru->hari_piket ?? []));
        $isGuruBk = $user->hasRole('Guru BK');
        $selectedKelasId = $request->get('kelas_id');
        $selectedJamBelajarId = null;
        $isQuickAccess = false;
        $selectedDate = $request->get('tanggal', date('Y-m-d'));
        
        // Check if this is quick access or manual with kelas preselected
        if (!empty($selectedKelasId)) {
            $isQuickAccess = true;
        }

        if ($isSiswaOfficer) {
            $siswa = $user->siswa;
            $selectedKelasId = $siswa->kelas_id;
            $isQuickAccess = true;
        }
        
        // Validate teacher schedule access
        if ($user->guru_id && !$isAdminOrKepala && !$isGuruPiket && !$isGuruBk) {
            if ($selectedKelasId) {
                $hariIndonesia = [
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                    'Sunday' => 'Minggu'
                ];
                $hariEnglish = Carbon::parse($selectedDate)->format('l');
                $hariQuery = $hariIndonesia[$hariEnglish] ?? $hariEnglish;
                
                $hasSchedule = JadwalKbm::where('guru_id', $user->guru_id)
                    ->where('kelas_id', $selectedKelasId)
                    ->where('hari', $hariQuery)
                    ->where('tahun_ajaran_id', $tahunAjaran->id)
                    ->where('semester_id', $semester->id)
                    ->exists();
                    
                if (!$hasSchedule) {
                    // Don't redirect, just show warning - let user change date
                    session()->flash('warning', 'Anda tidak memiliki jadwal mengajar di kelas ini pada hari ' . $hariQuery . ' (' . date('d/m/Y', strtotime($selectedDate)) . '). Silakan pilih tanggal lain.');
                    // Clear selectedKelasId so form shows normally but kelas is pre-selected
                    // Keep the kelas selected but don't auto-load siswa
                }
            }
        }

        $hariIndonesia = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];

        $multiSlotJadwal = collect();
        
        // Get jadwal for current user if they are a teacher
        if ($user->guru_id && !$isAdminOrKepala && !$isGuruPiket) {
            // Get today's schedule for display
            $hariIni = date('l');
            $jadwalHariIni = JadwalKbm::with(['kelas', 'jamBelajar', 'mataPelajaran'])
                ->where('guru_id', $user->guru_id)
                ->where('hari', $hariIndonesia[$hariIni] ?? $hariIni)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('semester_id', $semester->id)
                ->orderBy('jam_ke')
                ->get();
            
            // If coming from quick access, auto-fill jam belajar from today's schedule
            if ($isQuickAccess && $selectedKelasId) {
                $jadwalKelas = $jadwalHariIni->where('kelas_id', $selectedKelasId)->first();
                if ($jadwalKelas) {
                    $selectedJamBelajarId = $jadwalKelas->jam_belajar_id;
                }
            }
            
            // Get ALL classes taught by this teacher (all days)
            $allJadwal = JadwalKbm::with(['kelas'])
                ->where('guru_id', $user->guru_id)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('semester_id', $semester->id)
                ->get();
            
            // Get unique classes from all schedules
            $kelasList = $allJadwal->pluck('kelas')->unique('id')->sortBy('nama_kelas')->values();

            if ($isGuruBk && Schema::hasTable('kelas') && Schema::hasColumn('kelas', 'guru_bk_id')) {
                $kelasBinaanIds = Kelas::where('guru_bk_id', $user->guru_id)->pluck('id');
                $additionalKelas = Kelas::whereIn('id', $kelasBinaanIds)->get();
                $kelasList = $kelasList->merge($additionalKelas)->unique('id')->sortBy('nama_kelas')->values();
            }
            
            // Filter jam belajar based on teacher's schedule for selected class and date
            if ($selectedKelasId) {
                $hariEnglish = Carbon::parse($selectedDate)->format('l');
                $hariQuery = $hariIndonesia[$hariEnglish] ?? $hariEnglish;

                $multiSlotJadwal = JadwalKbm::with(['jamBelajar'])
                    ->where('guru_id', $user->guru_id)
                    ->where('kelas_id', $selectedKelasId)
                    ->where('hari', $hariQuery)
                    ->where('tahun_ajaran_id', $tahunAjaran->id)
                    ->where('semester_id', $semester->id)
                    ->orderBy('jam_ke')
                    ->get();
                    
                $scheduledJamIds = $multiSlotJadwal->pluck('jam_belajar_id')->unique();
                if ($scheduledJamIds->isNotEmpty()) {
                    $jamBelajarList = JamBelajar::whereIn('id', $scheduledJamIds)->orderBy('urutan')->get();
                } else {
                    $jamBelajarList = JamBelajar::orderBy('urutan')->get();
                }
                
                if (!$selectedJamBelajarId && $multiSlotJadwal->isNotEmpty()) {
                    $selectedJamBelajarId = $multiSlotJadwal->first()->jam_belajar_id;
                }
            } else {
                $jamBelajarList = JamBelajar::orderBy('urutan')->get();
            }
            
            $guruList = Guru::where('id', $user->guru_id)->get();
            $jadwalList = $jadwalHariIni;
        } elseif ($isSiswaOfficer) {
            $siswa = $user->siswa;
            $kelas = Kelas::find($siswa->kelas_id);
            $kelasList = $kelas ? collect([$kelas]) : collect();
            $selectedKelasId = $kelas->id ?? null;
            $guruList = $kelas && $kelas->waliKelas ? collect([$kelas->waliKelas]) : Guru::orderBy('nama')->get();
            $jamBelajarList = JamBelajar::orderBy('urutan')->get();
            $jadwalList = collect();
        } else {
            // Admin, kepala sekolah, atau guru piket dapat input absensi lintas kelas
            $kelasList = Kelas::orderBy('nama_kelas')->get();
            if ($isGuruPiket && $user->guru_id) {
                $guruList = Guru::where('id', $user->guru_id)->get();
            } else {
                $guruList = Guru::orderBy('nama')->get();
            }
            $jamBelajarList = JamBelajar::orderBy('urutan')->get();
            if ($isGuruPiket && !$selectedJamBelajarId && $jamBelajarList->isNotEmpty()) {
                $selectedJamBelajarId = $jamBelajarList->first()->id;
            }
            $jadwalList = collect();
        }

        return view('absensi.create', compact(
            'kelasList',
            'guruList',
            'jamBelajarList',
            'tahunAjaran',
            'semester',
            'jadwalList',
            'selectedKelasId',
            'selectedJamBelajarId',
            'isQuickAccess',
            'selectedDate',
            'multiSlotJadwal',
            'isGuruPiket'
        ));
    }

    public function generateForm(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Admin dan Kepala Sekolah.');
        }

        $tahunAjaran = TahunAjaran::where('is_active', 1)->first();
        $semester = Semester::where('is_active', 1)->first();

        if (! $tahunAjaran || ! $semester) {
            return redirect()->route('absensi.index')
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $guruList = Guru::orderBy('nama')->get();
        $jamBelajarList = JamBelajar::orderBy('urutan')->get();

        return view('absensi.generate', compact(
            'tahunAjaran',
            'semester',
            'kelasList',
            'guruList',
            'jamBelajarList'
        ));
    }

    public function generateStore(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Admin dan Kepala Sekolah.');
        }

        $tahunAjaran = TahunAjaran::where('is_active', 1)->first();
        $semester = Semester::where('is_active', 1)->first();

        if (! $tahunAjaran || ! $semester) {
            return redirect()->route('absensi.index')
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $validated = $request->validate([
            'mode_generate' => 'nullable|in:per_jam,per_hari',
            'scope' => 'required|in:single,all',
            'kelas_id' => 'nullable|required_if:scope,single|exists:kelas,id',
            'guru_id' => 'required|exists:guru,id',
            'jam_belajar_id' => 'nullable|exists:jam_belajar,id',
            'tanggal' => 'required|date',
            'status_kelas' => 'nullable|string|max:100',
            'jumlah_hadir' => 'nullable|integer|min:0',
            'jumlah_terlambat' => 'nullable|integer|min:0',
            'jumlah_sakit' => 'nullable|integer|min:0',
            'jumlah_izin' => 'nullable|integer|min:0',
            'jumlah_alpa' => 'nullable|integer|min:0',
            'status_sisa' => 'nullable|in:hadir,terlambat,sakit,izin,alpa',
            'overwrite_existing' => 'nullable|boolean',
        ]);

        $statusCounts = [
            'hadir' => (int) ($validated['jumlah_hadir'] ?? 0),
            'terlambat' => (int) ($validated['jumlah_terlambat'] ?? 0),
            'sakit' => (int) ($validated['jumlah_sakit'] ?? 0),
            'izin' => (int) ($validated['jumlah_izin'] ?? 0),
            'alpa' => (int) ($validated['jumlah_alpa'] ?? 0),
        ];

        if (array_sum($statusCounts) <= 0) {
            return back()->withInput()->withErrors([
                'error' => 'Minimal satu jumlah status harus diisi lebih dari 0.',
            ]);
        }

        $targetKelas = Kelas::query()
            ->when(($validated['scope'] ?? 'single') === 'single', function ($query) use ($validated) {
                $query->where('id', $validated['kelas_id']);
            })
            ->orderBy('nama_kelas')
            ->get();

        if ($targetKelas->isEmpty()) {
            return back()->withInput()->withErrors([
                'error' => 'Kelas target tidak ditemukan.',
            ]);
        }

        $overwriteExisting = (bool) ($validated['overwrite_existing'] ?? false);
        $statusSisa = $validated['status_sisa'] ?? 'hadir';
        $modeGenerate = $validated['mode_generate'] ?? 'per_jam';

        if ($modeGenerate === 'per_jam' && empty($validated['jam_belajar_id'])) {
            return back()->withInput()->withErrors([
                'jam_belajar_id' => 'Jam belajar wajib dipilih untuk mode generate per jam mata pelajaran.',
            ]);
        }

        $jamBelajarDefaultId = null;
        if ($modeGenerate === 'per_hari') {
            $jamBelajarDefaultId = JamBelajar::orderBy('urutan')->value('id');
            if (! $jamBelajarDefaultId) {
                return back()->withInput()->withErrors([
                    'error' => 'Tidak ada data jam belajar. Tambahkan jam belajar terlebih dahulu untuk menggunakan generate per hari.',
                ]);
            }
        }

        $createdCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;
        $generatedSiswaCount = 0;
        $kelasTanpaSiswa = [];

        DB::beginTransaction();
        try {
            foreach ($targetKelas as $kelas) {
                $siswaList = Siswa::query()
                    ->where('kelas_id', $kelas->id)
                    ->where('status_aktif', 1)
                    ->orderBy('nama')
                    ->get();

                $totalSiswa = $siswaList->count();
                if ($totalSiswa === 0) {
                    $kelasTanpaSiswa[] = $kelas->nama_kelas;
                    continue;
                }

                $totalInputStatus = array_sum($statusCounts);
                if ($totalInputStatus > $totalSiswa) {
                    throw new \RuntimeException(
                        'Jumlah komposisi status untuk kelas ' . $kelas->nama_kelas . ' melebihi jumlah siswa aktif (' . $totalSiswa . ').'
                    );
                }

                $statusPool = $this->buildGeneratedStatusPool($statusCounts, $totalSiswa, $statusSisa);
                shuffle($statusPool);

                if ($modeGenerate === 'per_hari') {
                    $existingAbsensiPerHari = AbsensiKelas::query()
                        ->where('kelas_id', $kelas->id)
                        ->whereDate('tanggal', $validated['tanggal'])
                        ->where('tahun_ajaran_id', $tahunAjaran->id)
                        ->where('semester_id', $semester->id)
                        ->get();

                    if ($existingAbsensiPerHari->isNotEmpty() && ! $overwriteExisting) {
                        $skippedCount++;
                        continue;
                    }

                    if ($existingAbsensiPerHari->isNotEmpty() && $overwriteExisting) {
                        $existingIds = $existingAbsensiPerHari->pluck('id');
                        AbsensiSiswa::whereIn('absensi_kelas_id', $existingIds)->delete();

                        if (Schema::hasTable('laporan_siswa_guru') && Schema::hasColumn('laporan_siswa_guru', 'absensi_kelas_id')) {
                            DB::table('laporan_siswa_guru')
                                ->whereIn('absensi_kelas_id', $existingIds)
                                ->update(['absensi_kelas_id' => null]);
                        }

                        if (Schema::hasTable('pelanggaran_siswa') && Schema::hasColumn('pelanggaran_siswa', 'absensi_kelas_id')) {
                            DB::table('pelanggaran_siswa')
                                ->whereIn('absensi_kelas_id', $existingIds)
                                ->update(['absensi_kelas_id' => null]);
                        }

                        AbsensiKelas::whereIn('id', $existingIds)->delete();
                        $updatedCount++;
                    }

                    $absensi = AbsensiKelas::create([
                        'kelas_id' => $kelas->id,
                        'guru_id' => $validated['guru_id'],
                        'jam_belajar_id' => $jamBelajarDefaultId,
                        'tanggal' => $validated['tanggal'],
                        'status_kelas' => $validated['status_kelas'] ?? null,
                        'tahun_ajaran_id' => $tahunAjaran->id,
                        'semester_id' => $semester->id,
                    ]);
                    $createdCount++;
                } else {
                    $existingAbsensi = AbsensiKelas::query()
                        ->where('kelas_id', $kelas->id)
                        ->where('jam_belajar_id', $validated['jam_belajar_id'])
                        ->whereDate('tanggal', $validated['tanggal'])
                        ->where('tahun_ajaran_id', $tahunAjaran->id)
                        ->where('semester_id', $semester->id)
                        ->first();

                    if ($existingAbsensi && ! $overwriteExisting) {
                        $skippedCount++;
                        continue;
                    }

                    if ($existingAbsensi) {
                        $existingAbsensi->update([
                            'guru_id' => $validated['guru_id'],
                            'status_kelas' => $validated['status_kelas'] ?? null,
                        ]);

                        AbsensiSiswa::where('absensi_kelas_id', $existingAbsensi->id)->delete();
                        $absensi = $existingAbsensi;
                        $updatedCount++;
                    } else {
                        $absensi = AbsensiKelas::create([
                            'kelas_id' => $kelas->id,
                            'guru_id' => $validated['guru_id'],
                            'jam_belajar_id' => $validated['jam_belajar_id'],
                            'tanggal' => $validated['tanggal'],
                            'status_kelas' => $validated['status_kelas'] ?? null,
                            'tahun_ajaran_id' => $tahunAjaran->id,
                            'semester_id' => $semester->id,
                        ]);
                        $createdCount++;
                    }
                }

                foreach ($siswaList->values() as $index => $siswa) {
                    AbsensiSiswa::create([
                        'absensi_kelas_id' => $absensi->id,
                        'siswa_id' => $siswa->id,
                        'status' => $this->normalizeAttendanceStatus($statusPool[$index] ?? $statusSisa),
                        'keterangan' => null,
                    ]);
                }

                $generatedSiswaCount += $totalSiswa;

                $this->syncAbsensiToAgendaGuru($absensi);
                $this->updateAgendaGuruAttendanceNote($absensi);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->withErrors([
                'error' => 'Gagal generate absensi: ' . $e->getMessage(),
            ]);
        }

        $messageParts = [];
        $messageParts[] = 'Generate absensi selesai.';
        $messageParts[] = 'Mode: ' . ($modeGenerate === 'per_hari' ? 'Per Hari' : 'Per Jam Mata Pelajaran') . '.';
        $messageParts[] = 'Dibuat: ' . $createdCount . ' kelas.';
        if ($updatedCount > 0) {
            $messageParts[] = 'Diupdate (overwrite): ' . $updatedCount . ' kelas.';
        }
        if ($skippedCount > 0) {
            $messageParts[] = 'Dilewati (sudah ada): ' . $skippedCount . ' kelas.';
        }
        $messageParts[] = 'Total data siswa tergenerate: ' . $generatedSiswaCount . '.';
        if (!empty($kelasTanpaSiswa)) {
            $messageParts[] = 'Kelas tanpa siswa aktif: ' . implode(', ', $kelasTanpaSiswa) . '.';
        }

        return redirect()->route('absensi.index')->with('success', implode(' ', $messageParts));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->hasRole('Siswa') && ! $user->hasClassPosition()) {
            return redirect()->route('home')->with('error', 'Akses ditolak. Hanya siswa dengan jabatan kelas yang bisa menginput absensi.');
        }

        $isSiswaOfficer = $user->hasRole('Siswa') && $user->hasClassPosition();
        $isAdminOrKepala = $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $isGuruPiket = $user->hasRole('Guru Piket') || !empty((array) ($user->guru->hari_piket ?? []));
        $isGuruBk = $user->hasRole('Guru BK');

        if ($isGuruPiket) {
            if (!$request->filled('guru_id') && $user->guru_id) {
                $request->merge(['guru_id' => $user->guru_id]);
            }

            if (!$request->filled('jam_belajar_id')) {
                $defaultJamBelajarId = JamBelajar::orderBy('urutan')->value('id');
                if ($defaultJamBelajarId) {
                    $request->merge(['jam_belajar_id' => $defaultJamBelajarId]);
                }
            }
        }

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:guru,id',
            'jam_belajar_id' => 'required|exists:jam_belajar,id',
            'tanggal' => 'required|date',
            'status_kelas' => 'nullable|string|max:100',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'semester_id' => 'required|exists:semester,id',
        ]);

        $absensiSiswa = $request->input('absensi_siswa', []);
        $keteranganSiswa = $request->input('keterangan_siswa', []);

        $hasSelectedStatus = collect($absensiSiswa)->contains(function ($status) {
            return !empty($this->normalizeAttendanceStatus($status));
        });

        if (!$hasSelectedStatus) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Minimal pilih status absensi untuk 1 siswa.']);
        }

        if ($user->guru_id && !$isAdminOrKepala && !$isGuruPiket && !$isGuruBk) {
            if ($validated['guru_id'] != $user->guru_id) {
                return back()->withErrors(['error' => 'Anda hanya dapat menginput absensi untuk jadwal Anda sendiri.']);
            }

            $hariIndonesia = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu'
            ];
            $hariEnglish = Carbon::parse($validated['tanggal'])->format('l');
            $hariQuery = $hariIndonesia[$hariEnglish] ?? $hariEnglish;

            $hasSchedule = JadwalKbm::where('guru_id', $user->guru_id)
                ->where('kelas_id', $validated['kelas_id'])
                ->where('hari', $hariQuery)
                ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
                ->where('semester_id', $validated['semester_id'])
                ->exists();

            if (!$hasSchedule) {
                return back()->withErrors(['error' => 'Anda tidak memiliki jadwal mengajar di kelas ini pada hari tersebut.']);
            }
        }

        if ($isGuruBk && Schema::hasTable('kelas') && Schema::hasColumn('kelas', 'guru_bk_id')) {
            $isKelasBinaan = Kelas::where('id', $validated['kelas_id'])
                ->where('guru_bk_id', $user->guru_id)
                ->exists();
            if (! $isKelasBinaan) {
                return back()->withErrors(['error' => 'Kelas ini bukan kelas binaan Anda.']);
            }
        }

        if ($isSiswaOfficer) {
            $siswa = $user->siswa;
            if (! $siswa || $validated['kelas_id'] != $siswa->kelas_id) {
                return back()->withErrors(['error' => 'Anda hanya dapat menginput absensi untuk kelas Anda.']);
            }

            $kelas = Kelas::find($siswa->kelas_id);
            if ($kelas && !$request->filled('guru_id') && $kelas->wali_kelas_id) {
                $validated['guru_id'] = $kelas->wali_kelas_id;
            }

            if (empty($validated['guru_id'])) {
                return back()->withErrors(['error' => 'Guru kelas belum ditentukan. Hubungi admin.']);
            }
        }

        try {
            DB::beginTransaction();

            $hariIndonesia = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu'
            ];
            $hariEnglish = Carbon::parse($validated['tanggal'])->format('l');
            $hariQuery = $hariIndonesia[$hariEnglish] ?? $hariEnglish;

            if ($isAdminOrKepala || $isGuruPiket || $isGuruBk) {
                $targetJamIds = collect([$validated['jam_belajar_id']]);
            } elseif ($user->guru_id) {
                $jadwalJamIds = JadwalKbm::where('guru_id', $validated['guru_id'])
                    ->where('kelas_id', $validated['kelas_id'])
                    ->where('hari', $hariQuery)
                    ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
                    ->where('semester_id', $validated['semester_id'])
                    ->pluck('jam_belajar_id')
                    ->unique();

                $targetJamIds = $jadwalJamIds
                    ->push($validated['jam_belajar_id'])
                    ->unique()
                    ->values();
            } else {
                $targetJamIds = collect([$validated['jam_belajar_id']]);
            }

            $createdAbsensi = [];
            $skippedJamIds = [];
            $firstExistingAbsensi = null;
            $savedAbsensiSiswaCount = 0;

            foreach ($targetJamIds as $jamId) {
                $existing = AbsensiKelas::where('kelas_id', $validated['kelas_id'])
                    ->where('guru_id', $validated['guru_id'])
                    ->where('jam_belajar_id', $jamId)
                    ->whereDate('tanggal', $validated['tanggal'])
                    ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
                    ->where('semester_id', $validated['semester_id'])
                    ->first();

                if ($existing) {
                    $skippedJamIds[] = $jamId;
                    $firstExistingAbsensi = $firstExistingAbsensi ?: $existing;
                    continue;
                }

                $absensi = AbsensiKelas::create(array_merge($validated, [
                    'jam_belajar_id' => $jamId,
                ]));

                foreach ($absensiSiswa as $siswaId => $status) {
                    $normalizedStatus = $this->normalizeAttendanceStatus($status);
                    if (!empty($normalizedStatus)) {
                        AbsensiSiswa::create([
                            'absensi_kelas_id' => $absensi->id,
                            'siswa_id' => $siswaId,
                            'status' => $normalizedStatus,
                            'keterangan' => $keteranganSiswa[$siswaId] ?? null,
                        ]);
                        $savedAbsensiSiswaCount++;
                    }
                }

                $this->syncAbsensiToAgendaGuru($absensi);
                $this->updateAgendaGuruAttendanceNote($absensi);

                $createdAbsensi[] = $absensi;
            }

            DB::commit();

            $jamBelajarLabels = JamBelajar::whereIn('id', $targetJamIds)
                ->orderBy('urutan')
                ->get()
                ->map(function ($jam) {
                    return 'Jam ke-' . $jam->urutan;
                })->implode(', ');

            $skippedLabels = JamBelajar::whereIn('id', $skippedJamIds)
                ->orderBy('urutan')
                ->get()
                ->map(function ($jam) {
                    return 'Jam ke-' . $jam->urutan;
                })->implode(', ');

            $successMessage = !empty($createdAbsensi)
                ? 'Absensi kelas berhasil disimpan untuk ' . $savedAbsensiSiswaCount . ' siswa.'
                : 'Absensi sudah tersedia untuk jadwal ini.';
            if ($targetJamIds->count() > 1) {
                $successMessage .= ' Diterapkan ke: ' . $jamBelajarLabels . '.';
            }
            if (!empty($skippedJamIds)) {
                $successMessage .= ' Melewati jam yang sudah terisi: ' . $skippedLabels . '.';
            }

            $redirectAbsensiId = !empty($createdAbsensi)
                ? $createdAbsensi[0]->id
                : ($firstExistingAbsensi->id ?? null);

            if (!$redirectAbsensiId) {
                return redirect()->route('absensi.index')->with('success', $successMessage);
            }

            return redirect()->route('absensi.show', $redirectAbsensiId)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan absensi: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $absensi = AbsensiKelas::with(['kelas', 'guru', 'jamBelajar', 'tahunAjaran', 'semester', 'absensiSiswa.siswa'])
            ->findOrFail($id);

        return view('absensi.show', compact('absensi'));
    }

    public function edit($id)
    {
        $absensi = AbsensiKelas::findOrFail($id);
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $guruList = Guru::orderBy('nama')->get();
        $jamBelajarList = JamBelajar::orderBy('urutan')->get();
        $tahunAjaranList = TahunAjaran::orderBy('nama_tahun')->get();
        $semesterList = Semester::all();

        return view('absensi.edit', compact('absensi', 'kelasList', 'guruList', 'jamBelajarList', 'tahunAjaranList', 'semesterList'));
    }

    public function update(Request $request, $id)
    {
        $absensi = AbsensiKelas::findOrFail($id);

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:guru,id',
            'jam_belajar_id' => 'required|exists:jam_belajar,id',
            'tanggal' => 'required|date',
            'status_kelas' => 'nullable|string|max:100',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'semester_id' => 'required|exists:semester,id',
        ]);

        $absensi->update($validated);
        
        // Update attendance note in agenda guru
        $this->updateAgendaGuruAttendanceNote($absensi);

        return redirect()->route('absensi.show', $absensi->id)
            ->with('success', 'Absensi kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $absensi = AbsensiKelas::findOrFail($id);
        $absensi->delete();

        return redirect()->route('absensi.index')
            ->with('success', 'Absensi kelas berhasil dihapus.');
    }

    public function destroyByDate(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Admin dan Kepala Sekolah.');
        }

        $validated = $request->validate([
            'tanggal_hapus' => 'required|date',
        ]);

        $tanggal = $validated['tanggal_hapus'];

        $absensiIds = AbsensiKelas::query()
            ->whereDate('tanggal', $tanggal)
            ->pluck('id');

        if ($absensiIds->isEmpty()) {
            return redirect()->route('absensi.index', ['tanggal' => $tanggal])
                ->with('warning', 'Tidak ada data absensi pada tanggal tersebut.');
        }

        try {
            DB::beginTransaction();

            $deletedSiswa = AbsensiSiswa::whereIn('absensi_kelas_id', $absensiIds)->count();
            AbsensiSiswa::whereIn('absensi_kelas_id', $absensiIds)->delete();

            if (Schema::hasTable('laporan_siswa_guru') && Schema::hasColumn('laporan_siswa_guru', 'absensi_kelas_id')) {
                DB::table('laporan_siswa_guru')
                    ->whereIn('absensi_kelas_id', $absensiIds)
                    ->update(['absensi_kelas_id' => null]);
            }

            if (Schema::hasTable('pelanggaran_siswa') && Schema::hasColumn('pelanggaran_siswa', 'absensi_kelas_id')) {
                DB::table('pelanggaran_siswa')
                    ->whereIn('absensi_kelas_id', $absensiIds)
                    ->update(['absensi_kelas_id' => null]);
            }

            $deletedKelas = AbsensiKelas::whereIn('id', $absensiIds)->delete();

            DB::commit();

            return redirect()->route('absensi.index', ['tanggal' => $tanggal])
                ->with('success', 'Berhasil menghapus ' . $deletedKelas . ' data absensi kelas dan ' . $deletedSiswa . ' data absensi siswa pada tanggal ' . Carbon::parse($tanggal)->format('d/m/Y') . '.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->route('absensi.index', ['tanggal' => $tanggal])
                ->withErrors(['error' => 'Gagal menghapus data absensi berdasarkan tanggal: ' . $e->getMessage()]);
        }
    }

    public function getSiswa(Request $request)
    {
        try {
            $kelasId = $request->get('kelas_id');
            
            if (!$kelasId) {
                return response()->json([
                    'siswa' => [],
                    'error' => 'Kelas ID tidak ditemukan'
                ], 400);
            }

            $siswa = \App\Models\Siswa::where('kelas_id', $kelasId)
                ->where('status_aktif', 1)
                ->orderBy('nama')
                ->select('id', 'nis', 'nisn', 'nama', 'jenis_kelamin')
                ->get();

            return response()->json([
                'siswa' => $siswa,
                'count' => $siswa->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'siswa' => [],
                'error' => $e->getMessage(),
                'debug' => config('app.debug') ? $e->getTrace() : null
            ], 500);
        }
    }

    public function storeLaporanSiswa(Request $request, $absensiId)
    {
        $user = auth()->user();

        if (! $user || empty($user->guru_id)) {
            return redirect()->back()->withErrors(['error' => 'Hanya akun guru yang dapat mengirim laporan.']);
        }

        $absensi = AbsensiKelas::with('kelas')->findOrFail($absensiId);

        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'deskripsi_permasalahan' => 'required|string|min:5',
        ]);

        $siswa = DB::table('siswa')
            ->where('id', $validated['siswa_id'])
            ->where('kelas_id', $absensi->kelas_id)
            ->first();

        if (! $siswa) {
            return redirect()->back()->withErrors(['error' => 'Siswa tidak berada di kelas absensi ini.']);
        }

        $waliKelasId = $absensi->kelas->wali_kelas_id ?? null;
        $guruBkId = $absensi->kelas->guru_bk_id ?? null;

        LaporanSiswaGuru::create([
            'absensi_kelas_id' => $absensi->id,
            'kelas_id' => $absensi->kelas_id,
            'siswa_id' => $validated['siswa_id'],
            'guru_pelapor_id' => $user->guru_id,
            'wali_kelas_id' => $waliKelasId,
            'guru_bk_id' => $guruBkId,
            'deskripsi_permasalahan' => $validated['deskripsi_permasalahan'],
        ]);

        return redirect()->route('absensi.show', $absensi->id)
            ->with('success', 'Laporan permasalahan siswa berhasil dikirim ke Wali Kelas dan Guru BK.');
    }

    /**
     * Sync attendance to agenda guru
     * Mencatat bahwa guru sudah melakukan absensi di agenda guru
     */
    private function syncAbsensiToAgendaGuru(AbsensiKelas $absensi)
    {
        // Cari atau buat agenda guru dengan kriteria yang sama
        $agendaGuru = AgendaGuru::where('guru_id', $absensi->guru_id)
            ->where('jam_belajar_id', $absensi->jam_belajar_id)
            ->where('tanggal', $absensi->tanggal)
            ->where('tahun_ajaran_id', $absensi->tahun_ajaran_id)
            ->where('semester_id', $absensi->semester_id)
            ->first();

        if (!$agendaGuru) {
            // Create agenda guru if not exists
            $kelas = DB::table('kelas')->find($absensi->kelas_id);
            $kegiatan = $kelas ? $kelas->nama_kelas . ' - Absensi' : 'Absensi';
            
            AgendaGuru::create([
                'guru_id' => $absensi->guru_id,
                'jam_belajar_id' => $absensi->jam_belajar_id,
                'tanggal' => $absensi->tanggal,
                'kegiatan' => $kegiatan,
                'tahun_ajaran_id' => $absensi->tahun_ajaran_id,
                'semester_id' => $absensi->semester_id,
            ]);
        }
    }

    private function getGuruBkMonitoringData(int $guruId, string $selectedTanggal)
    {
        return DB::table('absensi_siswa')
            ->join('absensi_kelas', 'absensi_kelas.id', '=', 'absensi_siswa.absensi_kelas_id')
            ->join('siswa', 'siswa.id', '=', 'absensi_siswa.siswa_id')
            ->join('kelas', 'kelas.id', '=', 'absensi_kelas.kelas_id')
            ->leftJoin('guru', 'guru.id', '=', 'absensi_kelas.guru_id')
            ->where('kelas.guru_bk_id', $guruId)
            ->whereDate('absensi_kelas.tanggal', $selectedTanggal)
            ->whereIn(DB::raw('LOWER(absensi_siswa.status)'), ['terlambat', 'telat', 'alpa', 'alpha', 'alfa', 'absen'])
            ->select(
                'absensi_kelas.id as absensi_kelas_id',
                'absensi_kelas.tanggal',
                'kelas.id as kelas_id',
                'kelas.nama_kelas',
                'siswa.id as siswa_id',
                'siswa.nama as nama_siswa',
                'absensi_siswa.status',
                'absensi_siswa.keterangan',
                'guru.nama as nama_guru'
            )
            ->orderBy('kelas.nama_kelas')
            ->orderBy('siswa.nama')
            ->get();
    }

    private function getDailyStudentReportRows(string $selectedTanggal, $kelasId, $tahun, $semester)
    {
        $dailySubQuery = DB::table('absensi_siswa as abs_s')
            ->join('absensi_kelas as abs_k', 'abs_s.absensi_kelas_id', '=', 'abs_k.id')
            ->leftJoin('siswa as s', 'abs_s.siswa_id', '=', 's.id')
            ->leftJoin('kelas as k', 'abs_k.kelas_id', '=', 'k.id')
            ->leftJoin('guru as g', 'abs_k.guru_id', '=', 'g.id')
            ->whereDate('abs_k.tanggal', $selectedTanggal)
            ->when($kelasId, function ($query) use ($kelasId) {
                $query->where('abs_k.kelas_id', $kelasId);
            })
            ->when($tahun, function ($query) use ($tahun) {
                $query->where('abs_k.tahun_ajaran_id', $tahun->id);
            })
            ->when($semester, function ($query) use ($semester) {
                $query->where('abs_k.semester_id', $semester->id);
            })
            ->select(
                'abs_k.kelas_id',
                'abs_s.siswa_id',
                DB::raw('DATE(abs_k.tanggal) as tanggal'),
                DB::raw("COALESCE(k.nama_kelas, '-') as nama_kelas"),
                DB::raw("COALESCE(s.nama, '-') as nama_siswa"),
                DB::raw("COALESCE(s.nis, '-') as nis"),
                DB::raw("COALESCE(s.nisn, '-') as nisn"),
                DB::raw("GROUP_CONCAT(DISTINCT COALESCE(g.nama, '-') ORDER BY g.nama SEPARATOR ', ') as nama_guru"),
                DB::raw("MAX(NULLIF(abs_s.keterangan, '')) as keterangan"),
                DB::raw("MAX(CASE
                    WHEN LOWER(abs_s.status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 5
                    WHEN LOWER(abs_s.status) = 'sakit' THEN 4
                    WHEN LOWER(abs_s.status) IN ('izin','ijin') THEN 3
                    WHEN LOWER(abs_s.status) IN ('terlambat','telat') THEN 2
                    WHEN LOWER(abs_s.status) = 'hadir' THEN 1
                    ELSE 0
                END) as status_rank")
            )
            ->groupBy(
                'abs_k.kelas_id',
                'abs_s.siswa_id',
                DB::raw('DATE(abs_k.tanggal)'),
                'k.nama_kelas',
                's.nama',
                's.nis',
                's.nisn'
            );

        return DB::query()
            ->fromSub($dailySubQuery, 'daily_siswa')
            ->select(
                'daily_siswa.tanggal',
                'daily_siswa.kelas_id',
                'daily_siswa.nama_kelas',
                'daily_siswa.nama_siswa',
                'daily_siswa.nis',
                'daily_siswa.nisn',
                DB::raw("COALESCE(daily_siswa.nama_guru, '-') as nama_guru"),
                DB::raw("CASE
                    WHEN daily_siswa.status_rank = 5 THEN 'Absen'
                    WHEN daily_siswa.status_rank = 4 THEN 'Sakit'
                    WHEN daily_siswa.status_rank = 3 THEN 'Izin'
                    WHEN daily_siswa.status_rank = 2 THEN 'Terlambat'
                    WHEN daily_siswa.status_rank = 1 THEN 'Hadir'
                    ELSE '-'
                END as status"),
                DB::raw("COALESCE(daily_siswa.keterangan, '-') as keterangan")
            )
            ->orderBy('daily_siswa.nama_kelas')
            ->orderBy('daily_siswa.nama_siswa')
            ->get();
    }

    private function getDailyAttendanceSummaryPerClass($kelasIds, int $tahunAjaranId, int $semesterId, string $selectedTanggal)
    {
        $dailySubQuery = DB::table('absensi_siswa as abs_s')
            ->join('absensi_kelas as abs_k', 'abs_k.id', '=', 'abs_s.absensi_kelas_id')
            ->whereIn('abs_k.kelas_id', $kelasIds)
            ->whereDate('abs_k.tanggal', $selectedTanggal)
            ->where('abs_k.tahun_ajaran_id', $tahunAjaranId)
            ->where('abs_k.semester_id', $semesterId)
            ->select(
                'abs_k.kelas_id',
                'abs_s.siswa_id',
                DB::raw('DATE(abs_k.tanggal) as tanggal'),
                DB::raw("MAX(CASE
                    WHEN LOWER(abs_s.status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 5
                    WHEN LOWER(abs_s.status) = 'sakit' THEN 4
                    WHEN LOWER(abs_s.status) IN ('izin','ijin') THEN 3
                    WHEN LOWER(abs_s.status) IN ('terlambat','telat') THEN 2
                    WHEN LOWER(abs_s.status) = 'hadir' THEN 1
                    ELSE 0
                END) as status_rank")
            )
            ->groupBy('abs_k.kelas_id', 'abs_s.siswa_id', DB::raw('DATE(abs_k.tanggal)'));

        return DB::query()
            ->fromSub($dailySubQuery, 'daily_siswa')
            ->select(
                'daily_siswa.kelas_id',
                DB::raw('COUNT(*) as total_siswa_harian'),
                DB::raw('SUM(CASE WHEN daily_siswa.status_rank = 1 THEN 1 ELSE 0 END) as total_hadir'),
                DB::raw('SUM(CASE WHEN daily_siswa.status_rank = 2 THEN 1 ELSE 0 END) as total_terlambat'),
                DB::raw('SUM(CASE WHEN daily_siswa.status_rank = 3 THEN 1 ELSE 0 END) as total_izin'),
                DB::raw('SUM(CASE WHEN daily_siswa.status_rank = 4 THEN 1 ELSE 0 END) as total_sakit'),
                DB::raw('SUM(CASE WHEN daily_siswa.status_rank = 5 THEN 1 ELSE 0 END) as total_alpha')
            )
            ->groupBy('daily_siswa.kelas_id')
            ->get();
    }

    /**
     * Update attendance status in agenda guru
     * Jika ada perubahan status absensi, update catatan di agenda guru
     */
    private function updateAgendaGuruAttendanceNote(AbsensiKelas $absensi)
    {
        $absensiSummary = \App\Models\AbsensiSiswa::where('absensi_kelas_id', $absensi->id)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $notes = [];
        foreach ($absensiSummary as $summary) {
            $notes[] = $summary->status . ': ' . $summary->total;
        }
        
        if (!empty($notes)) {
            $agendaGuru = AgendaGuru::where('guru_id', $absensi->guru_id)
                ->where('jam_belajar_id', $absensi->jam_belajar_id)
                ->where('tanggal', $absensi->tanggal)
                ->where('tahun_ajaran_id', $absensi->tahun_ajaran_id)
                ->where('semester_id', $absensi->semester_id)
                ->first();

            if ($agendaGuru) {
                // Update kegiatan to include attendance summary if not already there
                $kelas = DB::table('kelas')->find($absensi->kelas_id);
                $summary = implode(', ', $notes);
                $kegiatan = $kelas ? $kelas->nama_kelas : '';
                $kegiatan .= ' | Absensi: ' . $summary;
                
                // Only update if summary not already in kegiatan
                if (strpos($agendaGuru->kegiatan, 'Absensi:') === false) {
                    $agendaGuru->kegiatan = $agendaGuru->kegiatan . ' (' . $summary . ')';
                    $agendaGuru->save();
                }
            }
        }
    }

    /**
     * Normalize status from form/input to canonical DB values.
     */
    private function normalizeAttendanceStatus($status)
    {
        if ($status === null) {
            return null;
        }

        $normalized = strtolower(trim((string) $status));

        return match ($normalized) {
            'hadir' => 'Hadir',
            'terlambat', 'telat' => 'Terlambat',
            'sakit' => 'Sakit',
            'izin' => 'Izin',
            'alpa', 'alpha', 'absen' => 'Absen',
            default => null,
        };
    }

    private function buildGeneratedStatusPool(array $statusCounts, int $totalSiswa, string $statusSisa): array
    {
        $pool = [];

        foreach ($statusCounts as $status => $count) {
            $safeCount = max((int) $count, 0);
            for ($i = 0; $i < $safeCount; $i++) {
                $pool[] = $status;
            }
        }

        $remaining = $totalSiswa - count($pool);
        for ($i = 0; $i < $remaining; $i++) {
            $pool[] = $statusSisa;
        }

        return $pool;
    }
}
