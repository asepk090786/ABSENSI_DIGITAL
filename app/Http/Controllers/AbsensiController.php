<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\AbsensiKelas;
use App\Models\AgendaGuru;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\JamBelajar;
use App\Models\Siswa;
use App\Models\AbsensiSiswa;
use App\Models\AbsensiGuru;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\JadwalKbm;
use App\Models\AttendanceVerificationCode;
use App\Models\LaporanSiswaGuru;
use App\Exports\AbsensiBkMonitoringExport;
use App\Exports\AbsensiLaporanSiswaHarianExport;
use App\Events\StudentVerified;
use App\Exports\AbsensiLaporanSiswaRangeExport;
use App\Services\SettingsManager;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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

        // Additional filters
        $filterKelasId = $request->get('kelas_id');
        $filterGuruId = $request->get('guru_id');
        $filterQuery = $request->get('q');

        if (! $tahun || ! $semester) {
            $items = collect();
            return view('absensi.index', compact('items'))
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $user = auth()->user();
        $isAdminOrKepala = $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $mode = $request->get('mode');
        $bkAttendanceStats = [];
        $hariPiketArr = (array) ($user->guru->hari_piket ?? []);
        $todayEng = \Carbon\Carbon::now()->format('l');
        $map = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $todayIndo = $map[$todayEng] ?? null;
        $isGuruPiket = in_array($todayIndo, $hariPiketArr, true) && $mode !== 'academic';
        $isGuruBk = $user->hasRole('Guru BK');
        $isWaliKelas = $user->hasRole('Wali Kelas');
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
        if ($user->guru_id && !$isAdminOrKepala && !$isGuruPiket && !$isGuruBk && !$isWaliKelas) {
            $query->where('guru_id', $user->guru_id);
        }

        // If user is a student, restrict to their kelas only and default filter
        $isSiswaOfficer = $user->hasRole('Siswa') && $user->hasClassPosition();
        if ($user->hasRole('Siswa')) {
            $siswa = $user->siswa;
            if ($siswa && !empty($siswa->kelas_id)) {
                $query->where('kelas_id', $siswa->kelas_id);
                $kelasQuickAccess = Kelas::where('id', $siswa->kelas_id)->get();
                // default the filter to the siswa's kelas so the select shows correctly
                $filterKelasId = $siswa->kelas_id;
            } else {
                // No kelas assigned, return empty
                $query->whereRaw('1 = 0');
                $kelasQuickAccess = collect();
            }
        }

        // Apply filters from UI (kelas, guru, search)
        if (!empty($filterKelasId)) {
            $query->where('kelas_id', $filterKelasId);
        }

        if (!empty($filterGuruId)) {
            $query->where('guru_id', $filterGuruId);
        }

        if (!empty($filterQuery)) {
            $query->where(function ($q) use ($filterQuery) {
                $q->whereHas('kelas', function ($qq) use ($filterQuery) {
                    $qq->where('nama_kelas', 'like', '%' . $filterQuery . '%');
                })->orWhereHas('guru', function ($qq) use ($filterQuery) {
                    $qq->where('nama', 'like', '%' . $filterQuery . '%');
                });
            });
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
            $bkAttendanceStats = $this->buildBkAttendanceStats($siswaPerluPerhatian);
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
                        'total_alpa' => (int) ($summary->total_alpha ?? $summary->total_alpa ?? 0),
                        // backward compatibility alias
                        'total_alpha' => (int) ($summary->total_alpha ?? $summary->total_alpa ?? 0),
                        'total_data_siswa' => (int) ($totalSiswaAktifPerKelas[$kelas->id] ?? 0),
                    ];
                })->values();
            }
        }
        
        // Provide kelas/guru lists for filter selects
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $guruList = Guru::orderBy('nama')->get();

        $canPrintExport = $isAdminOrKepala || $this->isGuruPiketUser(auth()->user()) || $isSiswaOfficer;

        return view('absensi.index', compact('items', 'kelasQuickAccess', 'rekapPerKelas', 'selectedTanggal', 'isGuruPiket', 'isGuruBk', 'siswaPerluPerhatian', 'kelasList', 'guruList', 'filterKelasId', 'filterGuruId', 'filterQuery', 'isSiswaOfficer', 'canPrintExport', 'bkAttendanceStats'));
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
        if (! $user) {
            abort(403, 'Akses ditolak. Silakan login.');
        }

        $selectedTanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $kelasId = $request->get('kelas_id');
        $guruId = $request->get('guru_id');
        $period = $request->get('period', 'daily'); // daily, weekly, monthly

        // Access is allowed for Admin/Kepala Sekolah, Guru Piket role, guru with piket schedule on selected date, and siswa officer.
        $canAdminPrint = $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $isGuruPiketRole = $user->hasRole('Guru Piket');
        $isGuruPiketUser = $this->isGuruPiketUser($user);
        $isSiswaOfficer = $user->hasRole('Siswa') && $user->hasClassPosition();

        if (! $canAdminPrint && ! $isGuruPiketRole && ! $isGuruPiketUser && ! $isSiswaOfficer) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Admin, Kepala Sekolah, Guru Piket atau siswa dengan jabatan kelas.');
        }

        // If the user is a siswa officer, force kelasId to their kelas
        if ($isSiswaOfficer) {
            $kelasId = $user->siswa->kelas_id ?? $kelasId;
        }

        // Compute date range based on period or explicit range passed from UI
        $rangeStart = $request->get('range_start');
        $rangeEnd = $request->get('range_end');

        if ($rangeStart && $rangeEnd) {
            $startDate = Carbon::parse($rangeStart)->format('Y-m-d');
            $endDate = Carbon::parse($rangeEnd)->format('Y-m-d');
        } else {
            $startDate = $selectedTanggal;
            $endDate = $selectedTanggal;
            if ($period === 'weekly') {
                $dt = Carbon::parse($selectedTanggal);
                $startDate = $dt->startOfWeek()->format('Y-m-d');
                $endDate = $dt->endOfWeek()->format('Y-m-d');
            } elseif ($period === 'monthly') {
                $dt = Carbon::parse($selectedTanggal);
                $startDate = $dt->startOfMonth()->format('Y-m-d');
                $endDate = $dt->endOfMonth()->format('Y-m-d');
            }
        }
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        $jamColumns = [];

        // Check cache presence for this report request and log it (helps verify Redis vs DB)
        try {
            $cacheStore = Cache::getStore();
            $checkKeys = [];
            if ($period === 'daily') {
                $checkKeys[] = $this->buildStudentReportCacheKey('daily', [
                    'tanggal' => $selectedTanggal,
                    'kelas_id' => $kelasId,
                    'guru_id' => $guruId,
                    'tahun_id' => $tahun?->id,
                    'semester_id' => $semester?->id,
                ]);
                if ($kelasId) {
                    $checkKeys[] = $this->buildStudentReportCacheKey('daily_jam', [
                        'tanggal' => $selectedTanggal,
                        'kelas_id' => $kelasId,
                        'guru_id' => $guruId,
                        'tahun_id' => $tahun?->id,
                        'semester_id' => $semester?->id,
                    ]);
                }
            } else {
                $checkKeys[] = $this->buildStudentReportCacheKey('range', [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'kelas_id' => $kelasId,
                    'guru_id' => $guruId,
                    'tahun_id' => $tahun?->id,
                    'semester_id' => $semester?->id,
                ]);
            }

            foreach ($checkKeys as $ck) {
                $exists = false;
                if (method_exists($cacheStore, 'supportsTags') && $cacheStore->supportsTags()) {
                    try {
                        $exists = Cache::tags(['absensi_laporan_siswa'])->has($ck);
                    } catch (\Throwable $e) {
                        $exists = Cache::has($ck);
                    }
                } else {
                    $exists = Cache::has($ck);
                }

                Log::info('absensi.laporan.cache_check', ['key' => $ck, 'cache_hit' => $exists, 'period' => $period, 'start' => $startDate, 'end' => $endDate]);
            }
        } catch (\Throwable $e) {
            Log::warning('absensi.laporan.cache_check_failed', ['error' => $e->getMessage()]);
        }
        if ($period === 'daily') {
            // build per-jam matrix if kelas specified
            $perJam = $this->getDailyStudentReportRowsPerJam($selectedTanggal, $kelasId, $tahun, $semester, $guruId);
            if (is_array($perJam) && isset($perJam['rows'])) {
                $laporanRows = $perJam['rows'];
                $jamColumns = $perJam['jamColumns'];
            } else {
                $laporanRows = $this->getDailyStudentReportRows($selectedTanggal, $kelasId, $tahun, $semester, $guruId);
            }
        } else {
            $laporanRows = $this->getRangeStudentReportRows($startDate, $endDate, $kelasId, $tahun, $semester, $guruId);
        }

        // Support two shapes of $laporanRows:
        // - daily rows: each row has 'status'
        // - aggregated range rows: each row has 'hadir_count','terlambat_count', etc.
        if ($laporanRows->isEmpty()) {
            $summary = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'total' => 0];
        } else {
            $first = $laporanRows->first();
            if (isset($first->status)) {
                $summary = [
                    'hadir' => $laporanRows->filter(fn ($row) => strtolower((string) $row->status) === 'hadir')->count(),
                    'terlambat' => $laporanRows->filter(fn ($row) => in_array(strtolower((string) $row->status), ['terlambat', 'telat'], true))->count(),
                    'izin' => $laporanRows->filter(fn ($row) => in_array(strtolower((string) $row->status), ['izin', 'ijin'], true))->count(),
                    'sakit' => $laporanRows->filter(fn ($row) => strtolower((string) $row->status) === 'sakit')->count(),
                    'alpa' => $laporanRows->filter(fn ($row) => in_array(strtolower((string) $row->status), ['alpha', 'alpa', 'alfa', 'absen', 'tidak_hadir'], true))->count(),
                    'total' => $laporanRows->count(),
                ];
            } elseif (isset($first->hadir_count) || isset($first->hadir)) {
                // aggregated per-student counts
                $summary = [
                    'hadir' => (int) $laporanRows->sum(fn ($r) => (int) ($r->hadir_count ?? $r->hadir ?? 0)),
                    'terlambat' => (int) $laporanRows->sum(fn ($r) => (int) ($r->terlambat_count ?? $r->terlambat ?? 0)),
                    'izin' => (int) $laporanRows->sum(fn ($r) => (int) ($r->izin_count ?? $r->izin ?? 0)),
                    'sakit' => (int) $laporanRows->sum(fn ($r) => (int) ($r->sakit_count ?? $r->sakit ?? 0)),
                    'alpa' => (int) $laporanRows->sum(fn ($r) => (int) ($r->alpa_count ?? $r->alfa_count ?? $r->alfa ?? 0)),
                    'total' => (int) $laporanRows->sum(fn ($r) => (int) ($r->total_days ?? 0)),
                ];
            } else {
                // Unknown shape, attempt best-effort by counting any non-empty properties
                $summary = ['hadir' => 0, 'terlambat' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0, 'total' => $laporanRows->count()];
            }
        }

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
            'sekolah',
            'period',
            'startDate',
            'endDate',
            'jamColumns'
        ));
        $pdf->setPaper('a4', 'portrait');

        $fileLabel = $selectedTanggal;
        if (!empty($period) && $period !== 'daily') {
            $fileLabel = $startDate . '_to_' . $endDate;
        }

        return $pdf->stream('Laporan-Kehadiran-Siswa-' . $fileLabel . '.pdf');
    }

    public function exportLaporanSiswa(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, 'Akses ditolak. Silakan login.');
        }

        $selectedTanggal = $request->get('tanggal', Carbon::today()->format('Y-m-d'));
        $kelasId = $request->get('kelas_id');
        $guruId = $request->get('guru_id');
        $period = $request->get('period', 'daily');
        $rangeStart = $request->get('range_start');
        $rangeEnd = $request->get('range_end');
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        $canAdminExport = $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $isGuruPiketRole = $user->hasRole('Guru Piket');
        $isGuruPiketUser = $this->isGuruPiketUser($user);

        $isGuruBk = $user->hasRole('Guru BK');

        // Restrict exports for normal teachers: they can only export for classes they teach.
        if (! $canAdminExport && ! $isGuruBk) {
            // If a specific class is provided, ensure the teacher actually teaches it
            if (! empty($selectedTanggal) && empty($request->get('kelas_id')) && ! $request->has('kelas_id')) {
                // No kelas selected: require teacher to choose a kelas when not admin/gbk
                abort(403, 'Pilih kelas yang Anda ampu untuk melakukan rekap.');
            }
            $requestedKelasId = $request->get('kelas_id');
            if ($requestedKelasId) {
                $teaches = JadwalKbm::where('guru_id', $user->guru_id)
                    ->where('kelas_id', $requestedKelasId)
                    ->where('tahun_ajaran_id', $tahun?->id)
                    ->where('semester_id', $semester?->id)
                    ->exists();
                if (! $teaches) {
                    abort(403, 'Akses ditolak. Anda hanya bisa merekap untuk kelas yang Anda ampu.');
                }
            }
        }

        if (! $canAdminExport && ! $isGuruPiketRole && ! $isGuruPiketUser) {
            abort(403, 'Akses ditolak. Fitur ini hanya untuk Admin, Kepala Sekolah, Guru Piket atau guru piket.');
        }

        if ($rangeStart && $rangeEnd) {
            $startDate = Carbon::parse($rangeStart)->format('Y-m-d');
            $endDate = Carbon::parse($rangeEnd)->format('Y-m-d');
        } else {
            $startDate = $selectedTanggal;
            $endDate = $selectedTanggal;
            if ($period === 'weekly') {
                $dt = Carbon::parse($selectedTanggal);
                $startDate = $dt->startOfWeek()->format('Y-m-d');
                $endDate = $dt->endOfWeek()->format('Y-m-d');
            } elseif ($period === 'monthly') {
                $dt = Carbon::parse($selectedTanggal);
                $startDate = $dt->startOfMonth()->format('Y-m-d');
                $endDate = $dt->endOfMonth()->format('Y-m-d');
            }
        }

        // If kelas_id is not provided and caller is admin or guru BK, produce one file per kelas into a zip
        $canCreatePerKelasZip = $canAdminExport || $isGuruBk;

        if ($period === 'daily') {
            $laporanRows = $this->getDailyStudentReportRows($selectedTanggal, $kelasId, $tahun, $semester, $guruId);
            $laporanRows = $laporanRows->sortBy('nama_kelas')->values();

            if ($canCreatePerKelasZip && empty($kelasId)) {
                // group by kelas and create separate files
                $grouped = $laporanRows->groupBy('nama_kelas');
                $tempFiles = [];
                $tmpDir = storage_path('app/temp_exports_' . time());
                if (! file_exists($tmpDir)) mkdir($tmpDir, 0777, true);

                foreach ($grouped as $kelasName => $rows) {
                    $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $kelasName ?: 'kelas');
                    $fileName = "absensi_harian_{$safeName}_" . Carbon::parse($selectedTanggal)->format('Ymd') . '.xlsx';
                    $filePath = $tmpDir . DIRECTORY_SEPARATOR . $fileName;
                    Excel::store(new AbsensiLaporanSiswaHarianExport($rows->values()), basename($tmpDir) . '/' . $fileName);
                    $tempFiles[] = storage_path('app/' . basename($tmpDir) . '/' . $fileName);
                }

                // create zip
                $zipName = 'Laporan-Absensi-Harian-PerKelas-' . Carbon::parse($selectedTanggal)->format('Ymd') . '.zip';
                $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $zipName;
                $zip = new \ZipArchive();
                if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
                        foreach ($tempFiles as $f) {
                            if (file_exists($f)) {
                                $zip->addFile($f, basename($f));
                            }
                        }
                        $zip->close();
                        // stream zip and clean up temp files after sending
                        return response()->streamDownload(function() use ($zipPath, $tempFiles, $tmpDir) {
                            $handle = fopen($zipPath, 'rb');
                            if ($handle) {
                                while (!feof($handle)) {
                                    echo fread($handle, 8192);
                                    flush();
                                }
                                fclose($handle);
                            }
                            // cleanup
                            foreach ($tempFiles as $tf) {
                                if (file_exists($tf)) @unlink($tf);
                            }
                            if (file_exists($zipPath)) @unlink($zipPath);
                            // attempt to remove the temp directory
                            @rmdir($tmpDir);
                        }, $zipName, ['Content-Type' => 'application/zip']);
                    }
                // fallback to single file if zip failed
            }

            $filename = 'Laporan-Kehadiran-Siswa-Harian-' . Carbon::parse($selectedTanggal)->format('Ymd') . '.xlsx';
            return Excel::download(new AbsensiLaporanSiswaHarianExport($laporanRows), $filename);
        }

        $laporanRows = $this->getRangeStudentReportRows($startDate, $endDate, $kelasId, $tahun, $semester, $guruId);
        $laporanRows = $laporanRows->sortBy('nama_kelas')->values();

        if ($canCreatePerKelasZip && empty($kelasId)) {
            $grouped = $laporanRows->groupBy('nama_kelas');
            $tempFiles = [];
            $tmpDir = storage_path('app/temp_exports_' . time());
            if (! file_exists($tmpDir)) mkdir($tmpDir, 0777, true);

            foreach ($grouped as $kelasName => $rows) {
                $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $kelasName ?: 'kelas');
                $fileName = "absensi_range_{$safeName}_" . Carbon::parse($startDate)->format('Ymd') . '_to_' . Carbon::parse($endDate)->format('Ymd') . '.xlsx';
                Excel::store(new \App\Exports\AbsensiLaporanSiswaRangeExport($rows->values()), basename($tmpDir) . '/' . $fileName);
                $tempFiles[] = storage_path('app/' . basename($tmpDir) . '/' . $fileName);
            }

            $zipName = 'Laporan-Absensi-' . ucfirst($period) . '-PerKelas-' . Carbon::parse($startDate)->format('Ymd') . '_to_' . Carbon::parse($endDate)->format('Ymd') . '.zip';
            $zipPath = $tmpDir . DIRECTORY_SEPARATOR . $zipName;
            $zip = new \ZipArchive();
            if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
                foreach ($tempFiles as $f) {
                    if (file_exists($f)) {
                        $zip->addFile($f, basename($f));
                    }
                }
                $zip->close();
                return response()->streamDownload(function() use ($zipPath, $tempFiles, $tmpDir) {
                    $handle = fopen($zipPath, 'rb');
                    if ($handle) {
                        while (!feof($handle)) {
                            echo fread($handle, 8192);
                            flush();
                        }
                        fclose($handle);
                    }
                    foreach ($tempFiles as $tf) {
                        if (file_exists($tf)) @unlink($tf);
                    }
                    if (file_exists($zipPath)) @unlink($zipPath);
                    @rmdir($tmpDir);
                }, $zipName, ['Content-Type' => 'application/zip']);
            }
            // fallback
        }

        $filename = 'Laporan-Kehadiran-Siswa-' . ucfirst($period) . '-' . Carbon::parse($startDate)->format('Ymd') . '_to_' . Carbon::parse($endDate)->format('Ymd') . '.xlsx';
        return Excel::download(new \App\Exports\AbsensiLaporanSiswaRangeExport($laporanRows), $filename);
    }

    private function isGuruPiketByDate($user, $selectedTanggal)
    {
        if (! $user || ! $user->guru_id) {
            return false;
        }

        $hariPiketArr = (array) ($user->guru->hari_piket ?? []);
        if (empty($hariPiketArr)) {
            return false;
        }

        try {
            $hariEnglish = Carbon::parse($selectedTanggal)->format('l');
        } catch (\Throwable $e) {
            return false;
        }

        $map = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];

        $selectedDay = $map[$hariEnglish] ?? null;
        return $selectedDay && in_array($selectedDay, $hariPiketArr, true);
    }

    private function isGuruPiketUser($user)
    {
        if (! $user || ! $user->guru_id) {
            return false;
        }

        return $user->hasRole('Guru Piket') || ! empty((array) ($user->guru->hari_piket ?? []));
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

        $isGuruPiket = !empty((array) ($user->guru->hari_piket ?? []));
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
            'total_alpa' => $items->sum(function ($item) {
                return $item->absensiSiswa->filter(function ($row) {
                    return in_array(strtolower((string) ($row->status ?? '')), ['alpa', 'alpha', 'alfa', 'absen'], true);
                })->count();
            }),
            // backward compatibility
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
        // Determine selected date early so we can evaluate piket for that specific day
        $selectedDate = $request->get('tanggal', date('Y-m-d'));
        // Compute whether the current user is acting as "guru piket" on the selected date.
        // Previously the code treated any guru with a non-empty hari_piket as guru piket
        // for all dates. Change behavior to only enable guru-piket UI when the selected
        // date matches one of the guru's assigned piket days, or the user explicitly
        // has the role 'Guru Piket'.
        $hariPiketArr = (array) ($user->guru->hari_piket ?? []);
        $map = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        try {
            $hariEnglish = \Carbon\Carbon::parse($selectedDate)->format('l');
            $hariIndo = $map[$hariEnglish] ?? $hariEnglish;
        } catch (\Throwable $e) {
            $hariIndo = null;
        }
        $isGuruPiket = $user->hasRole('Guru Piket') || ($hariIndo && in_array($hariIndo, $hariPiketArr, true));
        $isGuruBk = $user->hasRole('Guru BK');
        $isWaliKelas = $user->hasRole('Wali Kelas');
        $selectedKelasId = $request->get('kelas_id');
        $selectedJamBelajarId = null;
        $isQuickAccess = false;

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

        // Check if this is quick access or manual with kelas preselected
        if (!empty($selectedKelasId)) {
            $isQuickAccess = true;
        }

        if ($isSiswaOfficer) {
            $siswa = $user->siswa;
            $selectedKelasId = $siswa->kelas_id;
            $isQuickAccess = true;
        }

        // Validate teacher schedule access for normal guru users
        if ($user->guru_id && !$isAdminOrKepala && !$isGuruPiket && !$isGuruBk && !$isWaliKelas) {
            if ($selectedKelasId) {
                $hasSchedule = JadwalKbm::where('guru_id', $user->guru_id)
                    ->where('kelas_id', $selectedKelasId)
                    ->where('hari', $hariQuery)
                    ->where('tahun_ajaran_id', $tahunAjaran->id)
                    ->where('semester_id', $semester->id)
                    ->exists();
                    
                if (!$hasSchedule) {
                    session()->flash('warning', 'Anda tidak memiliki jadwal mengajar di kelas ini pada hari ' . $hariQuery . ' (' . date('d/m/Y', strtotime($selectedDate)) . '). Silakan pilih tanggal lain.');
                }
            }
        }

        $multiSlotJadwal = collect();
        
        // Get jadwal for current user if they are a teacher (excluding wali kelas)
        if ($user->guru_id && !$isAdminOrKepala && !$isGuruPiket && !$isWaliKelas) {
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
            
            // Get classes scheduled for this teacher on the selected date
            $kelasList = JadwalKbm::with(['kelas'])
                ->where('guru_id', $user->guru_id)
                ->where('hari', $hariQuery)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('semester_id', $semester->id)
                ->get()
                ->pluck('kelas')
                ->unique('id')
                ->sortBy('nama_kelas')
                ->values();

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
                    ->get()
                    ->unique(fn ($item) => $item->jam_belajar_id ?? $item->jam_ke)
                    ->values();

                $scheduledJamIds = $multiSlotJadwal->pluck('jam_belajar_id')->unique();
                if ($scheduledJamIds->isNotEmpty()) {
                    $jamBelajarList = JamBelajar::whereIn('id', $scheduledJamIds)->orderBy('urutan')->get();
                } else {
                    // Fallback to the master jam KBM list so the dropdown is never empty
                    $jamBelajarList = JamBelajar::orderBy('urutan')->get();
                }

                if (!$selectedJamBelajarId && $multiSlotJadwal->isNotEmpty()) {
                    $selectedJamBelajarId = $multiSlotJadwal->first()->jam_belajar_id;
                }
            } else {
                $jamBelajarList = JamBelajar::orderBy('urutan')->get();
            }
            
            // Also provide ALL schedules for this teacher (all days) so client-side
            // code can filter by any selected date without needing a page reload.
            $allJadwal = JadwalKbm::with(['kelas', 'jamBelajar', 'mataPelajaran'])
                ->where('guru_id', $user->guru_id)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('semester_id', $semester->id)
                ->get();

            $guruList = Guru::where('id', $user->guru_id)->get();
            $jadwalList = $allJadwal;
        } elseif ($isWaliKelas) {
            $kelasBinaan = Kelas::where('wali_kelas_id', $user->guru_id)->first();
            $allowedKelasIds = collect();
            if ($kelasBinaan) {
                $allowedKelasIds->push($kelasBinaan->id);
            }

            $hariEnglish = Carbon::parse($selectedDate)->format('l');
            $hariQuery = $hariIndonesia[$hariEnglish] ?? $hariEnglish;

            $jadwalLainIds = JadwalKbm::where('guru_id', $user->guru_id)
                ->when($kelasBinaan, function ($query) use ($kelasBinaan) {
                    return $query->where('kelas_id', '!=', $kelasBinaan->id);
                })
                ->where('hari', $hariQuery)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('semester_id', $semester->id)
                ->pluck('kelas_id')
                ->unique();

            $allowedKelasIds = $allowedKelasIds->merge($jadwalLainIds)->unique();
            $kelasList = $allowedKelasIds->isNotEmpty()
                ? Kelas::whereIn('id', $allowedKelasIds)->orderBy('nama_kelas')->get()
                : collect();

            if (! $selectedKelasId || ! $allowedKelasIds->contains($selectedKelasId)) {
                $selectedKelasId = optional($kelasBinaan)->id ?? $allowedKelasIds->first();
            }

            $jadwalForKelas = $selectedKelasId ? JadwalKbm::with(['jamBelajar', 'mataPelajaran', 'guru'])
                ->where('kelas_id', $selectedKelasId)
                ->where('hari', $hariQuery)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('semester_id', $semester->id)
                ->orderBy('jam_ke')
                ->get()
                ->unique(fn ($item) => $item->jam_belajar_id ?? $item->jam_ke)
                ->values() : collect();

            $multiSlotJadwal = $jadwalForKelas;
            $scheduledJamIds = $jadwalForKelas->pluck('jam_belajar_id')->unique();
            if ($scheduledJamIds->isNotEmpty()) {
                $jamBelajarList = JamBelajar::whereIn('id', $scheduledJamIds)->orderBy('urutan')->get();
            } else {
                $jamBelajarList = JamBelajar::orderBy('urutan')->get();
            }

            if (!$selectedJamBelajarId && $jadwalForKelas->isNotEmpty()) {
                $selectedJamBelajarId = $jadwalForKelas->first()->jam_belajar_id;
            }

            $guruList = $jadwalForKelas->pluck('guru')->unique('id')->values();
            if ($guruList->isEmpty() && $user->guru_id) {
                $guruList = Guru::where('id', $user->guru_id)->get();
            }
            $jadwalList = $jadwalForKelas;
        } elseif ($isSiswaOfficer) {
            $siswa = $user->siswa;
            $kelas = Kelas::find($siswa->kelas_id);
            $kelasList = $kelas ? collect([$kelas]) : collect();
            $selectedKelasId = $kelas->id ?? null;
                // Get all teachers who have schedule for this class in current year/semester
                $guruList = DB::table('jadwal_kbm')
                    ->join('guru', 'jadwal_kbm.guru_id', '=', 'guru.id')
                    ->where('jadwal_kbm.kelas_id', $selectedKelasId)
                    ->where('jadwal_kbm.tahun_ajaran_id', $tahunAjaran->id)
                    ->where('jadwal_kbm.semester_id', $semester->id)
                    ->select('guru.id', 'guru.nama', 'guru.kode_guru')
                    ->distinct()
                    ->orderBy('guru.nama')
                    ->get();

                // Build jadwal for this class to populate jamBelajarList based on selected date
                $hariEnglish = Carbon::parse($selectedDate)->format('l');
                $hariQuery = [
                    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
                ][$hariEnglish] ?? $hariEnglish;

                $jadwalForKelas = JadwalKbm::with('jamBelajar')
                    ->where('kelas_id', $selectedKelasId)
                    ->where('hari', $hariQuery)
                    ->where('tahun_ajaran_id', $tahunAjaran->id)
                    ->where('semester_id', $semester->id)
                    ->orderBy('jam_ke')
                    ->get()
                    ->unique(fn ($item) => $item->jam_belajar_id ?? $item->jam_ke)
                    ->values();

                $multiSlotJadwal = $jadwalForKelas;

                $scheduledJamIds = $jadwalForKelas->pluck('jam_belajar_id')->unique();
                if ($scheduledJamIds->isNotEmpty()) {
                    $jamBelajarList = JamBelajar::whereIn('id', $scheduledJamIds)->orderBy('urutan')->get();
                } else {
                    $jamBelajarList = JamBelajar::orderBy('urutan')->get();
                }

                $jadwalList = $jadwalForKelas;
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
            $jadwalList = JadwalKbm::with(['kelas', 'jamBelajar', 'guru'])
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('semester_id', $semester->id)
                ->get();
        }

        // If current user is a guru piket, provide the list of guru assigned piket for the selected day
        $guruPiketList = collect();
        if ($isGuruPiket) {
            try {
                $guruPiketList = Guru::whereJsonContains('hari_piket', $hariQuery)->orderBy('nama')->get();
            } catch (\Throwable $e) {
                $guruPiketList = collect();
            }
        }

        $settings = new SettingsManager();
        $verificationTimeoutSeconds = (int) $settings->get('attendance.verification_timeout_seconds', 300);

        $verificationActive = false;
        $verificationCode = null;
        $verificationExpiresAt = null;
        $verificationExpiresAtTimestamp = null;
        $verificationValidFrom = null;
        $verificationValidTo = null;
        $verificationRecord = null;

        if ($selectedKelasId && $selectedDate) {
            $verificationQuery = AttendanceVerificationCode::where('kelas_id', $selectedKelasId)
                ->where('tanggal', $selectedDate)
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now());
                });

            if ($selectedJamBelajarId) {
                $verificationQuery->where('jam_belajar_id', $selectedJamBelajarId);
            }

            $verificationRecord = $verificationQuery->orderByDesc('id')->first();

            if (! $verificationRecord) {
                // fallback: any active verification for this class/date, even if jam differs
                $verificationRecord = AttendanceVerificationCode::where('kelas_id', $selectedKelasId)
                    ->where('tanggal', $selectedDate)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now());
                    })
                    ->orderByDesc('id')
                    ->first();
            }

            if ($verificationRecord) {
                $verificationActive = true;
                $verificationCode = $verificationRecord->kode;
                $verificationExpiresAt = $verificationRecord->expires_at
                    ? Carbon::parse($verificationRecord->expires_at)->setTimezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP')
                    : null;
                $verificationExpiresAtTimestamp = $verificationRecord->expires_at
                    ? $verificationRecord->expires_at->timestamp * 1000
                    : null;
                $verificationValidFrom = $verificationRecord->valid_from ? Carbon::parse($verificationRecord->valid_from)->format('H:i') : null;
                $verificationValidTo = $verificationRecord->valid_to ? Carbon::parse($verificationRecord->valid_to)->format('H:i') : null;
                if (! $selectedJamBelajarId && $verificationRecord->jam_belajar_id) {
                    $selectedJamBelajarId = $verificationRecord->jam_belajar_id;
                }
            }
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
            'isGuruPiket',
            'guruPiketList',
            'isWaliKelas',
            'verificationTimeoutSeconds',
            'verificationActive',
            'verificationCode',
            'verificationExpiresAt',
            'verificationExpiresAtTimestamp',
            'verificationValidFrom',
            'verificationValidTo',
            'isAdminOrKepala'
        ));
    }

    public function refreshVerification(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'jam_belajar_id' => 'nullable|exists:jam_belajar,id',
            'tanggal' => 'required|date',
            'guru_id' => 'nullable|exists:guru,id',
            'verification_valid_from' => 'nullable|date_format:H:i',
            'verification_valid_to' => 'nullable|date_format:H:i',
            'timeout_seconds' => 'nullable|integer|min:10|max:3600'
        ]);

        if (! empty($validated['verification_valid_from']) && ! empty($validated['verification_valid_to']) && $validated['verification_valid_to'] < $validated['verification_valid_from']) {
            return response()->json(['success' => false, 'message' => 'Waktu akhir harus sama atau setelah waktu mulai.'], 422);
        }

        $guruId = $validated['guru_id'] ?? $user->guru_id ?? null;
        $timeout = (int) ($validated['timeout_seconds'] ?? (new \App\Services\SettingsManager())->get('attendance.verification_timeout_seconds', 300));
        $timeout = max(10, min(3600, $timeout));

        if ($request->filled('timeout_seconds')) {
            (new \App\Services\SettingsManager())->set('attendance.verification_timeout_seconds', $timeout);
        }

        $guruKode = null;
        if ($guruId) {
            $guruKode = DB::table('guru')->where('id', $guruId)->value('kode_guru');
        }

        $code = $this->generateVerificationCode($guruKode);
        $expiresAt = Carbon::now('Asia/Jakarta')->addSeconds($timeout);

        if (! empty($validated['verification_valid_to'])) {
            $expiresAt = Carbon::parse($validated['tanggal'] . ' ' . $validated['verification_valid_to'], 'Asia/Jakarta');
        }

        // Convert to UTC before persisting so Laravel reads it back correctly
        $expiresAtUtc = $expiresAt->copy()->setTimezone('UTC');

        try {
            $record = AttendanceVerificationCode::updateOrCreate(
                [
                    'guru_id' => $guruId,
                    'kelas_id' => $validated['kelas_id'],
                    'jam_belajar_id' => $validated['jam_belajar_id'] ?? null,
                    'tanggal' => $validated['tanggal'],
                ],
                $this->buildVerificationRecordValues($code, $expiresAtUtc, $validated)
            );
        } catch (\Throwable $e) {
            Log::error('absensi.verification.refresh_failed', [
                'kelas_id' => $validated['kelas_id'],
                'jam_belajar_id' => $validated['jam_belajar_id'] ?? null,
                'tanggal' => $validated['tanggal'],
                'guru_id' => $guruId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Gagal memperbarui kode verifikasi.'], 500);
        }

            return response()->json([
                'success' => true,
                'kode' => $record->kode,
                'expires_at_timestamp' => $record->expires_at ? $record->expires_at->timestamp * 1000 : null,
                'timeout_seconds' => $timeout,
            ]);
    }

    public function saveVerificationConfig(Request $request)
    {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'jam_belajar_id' => 'nullable|exists:jam_belajar,id',
            'verifikasi_aktif' => 'nullable|boolean',
            'verification_valid_from' => 'nullable|date_format:H:i',
            'verification_valid_to' => 'nullable|date_format:H:i',
            'timeout_seconds' => 'nullable|integer|min:10|max:3600',
        ]);

        if (! empty($validated['verification_valid_from']) && ! empty($validated['verification_valid_to']) && $validated['verification_valid_to'] < $validated['verification_valid_from']) {
            return response()->json(['success' => false, 'message' => 'Waktu akhir harus sama atau setelah waktu mulai.'], 422);
        }

        $guruId = $user->guru_id ?? null;
        if (! $guruId) {
            $guruId = DB::table('kelas')->where('id', $validated['kelas_id'])->value('wali_kelas_id');
        }

        $active = (bool) ($validated['verifikasi_aktif'] ?? false);

        if (! $active) {
            try {
                AttendanceVerificationCode::where('kelas_id', $validated['kelas_id'])
                    ->where('tanggal', $validated['tanggal'])
                    ->when($validated['jam_belajar_id'], fn($q) => $q->where('jam_belajar_id', $validated['jam_belajar_id']))
                    ->when($guruId, fn($q) => $q->where('guru_id', $guruId))
                    ->delete();
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'message' => 'Gagal menyimpan konfigurasi verifikasi.'], 500);
            }

            return response()->json(['success' => true, 'message' => 'Konfigurasi verifikasi berhasil disimpan.']);
        }

        $timeout = (int) ($validated['timeout_seconds'] ?? (new \App\Services\SettingsManager())->get('attendance.verification_timeout_seconds', 300));
        $timeout = max(10, min(3600, $timeout));

        if ($request->filled('timeout_seconds')) {
            (new \App\Services\SettingsManager())->set('attendance.verification_timeout_seconds', $timeout);
        }

        $guruKode = null;
        if ($guruId) {
            $guruKode = DB::table('guru')->where('id', $guruId)->value('kode_guru');
        }

        $code = $this->generateVerificationCode($guruKode);
        $expiresAt = Carbon::now('Asia/Jakarta')->addSeconds($timeout);
        if (! empty($validated['verification_valid_to'])) {
            $expiresAt = Carbon::parse($validated['tanggal'] . ' ' . $validated['verification_valid_to'], 'Asia/Jakarta');
        }

        // Convert to UTC before persisting so Laravel reads it back correctly
        $expiresAtUtc = $expiresAt->copy()->setTimezone('UTC');

        try {
            $record = AttendanceVerificationCode::updateOrCreate(
                [
                    'guru_id' => $guruId,
                    'kelas_id' => $validated['kelas_id'],
                    'jam_belajar_id' => $validated['jam_belajar_id'] ?? null,
                    'tanggal' => $validated['tanggal'],
                ],
                $this->buildVerificationRecordValues($code, $expiresAtUtc, $validated)
            );
        } catch (\Throwable $e) {
            Log::error('absensi.verification.save_failed', [
                'kelas_id' => $validated['kelas_id'],
                'jam_belajar_id' => $validated['jam_belajar_id'] ?? null,
                'tanggal' => $validated['tanggal'],
                'guru_id' => $guruId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan konfigurasi verifikasi.'], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Konfigurasi verifikasi berhasil disimpan.',
            'kode' => $record->kode,
            'expires_at_timestamp' => $record->expires_at ? $record->expires_at->timestamp * 1000 : null,
            'valid_from' => $record->valid_from,
            'valid_to' => $record->valid_to,
            'timeout_seconds' => $timeout,
        ]);
    }

    public function verifyStudent(Request $request)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasRole('Siswa') || empty($user->siswa_id)) {
            return response()->json(['success' => false, 'message' => 'Akses ditolak'], 403);
        }

        $validated = $request->validate([
            'kode' => 'required|string',
            'kelas_id' => 'nullable|exists:kelas,id',
            'tanggal' => 'nullable|date',
            'jam_belajar_id' => 'nullable|exists:jam_belajar,id',
        ]);

        $tanggal = $validated['tanggal'] ?? date('Y-m-d');
        $kode = trim($validated['kode']);
        $kelasId = $validated['kelas_id'] ?? ($user->siswa->kelas_id ?? null);
        $jamId = $validated['jam_belajar_id'] ?? null;

        if (! $kelasId) {
            return response()->json(['success' => false, 'message' => 'Kelas tidak ditemukan untuk siswa ini.'], 400);
        }

        $now = Carbon::now();

        // Try find by verification table first (active code generated by teacher)
        $verif = AttendanceVerificationCode::where('kelas_id', $kelasId)
            ->where('tanggal', $tanggal)
            ->where('kode', $kode)
            ->where(function($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            })
            ->when($jamId, function ($q) use ($jamId) { return $q->where('jam_belajar_id', $jamId); })
            ->first();

        if ($verif) {
            $currentTime = Carbon::now()->format('H:i');
            if (! empty($verif->valid_from) && $currentTime < $verif->valid_from) {
                $verif = null;
            }
            if (! empty($verif->valid_to) && $currentTime > $verif->valid_to) {
                $verif = null;
            }
        }

        // If not found, try match against AbsensiKelas.kode_verifikasi (in case code was stored in absensi record)
        $absensi = null;
        if ($verif) {
            // find an AbsensiKelas record that matches the scope (if exists)
            $absensi = AbsensiKelas::where('kelas_id', $kelasId)
                ->whereDate('tanggal', $tanggal)
                ->when($jamId, fn($q) => $q->where('jam_belajar_id', $jamId))
                ->where(function($q) use ($kode) {
                    $q->where('kode_verifikasi', $kode)->orWhereNull('kode_verifikasi');
                })
                ->orderByDesc('id')
                ->first();
        } else {
            $absensi = AbsensiKelas::where('kelas_id', $kelasId)
                ->whereDate('tanggal', $tanggal)
                ->when($jamId, fn($q) => $q->where('jam_belajar_id', $jamId))
                ->where('kode_verifikasi', $kode)
                ->orderByDesc('id')
                ->first();
        }

        if (! $verif && ! $absensi) {
            return response()->json(['success' => false, 'message' => 'Kode verifikasi tidak valid atau sudah kadaluarsa.'], 400);
        }

        // Determine absensi_kelas id to attach student's hadir
        $absensiKelas = $absensi ?? null;

        // If no AbsensiKelas exists, create a minimal one so student can be recorded
        if (! $absensiKelas) {
            // ensure we have active tahun/semester (table constraints require non-null)
            $tahunAjaran = TahunAjaran::where('is_active', 1)->first();
            $semesterActive = Semester::where('is_active', 1)->first();
            if (! $tahunAjaran || ! $semesterActive) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat membuat entri absensi: Tahun ajaran / semester aktif belum diset. Hubungi admin.'], 500);
            }

            // determine a guru_id to attach: prefer verification record's guru, then scheduled teacher for the kelas/jam, then kelas.wali_kelas_id
            $guruIdToUse = null;
            if ($verif && ! empty($verif->guru_id)) {
                $guruIdToUse = $verif->guru_id;
            }

            if (! $guruIdToUse) {
                $jadwalQuery = JadwalKbm::where('kelas_id', $kelasId)
                    ->when($jamId, fn($q) => $q->where('jam_belajar_id', $jamId))
                    ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
                    ->when($semesterActive, fn($q) => $q->where('semester_id', $semesterActive->id));

                $guruIdToUse = $jadwalQuery->value('guru_id');
            }

            if (! $guruIdToUse) {
                $guruIdToUse = DB::table('kelas')->where('id', $kelasId)->value('wali_kelas_id');
            }

            // determine a jam_belajar_id to attach: prefer verification record's jam, then scheduled jam for the kelas on that date, then any scheduled jam, then master JamBelajar fallback
            $jamIdToUse = $jamId;
            if (empty($jamIdToUse) && $verif && ! empty($verif->jam_belajar_id)) {
                $jamIdToUse = $verif->jam_belajar_id;
            }

            if (empty($jamIdToUse)) {
                try {
                    $hariEnglish = Carbon::parse($tanggal)->format('l');
                    $mapHari = [
                        'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
                    ];
                    $hariQuery = $mapHari[$hariEnglish] ?? $hariEnglish;

                    $jadwalQuery = JadwalKbm::where('kelas_id', $kelasId)
                        ->when($hariQuery, fn($q) => $q->where('hari', $hariQuery))
                        ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
                        ->when($semesterActive, fn($q) => $q->where('semester_id', $semesterActive->id));

                    $jamIdToUse = $jadwalQuery->value('jam_belajar_id');
                } catch (\Throwable $_) {
                    $jamIdToUse = null;
                }
            }

            if (empty($jamIdToUse)) {
                // try any schedule for the kelas in current tahun/semester
                $jamIdToUse = JadwalKbm::where('kelas_id', $kelasId)
                    ->when($tahunAjaran, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id))
                    ->when($semesterActive, fn($q) => $q->where('semester_id', $semesterActive->id))
                    ->value('jam_belajar_id');
            }

            if (empty($jamIdToUse)) {
                // final fallback to first master JamBelajar
                $jamIdToUse = JamBelajar::orderBy('urutan')->value('id');
            }

            if (! $guruIdToUse) {
                return response()->json(['success' => false, 'message' => 'Tidak dapat membuat entri absensi: tidak ditemukan guru terkait untuk kelas ini. Hubungi admin.'], 500);
            }

            try {
                $absensiKelas = AbsensiKelas::create([
                    'kelas_id' => $kelasId,
                    'guru_id' => $guruIdToUse,
                    'jam_belajar_id' => $jamIdToUse,
                    'tanggal' => $tanggal,
                    'status_kelas' => null,
                    'tahun_ajaran_id' => $tahunAjaran->id,
                    'semester_id' => $semesterActive->id,
                    'verifikasi_aktif' => true,
                    'kode_verifikasi' => $kode,
                    'kode_verifikasi_expires_at' => $verif ? $verif->expires_at : null,
                ]);
            } catch (\Throwable $e) {
                return response()->json(['success' => false, 'message' => 'Gagal membuat entri absensi: ' . $e->getMessage()], 500);
            }
        }

        // Upsert AbsensiSiswa record for this student as 'hadir'
        try {
            $siswaId = $user->siswa_id;
            $as = AbsensiSiswa::updateOrCreate(
                ['absensi_kelas_id' => $absensiKelas->id, 'siswa_id' => $siswaId],
                ['status' => 'hadir', 'keterangan' => null]
            );
            // Update agenda/guru note
            $this->updateAgendaGuruAttendanceNote($absensiKelas);
            $this->flushStudentReportCache();

            // Broadcast real-time notification so teacher view can update
            try {
                $siswaName = null;
                try { $siswaName = \App\Models\Siswa::find($siswaId)->nama ?? null; } catch (\Throwable $_) { $siswaName = null; }
                event(new StudentVerified($absensiKelas->kelas_id, $absensiKelas->tanggal, $siswaId, 'hadir', $siswaName));
            } catch (\Throwable $__e) {
                Log::warning('broadcast.student_verified.failed', ['error' => $__e->getMessage()]);
            }
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan absensi siswa: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => true, 'message' => 'Verifikasi berhasil. Anda tercatat hadir.', 'absensi_kelas_id' => $absensiKelas->id]);
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

        // handle absensi guru inputs (if present) - upsert per guru per tanggal
        try {
            $absensiGuruInputs = $request->input('absensi_guru', []);
            $keteranganGuruInputs = $request->input('keterangan_guru', []);
            foreach ($absensiGuruInputs as $gId => $statusRaw) {
                $status = strtolower(trim($statusRaw ?? ''));
                // normalize frontend values to DB enum
                if (in_array($status, ['alpa','absen','alpha','alfa','tidak_hadir','tidak hadir'], true)) {
                    $status = 'tidak_hadir';
                } elseif ($status === 'hadir' || $status === 'sakit' || $status === 'izin') {
                    // keep as-is
                } else {
                    // default to tidak_hadir for unknown values
                    $status = 'tidak_hadir';
                }

                $keterangan = isset($keteranganGuruInputs[$gId]) ? trim($keteranganGuruInputs[$gId]) : null;

                AbsensiGuru::updateOrCreate(
                    [
                        'guru_id' => $gId,
                        'tanggal' => $validated['tanggal'],
                        'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
                        'semester_id' => $validated['semester_id'],
                    ],
                    [
                        'status' => $status,
                        'keterangan' => $keterangan,
                        'pencatat_guru_id' => auth()->user()->guru_id ?? null,
                    ]
                );
            }
        } catch (\Throwable $e) {
            // don't block main flow; log and continue
            Log::warning('absensi.store.absensi_guru_failed', ['error' => $e->getMessage(), 'input' => $request->input('absensi_guru')]);
        }

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
        $isGuruPiket = !empty((array) ($user->guru->hari_piket ?? []));
        $isGuruBk = $user->hasRole('Guru BK');
        $isWaliKelas = $user->hasRole('Wali Kelas');

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

        $applyAll = $request->has('apply_to_all_guru');

        // Debug logging: record who attempted to store absensi and payload
        try {
            Log::info('absensi.store.attempt', [
                'user_id' => optional(auth()->user())->id,
                'user_guru_id' => optional(auth()->user())->guru_id,
                'roles' => auth()->user() ? auth()->user()->getRoleNames()->toArray() : [],
                'input' => $request->all(),
            ]);
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => [Rule::requiredIf(!$applyAll), 'nullable', 'exists:guru,id'],
            'jam_belajar_id' => [Rule::requiredIf(!$applyAll), 'nullable', 'exists:jam_belajar,id'],
            'tanggal' => 'required|date',
            'status_kelas' => 'nullable|string|max:100',
            'verifikasi_aktif' => 'nullable|boolean',
            'kode_verifikasi' => 'nullable|string|max:32',
            'kode_verifikasi_expires_at' => 'nullable|date',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'semester_id' => 'required|exists:semester,id',
        ]);

        $absensiSiswa = $request->input('absensi_siswa', []);
        $keteranganSiswa = $request->input('keterangan_siswa', []);

        if ($this->isFutureDate($validated['tanggal'])) {
            return back()->withInput()->withErrors(['tanggal' => 'Tanggal absensi tidak boleh lebih dari hari ini.']);
        }

        if ($this->isPastDate($validated['tanggal']) && ! $this->canEditAttendanceDate($user, $validated['tanggal'])) {
            return back()->withInput()->withErrors(['tanggal' => 'Absensi tanggal lampau tidak diizinkan untuk peran Anda.']);
        }

        $verificationActive = $request->boolean('verifikasi_aktif');
        $verificationCode = $request->input('kode_verifikasi');
        $verificationExpiresAt = null;
        if ($verificationActive) {
            if (empty($verificationCode)) {
                $verificationCode = $this->generateVerificationCode();
            }

            $timeoutSeconds = (int) (new SettingsManager())->get('attendance.verification_timeout_seconds', 300);
            $timeoutSeconds = max(10, min(3600, $timeoutSeconds));
            $verificationExpiresAt = Carbon::now()->addSeconds($timeoutSeconds);
        }

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

        if ($isWaliKelas) {
            $kelasBinaan = Kelas::where('wali_kelas_id', $user->guru_id)->first();
            if (! $kelasBinaan) {
                return back()->withErrors(['error' => 'Anda tidak memiliki kelas binaan yang valid.']);
            }

            $allowedKelas = collect([$kelasBinaan->id]);

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

            $jadwalLain = JadwalKbm::where('guru_id', $user->guru_id)
                ->when($kelasBinaan, function ($query) use ($kelasBinaan) {
                    return $query->where('kelas_id', '!=', $kelasBinaan->id);
                })
                ->where('hari', $hariQuery)
                ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
                ->where('semester_id', $validated['semester_id'])
                ->pluck('kelas_id')
                ->unique();

            $allowedKelas = $allowedKelas->merge($jadwalLain)->unique();

            if (! $allowedKelas->contains($validated['kelas_id'])) {
                return back()->withErrors(['error' => 'Anda hanya dapat menginput absensi untuk kelas binaan Anda atau kelas lain jika Anda memiliki jadwal di kelas tersebut.']);
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

            // Build target entries: each entry contains 'jam_id' and 'guru_id' to create AbsensiKelas records
            $targetEntries = collect();

            if ($applyAll) {
                // Find all jadwal entries for the class on that day (may include different guru per jam)
                $jadwalEntries = JadwalKbm::where('kelas_id', $validated['kelas_id'])
                    ->where('hari', $hariQuery)
                    ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
                    ->where('semester_id', $validated['semester_id'])
                    ->get();

                if ($jadwalEntries->isNotEmpty()) {
                    foreach ($jadwalEntries as $je) {
                        $targetEntries->push((object)[
                            'jam_id' => $je->jam_belajar_id,
                            'guru_id' => $je->guru_id,
                        ]);
                    }
                    // unique by jam_id and guru_id
                    $targetEntries = $targetEntries->unique(fn($x) => $x->jam_id . ':' . ($x->guru_id ?? ''));
                } else {
                    // fallback to single entry with provided jam and guru (if any)
                    $targetEntries->push((object)['jam_id' => $validated['jam_belajar_id'], 'guru_id' => $validated['guru_id'] ?? null]);
                }
            } elseif ($isAdminOrKepala || $isGuruPiket || $isGuruBk) {
                $targetEntries->push((object)['jam_id' => $validated['jam_belajar_id'], 'guru_id' => $validated['guru_id'] ?? null]);
            } elseif ($isWaliKelas) {
                $jadwalEntries = JadwalKbm::where('kelas_id', $validated['kelas_id'])
                    ->where('hari', $hariQuery)
                    ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
                    ->where('semester_id', $validated['semester_id'])
                    ->get();

                if ($jadwalEntries->isNotEmpty()) {
                    foreach ($jadwalEntries as $entry) {
                        $targetEntries->push((object)[
                            'jam_id' => $entry->jam_belajar_id,
                            'guru_id' => $entry->guru_id,
                        ]);
                    }
                    $targetEntries = $targetEntries->unique(fn($entry) => $entry->jam_id . ':' . ($entry->guru_id ?? ''))->values();
                } else {
                    // Do not allow wali kelas to create absensi when there is no KBM jadwal
                    return back()->withInput()->withErrors(['error' => 'Tidak ada jadwal KBM untuk kelas ini pada hari tersebut. Wali Kelas tidak dapat membuat absensi tanpa jadwal.']);
                }
            } elseif ($user->guru_id && !$isWaliKelas) {
                $jadwalJamIds = JadwalKbm::where('guru_id', $validated['guru_id'])
                    ->where('kelas_id', $validated['kelas_id'])
                    ->where('hari', $hariQuery)
                    ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
                    ->where('semester_id', $validated['semester_id'])
                    ->pluck('jam_belajar_id')
                    ->unique();

                $jadwalJamIds = $jadwalJamIds->push($validated['jam_belajar_id'])->unique()->values();
                foreach ($jadwalJamIds as $jid) {
                    $targetEntries->push((object)['jam_id' => $jid, 'guru_id' => $validated['guru_id']]);
                }
            } else {
                $targetEntries->push((object)['jam_id' => $validated['jam_belajar_id'], 'guru_id' => $validated['guru_id'] ?? null]);
            }

            $createdAbsensi = [];
            $skippedJamIds = [];
            $firstExistingAbsensi = null;
            $savedAbsensiSiswaCount = 0;

            // Additional safety: ensure normal guru users cannot create absensi for jams
            // they do not actually have in their Jadwal KBM. This prevents bypasses
            // where a jam id is supplied but no jadwal exists for the teacher.
            if ($user->guru_id && !$isAdminOrKepala && !$isGuruPiket && !$isGuruBk && !$isWaliKelas) {
                foreach ($targetEntries as $te) {
                    $jamToCheck = $te->jam_id;
                    $guruToCheck = $te->guru_id ?? $validated['guru_id'] ?? null;
                    if (empty($guruToCheck)) continue;

                    $exists = JadwalKbm::where('guru_id', $guruToCheck)
                        ->where('kelas_id', $validated['kelas_id'])
                        ->where('jam_belajar_id', $jamToCheck)
                        ->where('hari', $hariQuery)
                        ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
                        ->where('semester_id', $validated['semester_id'])
                        ->exists();

                    if (! $exists) {
                        return back()->withInput()->withErrors(['error' => 'Anda tidak memiliki jadwal KBM untuk jam yang dipilih, sehingga absensi tidak dapat disimpan.']);
                    }
                }
            }

            $appliedJamIds = [];
            foreach ($targetEntries as $entry) {
                $jamId = $entry->jam_id;
                $entryGuruId = $entry->guru_id ?? $validated['guru_id'] ?? null;

                $existing = AbsensiKelas::where('kelas_id', $validated['kelas_id'])
                    ->where('guru_id', $entryGuruId)
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

                $toCreate = array_merge($validated, [
                    'jam_belajar_id' => $jamId,
                    'guru_id' => $entryGuruId,
                    'verifikasi_aktif' => $verificationActive,
                    'kode_verifikasi' => $verificationActive ? $verificationCode : null,
                    'kode_verifikasi_expires_at' => $verificationActive ? $verificationExpiresAt : null,
                ]);

                $absensi = AbsensiKelas::create($toCreate);

                // remove any pending verification codes for this scope since absensi now created
                try {
                    AttendanceVerificationCode::where('kelas_id', $validated['kelas_id'])
                        ->where('jam_belajar_id', $jamId)
                        ->where('tanggal', $validated['tanggal'])
                        ->when($entryGuruId, function ($q) use ($entryGuruId) { return $q->where('guru_id', $entryGuruId); })
                        ->delete();
                } catch (\Throwable $e) {
                    // don't block saving absensi if cleanup fails
                    \Log::warning('Failed to cleanup attendance_verification_codes: ' . $e->getMessage());
                }

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
                $appliedJamIds[] = $jamId;
            }

            DB::commit();
            $this->flushStudentReportCache();

            $jamBelajarLabels = JamBelajar::whereIn('id', array_values(array_unique($appliedJamIds)))
                ->orderBy('urutan')
                ->get()
                ->map(function ($jam) {
                    return 'Jam ke-' . $jam->urutan;
                })->implode(', ');

            $skippedLabels = JamBelajar::whereIn('id', array_values(array_unique($skippedJamIds)))
                ->orderBy('urutan')
                ->get()
                ->map(function ($jam) {
                    return 'Jam ke-' . $jam->urutan;
                })->implode(', ');

            $successMessage = !empty($createdAbsensi)
                ? 'Absensi kelas berhasil disimpan untuk ' . $savedAbsensiSiswaCount . ' siswa.'
                : 'Absensi sudah tersedia untuk jadwal ini.';
            if (!empty($appliedJamIds) && count(array_values(array_unique($appliedJamIds))) > 1) {
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

        // determine if current user is piket for today
        $user = auth()->user();
        $isGuruPiket = false;
        if ($user && $user->guru_id) {
            $hariPiketArr = (array) ($user->guru->hari_piket ?? []);
            $todayEng = \Carbon\Carbon::now()->format('l');
            $map = [
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
            ];
            $todayIndo = $map[$todayEng] ?? null;
            $isGuruPiket = in_array($todayIndo, $hariPiketArr, true);
        }

        return view('absensi.show', compact('absensi', 'isGuruPiket'));
    }

    private function canEditPastAttendance($user): bool
    {
        return $this->canEditAttendanceDate($user, Carbon::today()->toDateString());
    }

    protected function canEditAttendanceDate($user, $date): bool
    {
        if (! $user) {
            return false;
        }

        $dateValue = Carbon::parse($date)->startOfDay();
        $today = Carbon::today();

        if ($dateValue->gt($today)) {
            return false;
        }

        if ($dateValue->lt($today)) {
            if ($user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
                return true;
            }

            if ($user->hasRole('Wali Kelas') || $user->hasRole('Guru BK')) {
                return true;
            }

            if ($user->hasRole('Guru')) {
                return (bool) (new SettingsManager())->get('attendance.allow_edit_past_for_guru', false);
            }

            if ($user->hasRole('Siswa') && $user->hasClassPosition()) {
                return (bool) (new SettingsManager())->get('attendance.allow_edit_past_for_siswa_officer', false);
            }

            return false;
        }

        return true;
    }

    protected function canModifyStudentAttendanceStatus($user, $date, $isGuruPiketToday = false): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            return true;
        }

        if ($user->hasRole('Wali Kelas') || $user->hasRole('Guru BK')) {
            return true;
        }

        if ($user->hasRole('Guru')) {
            $dateValue = Carbon::parse($date)->startOfDay();
            $today = Carbon::today();

            if ($dateValue->lt($today)) {
                return (bool) (new SettingsManager())->get('attendance.allow_edit_past_for_guru', false);
            }

            return $isGuruPiketToday;
        }

        return false;
    }

    private function isFutureDate($date): bool
    {
        return Carbon::parse($date)->startOfDay()->gt(Carbon::today());
    }

    private function isPastDate($date): bool
    {
        return Carbon::parse($date)->startOfDay()->lt(Carbon::today());
    }

    public function updateSiswaStatus(Request $request, $absensiId, $siswaId)
    {
        $user = auth()->user();
        if (! $user || empty($user->guru_id)) {
            abort(403, 'Akses ditolak. Hanya guru yang dapat mengubah status.');
        }

        // allow admins and kepala always
        $isAdminOrKepala = $user->hasAnyRole(['Admin', 'Kepala Sekolah']);

        // check piket for today unless admin/kepala
        $isGuruPiket = false;
        if (! $isAdminOrKepala) {
            $hariPiketArr = (array) ($user->guru->hari_piket ?? []);
            $todayEng = \Carbon\Carbon::now()->format('l');
            $map = [
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
            ];
            $todayIndo = $map[$todayEng] ?? null;
            $isGuruPiket = in_array($todayIndo, $hariPiketArr, true);
        } else {
            $isGuruPiket = true;
        }

        $absensi = AbsensiKelas::findOrFail($absensiId);
        if (! $this->canModifyStudentAttendanceStatus($user, $absensi->tanggal, $isGuruPiket)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengubah status absensi ini.');
        }

        $validated = $request->validate([
            'status' => 'required|string|max:50',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // ensure siswa in same kelas
        $siswa = \App\Models\Siswa::where('id', $siswaId)->where('kelas_id', $absensi->kelas_id)->first();
        if (! $siswa) {
            return redirect()->back()->withErrors(['error' => 'Siswa tidak ditemukan di kelas ini.']);
        }

        $statusNormalized = $this->normalizeAttendanceStatus($validated['status']);

        $as = AbsensiSiswa::where('absensi_kelas_id', $absensi->id)->where('siswa_id', $siswaId)->first();
        $payload = [
            'status' => $statusNormalized,
            'keterangan' => $validated['keterangan'] ?? null,
            'updated_by' => $user->id,
        ];

        // record history before change
        $previousStatus = $as->status ?? null;
        $previousKeterangan = $as->keterangan ?? null;

        if ($as) {
            $as->update($payload);
            $absensiSiswaId = $as->id;
        } else {
            $payload['absensi_kelas_id'] = $absensi->id;
            $payload['siswa_id'] = $siswaId;
            $created = AbsensiSiswa::create($payload);
            $absensiSiswaId = $created->id;
        }

        // store history
        try {
            \App\Models\AbsensiSiswaHistory::create([
                'absensi_siswa_id' => $absensiSiswaId ?? null,
                'absensi_kelas_id' => $absensi->id,
                'siswa_id' => $siswaId,
                'previous_status' => $previousStatus,
                'new_status' => $statusNormalized,
                'previous_keterangan' => $previousKeterangan,
                'new_keterangan' => $payload['keterangan'],
                'changed_by' => $user->id,
            ]);
        } catch (\Exception $e) {
            // don't block update if history fails; log if necessary
            \Log::error('Failed to write absensi_siswa_history: ' . $e->getMessage());
        }

        // Update related notes and pelanggaran if necessary
        $deletedPelanggaran = 0;
        if (Schema::hasTable('pelanggaran_siswa')) {
            // remove pelanggaran if status changed to hadir/izin/sakit
            if (in_array(strtolower($statusNormalized), ['hadir','izin','sakit'], true)) {
                $deletedPelanggaran = DB::table('pelanggaran_siswa')->where('siswa_id', $siswaId)
                    ->whereDate('tanggal', $absensi->tanggal)
                    ->where('kelas_id', $absensi->kelas_id)
                    ->delete();
            }
        }

        // Sync to agenda guru
        $this->updateAgendaGuruAttendanceNote($absensi);
        $this->flushStudentReportCache();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status absensi siswa berhasil diperbarui.',
                'deleted_pelanggaran' => (int) $deletedPelanggaran,
            ]);
        }

        return redirect()->route('absensi.show', $absensi->id)->with('success', 'Status absensi siswa berhasil diperbarui.');
    }

    public function edit($id)
    {
        $absensi = AbsensiKelas::findOrFail($id);
        if (! $this->canEditAttendanceDate(auth()->user(), $absensi->tanggal)) {
            return redirect()->route('absensi.index')
                ->with('error', 'Akses ditolak. Absensi tanggal lampau hanya dapat diedit sesuai pengaturan admin.');
        }

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
        $user = auth()->user();

        if (! $this->canEditAttendanceDate($user, $absensi->tanggal)) {
            return back()->with('error', 'Akses ditolak. Absensi tanggal lampau hanya dapat diedit sesuai pengaturan admin.');
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

        // Debug: log incoming per-siswa payload for troubleshooting
        try {
            \Log::info('absensi.update.payload', [
                'user_id' => optional($user)->id,
                'absensi_id' => $absensi->id,
                'absensi_siswa_keys' => array_keys((array) $request->input('absensi_siswa', [])),
                'keterangan_siswa_keys' => array_keys((array) $request->input('keterangan_siswa', [])),
                'raw_input_count' => count($request->all()),
            ]);
        } catch (\Throwable $e) {
            // ignore logging failures
        }

        if (! $this->canEditAttendanceDate($user, $validated['tanggal'])) {
            return back()->with('error', 'Akses ditolak. Absensi tanggal lampau hanya dapat diedit sesuai pengaturan admin.');
        }

        $absensi->update($validated);
        
        // Update attendance note in agenda guru
        $this->updateAgendaGuruAttendanceNote($absensi);
        $this->flushStudentReportCache();

        // Process per-siswa status updates if provided
        $absensiSiswa = $request->input('absensi_siswa', []);
        $keteranganSiswa = $request->input('keterangan_siswa', []);

        if (!empty($absensiSiswa) && is_array($absensiSiswa)) {
            // determine isGuruPiket similar to updateSiswaStatus logic
            $isAdminOrKepala = $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
            $isGuruPiket = false;
            if (! $isAdminOrKepala && $user && ! empty($user->guru_id)) {
                $hariPiketArr = (array) ($user->guru->hari_piket ?? []);
                $todayEng = \Carbon\Carbon::now()->format('l');
                $map = [
                    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
                ];
                $todayIndo = $map[$todayEng] ?? null;
                $isGuruPiket = in_array($todayIndo, $hariPiketArr, true);
            } else {
                $isGuruPiket = true;
            }

            $canModifyGlobal = $this->canEditAttendanceDate($user, $absensi->tanggal) || (
                $this->canModifyStudentAttendanceStatus($user, $absensi->tanggal, $isGuruPiket) || (
                    $user && !empty($user->guru_id) && $user->guru_id == $absensi->guru_id
                )
            );

            foreach ($absensiSiswa as $siswaId => $statusRaw) {
                // permission check per-student
                if (! $canModifyGlobal) {
                    try { \Log::info('absensi.update.skip_permission', ['user_id' => $user->id ?? null, 'absensi_id' => $absensi->id]); } catch (\Throwable $e) {}
                    continue; // skip if user not allowed to modify student statuses
                }

                $siswa = \App\Models\Siswa::where('id', $siswaId)->where('kelas_id', $absensi->kelas_id)->first();
                if (! $siswa) {
                    continue;
                }

                $statusNormalized = $this->normalizeAttendanceStatus($statusRaw);
                if (empty($statusNormalized)) {
                    continue;
                }

                $as = AbsensiSiswa::where('absensi_kelas_id', $absensi->id)->where('siswa_id', $siswaId)->first();
                $payload = [
                    'status' => $statusNormalized,
                    'keterangan' => isset($keteranganSiswa[$siswaId]) ? $keteranganSiswa[$siswaId] : null,
                    'updated_by' => $user->id,
                ];

                $previousStatus = $as->status ?? null;
                $previousKeterangan = $as->keterangan ?? null;

                if ($as) {
                    $as->update($payload);
                    $absensiSiswaId = $as->id;
                    try { \Log::info('absensi.update.siswa', ['action' => 'update', 'absensi_id' => $absensi->id, 'siswa_id' => $siswaId, 'previous' => $previousStatus, 'new' => $statusNormalized, 'keterangan' => $payload['keterangan']]); } catch (\Throwable $e) {}
                } else {
                    $payload['absensi_kelas_id'] = $absensi->id;
                    $payload['siswa_id'] = $siswaId;
                    $created = AbsensiSiswa::create($payload);
                    $absensiSiswaId = $created->id;
                    try { \Log::info('absensi.update.siswa', ['action' => 'create', 'absensi_id' => $absensi->id, 'siswa_id' => $siswaId, 'new' => $statusNormalized, 'keterangan' => $payload['keterangan']]); } catch (\Throwable $e) {}
                }

                // store history
                try {
                    \App\Models\AbsensiSiswaHistory::create([
                        'absensi_siswa_id' => $absensiSiswaId ?? null,
                        'absensi_kelas_id' => $absensi->id,
                        'siswa_id' => $siswaId,
                        'previous_status' => $previousStatus,
                        'new_status' => $statusNormalized,
                        'previous_keterangan' => $previousKeterangan,
                        'new_keterangan' => $payload['keterangan'],
                        'changed_by' => $user->id,
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to write absensi_siswa_history: ' . $e->getMessage());
                }

                // remove pelanggaran if status changed to hadir/izin/sakit
                if (Schema::hasTable('pelanggaran_siswa')) {
                    if (in_array(strtolower($statusNormalized), ['hadir','izin','sakit'], true)) {
                        DB::table('pelanggaran_siswa')->where('siswa_id', $siswaId)
                            ->whereDate('tanggal', $absensi->tanggal)
                            ->where('kelas_id', $absensi->kelas_id)
                            ->delete();
                    }
                }
            }

            // ensure related agenda is updated after student changes
            $this->updateAgendaGuruAttendanceNote($absensi);
        }

        return redirect()->route('absensi.show', $absensi->id)
            ->with('success', 'Absensi kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $absensi = AbsensiKelas::findOrFail($id);
        if (! $this->canEditAttendanceDate(auth()->user(), $absensi->tanggal)) {
            return back()->with('error', 'Akses ditolak. Absensi tanggal lampau hanya dapat dihapus sesuai pengaturan admin.');
        }

        $absensi->delete();
        $this->flushStudentReportCache();

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
            $this->flushStudentReportCache();

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
            $tanggal = $request->get('tanggal');
            $loadExisting = $request->boolean('load_existing', false);
            
            if (!$kelasId) {
                return response()->json([
                    'siswa' => [],
                    'error' => 'Kelas ID tidak ditemukan'
                ], 400);
            }

            $siswa = \App\Models\Siswa::with(['user' => function ($query) {
                    $query->select('id', 'siswa_id', 'foto');
                }])
                ->where('kelas_id', $kelasId)
                ->where('status_aktif', 1)
                ->orderBy('nama')
                ->select('id', 'nis', 'nisn', 'nama', 'jenis_kelamin')
                ->get()
                ->map(function ($siswa) {
                    $fotoPath = $siswa->user?->foto;
                    // fallback: if siswa.nama is empty, prefer related user.name
                    $nama = $siswa->nama;
                    if (empty($nama) && isset($siswa->user) && !empty($siswa->user->name)) {
                        $nama = $siswa->user->name;
                    }
                    $fotoUrl = null;

                    if ($fotoPath) {
                        $fotoUrl = str_contains($fotoPath, '://')
                            ? $fotoPath
                            : \Storage::disk('public')->url($fotoPath);
                    }

                    return [
                        'id' => $siswa->id,
                        'nis' => $siswa->nis,
                        'nisn' => $siswa->nisn,
                        'nama' => $nama,
                        'jenis_kelamin' => $siswa->jenis_kelamin,
                        'foto' => $fotoPath,
                        'foto_url' => $fotoUrl,
                    ];
                });

            $existing = [];
            if ($tanggal && $loadExisting) {
                // find existing absensi_kelas for this kelas and tanggal
                $absensiKelas = AbsensiKelas::with(['absensiSiswa', 'jamBelajar', 'guru'])
                    ->where('kelas_id', $kelasId)
                    ->whereDate('tanggal', $tanggal)
                    ->get();

                foreach ($absensiKelas as $ak) {
                    $jamId = $ak->jam_belajar_id;
                    $existing[$jamId] = [
                        'absensi_kelas_id' => $ak->id,
                        'guru_id' => $ak->guru_id,
                        'guru_name' => $ak->guru->nama ?? null,
                        'jam_urutan' => $ak->jamBelajar->urutan ?? null,
                        'statuses' => [],
                    ];
                    foreach ($ak->absensiSiswa as $asw) {
                        $existing[$jamId]['statuses'][$asw->siswa_id] = $asw->status;
                    }
                }

                // If the requester is Guru Piket (or Admin/Kepala), include a daily aggregated
                // snapshot so piket view can prefill statuses from combined class absensi.
                try {
                    $user = auth()->user();
                    $isAdminOrKepala = $user ? $user->hasAnyRole(['Admin', 'Kepala Sekolah']) : false;
                    $isGuruPiket = false;
                    if ($user && $user->guru_id) {
                        $hariPiketArr = (array) ($user->guru->hari_piket ?? []);
                        $todayEng = \Carbon\Carbon::parse($tanggal)->format('l');
                        $map = [
                            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
                        ];
                        $todayIndo = $map[$todayEng] ?? null;
                        $isGuruPiket = in_array($todayIndo, $hariPiketArr, true);
                    }

                    if ($isGuruPiket || $isAdminOrKepala) {
                        // build aggregated daily status per siswa using existing absensi entries
                        $dailyRows = $this->getDailyStudentReportRows($tanggal, $kelasId, DB::table('tahun_ajaran')->where('is_active',1)->first(), DB::table('semester')->where('is_active',1)->first());
                        $dailyStatuses = [];
                        foreach ($dailyRows as $dr) {
                            // map status labels to normalized values
                            $norm = strtolower((string) $dr->status);
                            if ($norm === 'hadir') $norm = 'hadir';
                            elseif (in_array($norm, ['terlambat','telat'], true)) $norm = 'terlambat';
                            elseif ($norm === 'sakit') $norm = 'sakit';
                            elseif (in_array($norm, ['izin','ijin'], true)) $norm = 'izin';
                            else $norm = 'alpa';
                            $dailyStatuses[$dr->siswa_id] = $norm;
                        }

                        // insert with a special key so front-end can prefer this (jam_urutan = 0)
                        $existing['daily'] = [
                            'absensi_kelas_id' => null,
                            'guru_id' => null,
                            'guru_name' => '(Gabungan)',
                            'jam_urutan' => 0,
                            'statuses' => $dailyStatuses,
                        ];
                    }
                } catch (\Throwable $e) {
                    // ignore aggregation errors, return per-jam data as-is
                }
            }

            return response()->json([
                'siswa' => $siswa,
                'count' => $siswa->count(),
                'existing_absensi' => $existing,
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
            ->whereIn(DB::raw('LOWER(absensi_siswa.status)'), ['terlambat', 'telat', 'alpa', 'alpha', 'alfa', 'absen', 'sakit', 'izin', 'ijin', 'hadir'])
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

    private function buildBkAttendanceStats($rows)
    {
        $normalizedRows = collect($rows)->filter(function ($row) {
            return !empty($row->siswa_id) && !empty($row->status);
        });

        $stats = [
            'hadir' => 0,
            'sakit' => 0,
            'izin' => 0,
            'terlambat' => 0,
            'tidak_hadir' => 0,
        ];

        foreach ($normalizedRows as $row) {
            $status = strtolower((string) $row->status);
            $siswaId = (int) $row->siswa_id;

            if ($status === 'hadir') {
                $stats['hadir'] += 1;
            } elseif (in_array($status, ['sakit'], true)) {
                $stats['sakit'] += 1;
            } elseif (in_array($status, ['izin', 'ijin'], true)) {
                $stats['izin'] += 1;
            } elseif (in_array($status, ['terlambat', 'telat'], true)) {
                $stats['terlambat'] += 1;
            } elseif (in_array($status, ['alpa', 'alpha', 'alfa', 'absen', 'tidak_hadir'], true)) {
                $stats['tidak_hadir'] += 1;
            }
        }

        $uniqueCounts = [
            'hadir' => $normalizedRows->filter(fn ($row) => strtolower((string) $row->status) === 'hadir')->unique('siswa_id')->count(),
            'sakit' => $normalizedRows->filter(fn ($row) => in_array(strtolower((string) $row->status), ['sakit'], true))->unique('siswa_id')->count(),
            'izin' => $normalizedRows->filter(fn ($row) => in_array(strtolower((string) $row->status), ['izin', 'ijin'], true))->unique('siswa_id')->count(),
            'terlambat' => $normalizedRows->filter(fn ($row) => in_array(strtolower((string) $row->status), ['terlambat', 'telat'], true))->unique('siswa_id')->count(),
            'tidak_hadir' => $normalizedRows->filter(fn ($row) => in_array(strtolower((string) $row->status), ['alpa', 'alpha', 'alfa', 'absen', 'tidak_hadir'], true))->unique('siswa_id')->count(),
        ];

        return $uniqueCounts;
    }

    private function getDailyStudentReportRows(string $selectedTanggal, $kelasId, $tahun, $semester, $guruId = null)
    {
        // ensure selectedTanggal is a Y-m-d string to avoid raw SQL injection/format issues
        try {
            $selectedTanggal = Carbon::parse($selectedTanggal)->format('Y-m-d');
        } catch (\Throwable $e) {
            $selectedTanggal = date('Y-m-d');
        }

        $cacheKey = $this->buildStudentReportCacheKey('daily', [
            'tanggal' => $selectedTanggal,
            'kelas_id' => $kelasId,
            'guru_id' => $guruId,
            'tahun_id' => $tahun?->id,
            'semester_id' => $semester?->id,
        ]);

        return $this->rememberStudentReportCache($cacheKey, 900, function () use ($selectedTanggal, $kelasId, $tahun, $semester, $guruId) {
            // If kelasId provided, attempt to limit to jadwal_kbm (jam) that apply to that class on the selected date
            $scheduledJamIds = null;
            if ($kelasId) {
                try {
                    $hariEnglish = Carbon::parse($selectedTanggal)->format('l');
                    $hariMap = [
                        'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                        'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
                    ];
                    $hariQuery = $hariMap[$hariEnglish] ?? $hariEnglish;

                    $tahunCond = $tahun ? $tahun->id : null;
                    $semesterCond = $semester ? $semester->id : null;

                    $scheduledJamIds = JadwalKbm::query()
                        ->where('kelas_id', $kelasId)
                        ->where('hari', $hariQuery)
                        ->when($tahunCond, fn($q) => $q->where('tahun_ajaran_id', $tahunCond))
                        ->when($semesterCond, fn($q) => $q->where('semester_id', $semesterCond))
                        ->pluck('jam_belajar_id')
                        ->unique()
                        ->values()
                        ->all();

                    if (empty($scheduledJamIds)) {
                        $scheduledJamIds = null; // fallback: don't filter
                    }
                } catch (\Throwable $e) {
                    $scheduledJamIds = null;
                }
            }
            $dailySubQuery = DB::table('absensi_siswa as abs_s')
                ->join('absensi_kelas as abs_k', 'abs_s.absensi_kelas_id', '=', 'abs_k.id')
                ->leftJoin('siswa as s', 'abs_s.siswa_id', '=', 's.id')
                ->leftJoin('kelas as k', 'abs_k.kelas_id', '=', 'k.id')
                ->leftJoin('guru as g', 'abs_k.guru_id', '=', 'g.id')
                ->whereDate('abs_k.tanggal', $selectedTanggal)
                ->when($scheduledJamIds, function ($q) use ($scheduledJamIds) {
                    $q->whereIn('abs_k.jam_belajar_id', $scheduledJamIds);
                })
                ->when($kelasId, function ($query) use ($kelasId) {
                    $query->where('abs_k.kelas_id', $kelasId);
                })
                ->when($guruId, function ($query) use ($guruId) {
                    $query->where('abs_k.guru_id', $guruId);
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
                        DB::raw("COALESCE(s.jenis_kelamin, '-') as jenis_kelamin"),
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
                    's.jenis_kelamin',
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
        });
    }

    private function getDailyStudentReportRowsPerJam(string $selectedTanggal, $kelasId, $tahun, $semester, $guruId = null)
    {
        if (!$kelasId) {
            return null;
        }

        $cacheKey = $this->buildStudentReportCacheKey('daily_jam', [
            'tanggal' => $selectedTanggal,
            'kelas_id' => $kelasId,
            'guru_id' => $guruId,
            'tahun_id' => $tahun?->id,
            'semester_id' => $semester?->id,
        ]);

        return $this->rememberStudentReportCache($cacheKey, 900, function () use ($selectedTanggal, $kelasId, $tahun, $semester, $guruId) {
            // determine scheduled jam ids for the kelas on that day
            try {
                $hariEnglish = Carbon::parse($selectedTanggal)->format('l');
                $hariMap = [
                    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
                ];
                $hariQuery = $hariMap[$hariEnglish] ?? $hariEnglish;

                $tahunCond = $tahun ? $tahun->id : null;
                $semesterCond = $semester ? $semester->id : null;

                $scheduledJamIds = JadwalKbm::query()
                    ->where('kelas_id', $kelasId)
                    ->where('hari', $hariQuery)
                    ->when($tahunCond, fn($q) => $q->where('tahun_ajaran_id', $tahunCond))
                    ->when($semesterCond, fn($q) => $q->where('semester_id', $semesterCond))
                    ->orderBy('jam_ke')
                    ->pluck('jam_belajar_id')
                    ->unique()
                    ->values()
                    ->all();
            } catch (\Throwable $e) {
                $scheduledJamIds = [];
            }

            if (empty($scheduledJamIds)) {
                return null; // fallback to default daily row builder
            }

            // fetch jam details
            $jamRows = JamBelajar::whereIn('id', $scheduledJamIds)->orderBy('urutan')->get()->keyBy('id');

            // fetch students in class
            $students = DB::table('siswa')->where('kelas_id', $kelasId)->where('status_aktif', 1)->orderBy('nama')->get();
            if ($students->isEmpty()) {
                return ['rows' => collect(), 'jamColumns' => []];
            }

            // fetch absensi entries for that date, class and scheduled jams
            $absensi = DB::table('absensi_siswa as asw')
                ->join('absensi_kelas as ak', 'asw.absensi_kelas_id', '=', 'ak.id')
                ->whereDate('ak.tanggal', $selectedTanggal)
                ->where('ak.kelas_id', $kelasId)
                ->when($guruId, function ($query) use ($guruId) {
                    $query->where('ak.guru_id', $guruId);
                })
                ->whereIn('ak.jam_belajar_id', $scheduledJamIds)
                ->select('asw.siswa_id', 'ak.jam_belajar_id', 'asw.status', 'asw.keterangan')
                ->get();

            // map absensi by siswa_id and jam_id
            $map = [];
            foreach ($absensi as $a) {
                $status = $this->normalizeAttendanceStatus($a->status) ?? null;
                $map[$a->siswa_id][$a->jam_belajar_id] = $status;
            }

            $rows = collect();
            foreach ($students as $s) {
                $statuses = [];
                foreach ($scheduledJamIds as $jid) {
                    $statuses[$jid] = $map[$s->id][$jid] ?? '-';
                }
                $rows->push((object)[
                    'siswa_id' => $s->id,
                    'nama_siswa' => $s->nama,
                    'nama_kelas' => DB::table('kelas')->where('id', $kelasId)->value('nama_kelas') ?? '-',
                    'nis' => $s->nis,
                    'nisn' => $s->nisn,
                    'statuses' => $statuses,
                ]);
            }

            // build jamColumns array with id and label
            $jamColumns = array_map(function($jid) use ($jamRows) {
                $jb = $jamRows[$jid] ?? null;
                $label = $jb ? ('Jam ke-' . ($jb->urutan ?? '?') . ' (' . ($jb->jam_mulai ?? '') . ' - ' . ($jb->jam_selesai ?? '') . ')') : ('Jam ' . $jid);
                return ['id' => $jid, 'label' => $label];
            }, $scheduledJamIds);

            return ['rows' => $rows, 'jamColumns' => $jamColumns];
        });
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
                DB::raw('SUM(CASE WHEN daily_siswa.status_rank = 5 THEN 1 ELSE 0 END) as total_alpa')
            )
            ->groupBy('daily_siswa.kelas_id')
            ->get();
    }

    private function getRangeStudentReportRows(string $startDate, string $endDate, $kelasId, $tahun, $semester, $guruId = null)
    {
        $tahunCond = $tahun ? $tahun->id : null;
        $semesterCond = $semester ? $semester->id : null;

        // count distinct dates (days with absensi_kelas records) in range for the class
        $distinctDatesQuery = DB::table('absensi_kelas')
            ->whereBetween('tanggal', [$startDate, $endDate])
            ->when($kelasId, fn($q) => $q->where('kelas_id', $kelasId))
            ->when($tahunCond, fn($q) => $q->where('tahun_ajaran_id', $tahunCond))
            ->when($semesterCond, fn($q) => $q->where('semester_id', $semesterCond));

        $distinctDates = $distinctDatesQuery->distinct()->count(DB::raw('DATE(tanggal)'));

        $cacheKey = $this->buildStudentReportCacheKey('range', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'kelas_id' => $kelasId,
            'guru_id' => $guruId,
            'tahun_id' => $tahunCond,
            'semester_id' => $semesterCond,
        ]);

        return $this->rememberStudentReportCache($cacheKey, 900, function () use ($startDate, $endDate, $kelasId, $tahunCond, $semesterCond, $guruId, $distinctDates) {
            $attendanceQuery = DB::table('absensi_siswa as abs_s')
                ->join('absensi_kelas as abs_k', 'abs_s.absensi_kelas_id', '=', 'abs_k.id')
                ->whereBetween('abs_k.tanggal', [$startDate, $endDate])
                ->when($kelasId, fn($q) => $q->where('abs_k.kelas_id', $kelasId))
                ->when($guruId, fn($q) => $q->where('abs_k.guru_id', $guruId))
                ->when($tahunCond, fn($q) => $q->where('abs_k.tahun_ajaran_id', $tahunCond))
                ->when($semesterCond, fn($q) => $q->where('abs_k.semester_id', $semesterCond))
                ->select(
                    'abs_s.siswa_id',
                    DB::raw("SUM(CASE WHEN LOWER(abs_s.status) = 'hadir' THEN 1 ELSE 0 END) as hadir_count"),
                    DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('terlambat','telat') THEN 1 ELSE 0 END) as terlambat_count"),
                    DB::raw("SUM(CASE WHEN LOWER(abs_s.status) = 'sakit' THEN 1 ELSE 0 END) as sakit_count"),
                    DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('izin','ijin') THEN 1 ELSE 0 END) as izin_count"),
                    DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 1 ELSE 0 END) as alpa_count")
                )
                ->groupBy('abs_s.siswa_id');

            $attendance = $attendanceQuery->get()->keyBy('siswa_id');

            if ($kelasId) {
                $students = DB::table('siswa')
                    ->where('kelas_id', $kelasId)
                    ->where('status_aktif', 1)
                    ->select('id','nama','nis','nisn')
                    ->orderBy('nama')
                    ->get();

                $kelasName = DB::table('kelas')->where('id', $kelasId)->value('nama_kelas');

                $rows = collect();
                foreach ($students as $s) {
                    $att = $attendance->get($s->id);
                    $hadir = $att->hadir_count ?? 0;
                    $terlambat = $att->terlambat_count ?? 0;
                    $sakit = $att->sakit_count ?? 0;
                    $izin = $att->izin_count ?? 0;
                    $alfa = $att->alpa_count ?? $att->alfa_count ?? 0;

                    $rows->push((object) [
                        'siswa_id' => $s->id,
                        'nama_siswa' => $s->nama,
                        'nama_kelas' => $kelasName ?? '-',
                        'nis' => $s->nis,
                        'nisn' => $s->nisn,
                        'hadir_count' => (int) $hadir,
                        'terlambat_count' => (int) $terlambat,
                        'sakit_count' => (int) $sakit,
                        'izin_count' => (int) $izin,
                        'alfa_count' => (int) $alfa,
                        'total_days' => (int) max(1, $distinctDates),
                    ]);
                }

                return $rows;
            }

            $attendanceAll = DB::table('absensi_siswa as abs_s')
                ->join('absensi_kelas as abs_k', 'abs_s.absensi_kelas_id', '=', 'abs_k.id')
                ->join('siswa as s', 's.id', '=', 'abs_s.siswa_id')
                ->join('kelas as k', 'k.id', '=', 's.kelas_id')
                ->whereBetween('abs_k.tanggal', [$startDate, $endDate])
                ->when($tahunCond, fn($q) => $q->where('abs_k.tahun_ajaran_id', $tahunCond))
                ->when($semesterCond, fn($q) => $q->where('abs_k.semester_id', $semesterCond))
                ->when($guruId, fn($q) => $q->where('abs_k.guru_id', $guruId))
                ->where('s.status_aktif', 1)
                ->select(
                    's.id as siswa_id',
                    's.nama as nama_siswa',
                    's.nis',
                    's.nisn',
                    'k.nama_kelas',
                    DB::raw("SUM(CASE WHEN LOWER(abs_s.status) = 'hadir' THEN 1 ELSE 0 END) as hadir_count"),
                    DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('terlambat','telat') THEN 1 ELSE 0 END) as terlambat_count"),
                    DB::raw("SUM(CASE WHEN LOWER(abs_s.status) = 'sakit' THEN 1 ELSE 0 END) as sakit_count"),
                    DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('izin','ijin') THEN 1 ELSE 0 END) as izin_count"),
                    DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 1 ELSE 0 END) as alpa_count")
                )
                ->groupBy('s.id','s.nama','s.nis','s.nisn','k.nama_kelas')
                ->orderBy('s.nama')
                ->get();

            $rowsAll = collect();
            foreach ($attendanceAll as $att) {
                $rowsAll->push((object) [
                    'siswa_id' => $att->siswa_id,
                    'nama_siswa' => $att->nama_siswa,
                    'nama_kelas' => $att->nama_kelas ?? '-',
                    'nis' => $att->nis,
                    'nisn' => $att->nisn,
                    'hadir_count' => (int) ($att->hadir_count ?? 0),
                    'terlambat_count' => (int) ($att->terlambat_count ?? 0),
                    'sakit_count' => (int) ($att->sakit_count ?? 0),
                    'izin_count' => (int) ($att->izin_count ?? 0),
                    'alpa_count' => (int) ($att->alpa_count ?? 0),
                    'total_days' => (int) max(1, $distinctDates),
                ]);
            }

            return $rowsAll;
        });

        if ($kelasId) {
            $students = DB::table('siswa')
                ->where('kelas_id', $kelasId)
                ->where('status_aktif', 1)
                ->select('id','nama','nis','nisn')
                ->orderBy('nama')
                ->get();

            $kelasName = DB::table('kelas')->where('id', $kelasId)->value('nama_kelas');

            $rows = collect();
            foreach ($students as $s) {
                $att = $attendance->get($s->id);
                $hadir = $att->hadir_count ?? 0;
                $terlambat = $att->terlambat_count ?? 0;
                $sakit = $att->sakit_count ?? 0;
                $izin = $att->izin_count ?? 0;
                $alfa = $att->alpa_count ?? $att->alfa_count ?? 0;

                $rows->push((object) [
                    'siswa_id' => $s->id,
                    'nama_siswa' => $s->nama,
                    'nama_kelas' => $kelasName ?? '-',
                    'nis' => $s->nis,
                    'nisn' => $s->nisn,
                    'hadir_count' => (int) $hadir,
                    'terlambat_count' => (int) $terlambat,
                    'sakit_count' => (int) $sakit,
                    'izin_count' => (int) $izin,
                    'alfa_count' => (int) $alfa,
                    'total_days' => (int) max(1, $distinctDates),
                ]);
            }

            return $rows;
        }

        $attendanceAll = DB::table('absensi_siswa as abs_s')
            ->join('absensi_kelas as abs_k', 'abs_s.absensi_kelas_id', '=', 'abs_k.id')
            ->join('siswa as s', 's.id', '=', 'abs_s.siswa_id')
            ->join('kelas as k', 'k.id', '=', 's.kelas_id')
            ->whereBetween('abs_k.tanggal', [$startDate, $endDate])
            ->when($tahunCond, fn($q) => $q->where('abs_k.tahun_ajaran_id', $tahunCond))
            ->when($semesterCond, fn($q) => $q->where('abs_k.semester_id', $semesterCond))
            ->when($guruId, fn($q) => $q->where('abs_k.guru_id', $guruId))
            ->where('s.status_aktif', 1)
            ->select(
                's.id as siswa_id',
                's.nama as nama_siswa',
                's.nis',
                's.nisn',
                'k.nama_kelas',
                DB::raw("SUM(CASE WHEN LOWER(abs_s.status) = 'hadir' THEN 1 ELSE 0 END) as hadir_count"),
                DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('terlambat','telat') THEN 1 ELSE 0 END) as terlambat_count"),
                DB::raw("SUM(CASE WHEN LOWER(abs_s.status) = 'sakit' THEN 1 ELSE 0 END) as sakit_count"),
                DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('izin','ijin') THEN 1 ELSE 0 END) as izin_count"),
                DB::raw("SUM(CASE WHEN LOWER(abs_s.status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 1 ELSE 0 END) as alpa_count")
            )
            ->groupBy('s.id','s.nama','s.nis','s.nisn','k.nama_kelas')
            ->orderBy('s.nama')
            ->get();

        $rowsAll = collect();
        foreach ($attendanceAll as $att) {
            $rowsAll->push((object) [
                'siswa_id' => $att->siswa_id,
                'nama_siswa' => $att->nama_siswa,
                'nama_kelas' => $att->nama_kelas ?? '-',
                'nis' => $att->nis,
                'nisn' => $att->nisn,
                'hadir_count' => (int) ($att->hadir_count ?? 0),
                'terlambat_count' => (int) ($att->terlambat_count ?? 0),
                'sakit_count' => (int) ($att->sakit_count ?? 0),
                'izin_count' => (int) ($att->izin_count ?? 0),
                'alpa_count' => (int) ($att->alpa_count ?? 0),
                'total_days' => (int) max(1, $distinctDates),
            ]);
        }

        return $rowsAll;
    }

    private function buildStudentReportCacheKey(string $type, array $params): string
    {
        ksort($params);
        return 'absensi:laporan_siswa:' . $type . ':' . sha1(json_encode($params));
    }

    private function rememberStudentReportCache(string $cacheKey, int $ttlSeconds, \Closure $callback)
    {
        $store = Cache::getStore();
        if (method_exists($store, 'supportsTags') && $store->supportsTags()) {
            return Cache::tags(['absensi_laporan_siswa'])->remember($cacheKey, $ttlSeconds, $callback);
        }

        return Cache::remember($cacheKey, $ttlSeconds, $callback);
    }

    private function generateVerificationCode(string $guruKode = null): string
    {
        $prefix = strtoupper(trim($guruKode ?? '')) ?: 'GURU';
        $random = strtoupper(Str::random(3));
        return $prefix . $random;
    }

    private function buildVerificationRecordValues(string $code, Carbon $expiresAtUtc, array $validated): array
    {
        $values = [
            'kode' => $code,
            'expires_at' => $expiresAtUtc,
        ];

        if ($this->attendanceVerificationSupportsTimeRange()) {
            $values['valid_from'] = $validated['verification_valid_from'] ?? null;
            $values['valid_to'] = $validated['verification_valid_to'] ?? null;
        }

        return $values;
    }

    private function attendanceVerificationSupportsTimeRange(): bool
    {
        return Schema::hasTable('attendance_verification_codes')
            && Schema::hasColumn('attendance_verification_codes', 'valid_from')
            && Schema::hasColumn('attendance_verification_codes', 'valid_to');
    }

    private function flushStudentReportCache(): void
    {
        try {
            $store = Cache::getStore();
            if (method_exists($store, 'supportsTags') && $store->supportsTags()) {
                Cache::tags(['absensi_laporan_siswa'])->flush();
            }
        } catch (\Throwable $e) {
            Log::warning('absensi.cache.flush_failed', ['error' => $e->getMessage()]);
        }
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
