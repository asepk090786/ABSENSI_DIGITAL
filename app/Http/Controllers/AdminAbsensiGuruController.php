<?php

namespace App\Http\Controllers;

use App\Models\AbsensiGuru;
use App\Models\AbsensiGuruLog;
use App\Models\AgendaKelas;
use App\Models\Guru;
use App\Models\JadwalKbm;
use App\Services\SettingsManager;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AdminAbsensiGuruController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            $isPiketRoute = $request->routeIs('piket.absensi_guru.*');
            $isPersonalReportRoute = $request->routeIs('guru.absensi_guru.laporan');
            $hariMap = [
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu',
            ];
            $hariIni = $hariMap[Carbon::now()->format('l')] ?? null;
            $guruPiketHariIni = $user->guru
                && in_array($hariIni, (array) ($user->guru->hari_piket ?? []), true);
            if ($isPersonalReportRoute) {
                $allowed = $user->hasAnyRole(['Guru', 'Guru Mapel', 'Guru Kelas', 'Wali Kelas', 'Guru BK', 'Guru Piket', 'Pembina'])
                    && (bool) $user->guru_id;
            } elseif ($isPiketRoute) {
                $allowed = $user->hasRole('Guru Piket') || $guruPiketHariIni;
            } else {
                $allowed = $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);
            }

            abort_unless($allowed, 403);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::now()->format('Y-m-d'));
        $searchGuru = $request->input('search_guru', '');

        // Query guru dengan filter
        $guruQuery = Guru::with('user')
            ->where('is_active', 1);

        if ($searchGuru) {
            $guruQuery->where(function ($q) use ($searchGuru) {
                $q->where('nama', 'like', "%{$searchGuru}%")
                    ->orWhere('nip', 'like', "%{$searchGuru}%");
            });
        }

        $guru = $guruQuery->orderBy('nama')->get();

        // Ambil tahun dan semester aktif
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        // Siapkan indikator agenda untuk setiap jadwal guru pada tanggal absensi.
        $hariMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];
        $tanggalCarbon = Carbon::parse($tanggal);
        $jadwalGuru = JadwalKbm::with(['kelas', 'jamBelajar'])
            ->whereIn('guru_id', $guru->pluck('id'))
            ->where('hari', $hariMap[$tanggalCarbon->format('l')] ?? $tanggalCarbon->format('l'))
            ->when($tahun, fn ($query) => $query->where('tahun_ajaran_id', $tahun->id))
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id))
            ->orderBy('jam_ke')
            ->get();

        $agendaHariIni = AgendaKelas::whereDate('tanggal', $tanggal)
            ->when($tahun, fn ($query) => $query->where('tahun_ajaran_id', $tahun->id))
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id))
            ->get()
            ->keyBy(fn ($agenda) => $agenda->guru_id . '-' . $agenda->kelas_id . '-' . $agenda->jam_belajar_id);

        $waktuSekarang = Carbon::now();
        $jadwalPerGuru = $jadwalGuru->groupBy('guru_id')->map(function ($jadwalItems) use ($agendaHariIni, $tanggalCarbon, $waktuSekarang) {
            return $jadwalItems->map(function ($jadwal) use ($agendaHariIni, $tanggalCarbon, $waktuSekarang) {
                $agendaKey = $jadwal->guru_id . '-' . $jadwal->kelas_id . '-' . $jadwal->jam_belajar_id;
                $agendaTerisi = $agendaHariIni->has($agendaKey);
                $jamMulai = $jadwal->jamBelajar?->jam_mulai;
                $waktuMulai = $jamMulai ? Carbon::parse($tanggalCarbon->format('Y-m-d') . ' ' . $jamMulai) : null;
                $batasHariIni = $tanggalCarbon->isSameDay($waktuSekarang);
                $belumWaktunya = $tanggalCarbon->isFuture() || ($batasHariIni && $waktuMulai && $waktuSekarang->lt($waktuMulai));

                return [
                    'kelas' => $jadwal->kelas?->nama_kelas ?? 'Kelas',
                    'jam' => trim(($jadwal->jamBelajar?->jam_mulai ?? '-') . ' - ' . ($jadwal->jamBelajar?->jam_selesai ?? '-')),
                    'status' => $agendaTerisi ? 'hadir' : ($belumWaktunya ? 'menunggu' : 'tidak_hadir'),
                    'label' => $agendaTerisi ? 'Masuk Kelas' : ($belumWaktunya ? 'Belum KBM' : 'Tidak Masuk Kelas'),
                ];
            });
        });

        // Ambil absensi untuk tanggal yang dipilih
        $absensiQuery = AbsensiGuru::whereDate('tanggal', $tanggal);
        $showAbsensiAudit = !$request->routeIs('piket.absensi_guru.*');
        if ($showAbsensiAudit) {
            $absensiQuery->with(['logs', 'pencatat']);
        }
        $absensiHariIni = $absensiQuery->get()->keyBy('guru_id');
        $absensiLocks = DB::table('absensi_guru_locks')
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('guru_id');
        $activeGuruIds = Guru::where('is_active', 1)->pluck('id');
        $totalGuruAktif = $activeGuruIds->count();
        $guruTerkunciAktif = $absensiLocks->keys()->intersect($activeGuruIds)->count();
        $allGuruLocked = $totalGuruAktif > 0 && $guruTerkunciAktif === $totalGuruAktif;

        // Hitung rekap statistik
        $rekapCount = $absensiHariIni->groupBy('status')->map(function ($items) {
            return $items->count();
        })->toArray();

        $totalGuru = $guru->count();
        $sudahAbsensi = $absensiHariIni->count();
        $belumAbsensi = $totalGuru - $sudahAbsensi;
        $routePrefix = $this->routePrefix($request);
        $canManageLocks = $request->routeIs('admin.absensi_guru.*')
            && $request->user()->hasRole('Admin');
        $piketPastDateBlocked = $request->routeIs('piket.absensi_guru.*')
            && (bool) app(SettingsManager::class)->get('attendance.block_past_dates_for_guru_piket', false)
            && $tanggalCarbon->lt(Carbon::today());

        return view('admin.absensi_guru.index', compact(
            'guru',
            'tanggal',
            'searchGuru',
            'absensiHariIni',
            'tahun',
            'semester',
            'rekapCount',
            'jadwalPerGuru',
            'totalGuru',
            'sudahAbsensi',
            'belumAbsensi',
            'routePrefix',
            'showAbsensiAudit',
            'absensiLocks',
            'canManageLocks',
            'allGuruLocked',
            'totalGuruAktif',
            'piketPastDateBlocked'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'guru_id' => 'required|exists:guru,id',
            'status' => 'required|in:hadir,tidak_hadir,izin,sakit,dinas_luar',
            'tanggal_mulai' => 'nullable|required_with:tanggal_selesai|date',
            'tanggal_selesai' => 'nullable|required_with:tanggal_mulai|date|after_or_equal:tanggal_mulai',
            'keterangan' => 'nullable|string|max:255',
            'bukti' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $hasDateRange = ! empty($validated['tanggal_mulai']) && ! empty($validated['tanggal_selesai']);
        if ($hasDateRange && $request->routeIs('piket.absensi_guru.*')) {
            throw ValidationException::withMessages([
                'tanggal_mulai' => 'Rentang tanggal hanya dapat diisi melalui halaman Admin.',
            ]);
        }
        if ($hasDateRange && ! in_array($validated['status'], ['izin', 'sakit', 'dinas_luar'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Rentang tanggal hanya dapat digunakan untuk status Izin, Sakit, atau Dinas Luar.',
            ]);
        }
        $tanggalMulai = $validated['tanggal_mulai'] ?? $validated['tanggal'];
        $tanggalSelesai = $validated['tanggal_selesai'] ?? $validated['tanggal'];
        $tanggalAbsensi = [];
        $tanggalBerjalan = Carbon::parse($tanggalMulai)->startOfDay();
        $tanggalAkhir = Carbon::parse($tanggalSelesai)->startOfDay();
        while ($tanggalBerjalan->lte($tanggalAkhir)) {
            $tanggalAbsensi[] = $tanggalBerjalan->toDateString();
            $tanggalBerjalan->addDay();
        }

        $this->ensurePiketCanChangeAttendance($request, [$validated['guru_id']], $tanggalAbsensi[0]);
        $this->validateEvidenceStatus($request, $validated['status']);

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        $adminId = auth()->user()->guru?->id;
        $newEvidencePaths = [];

        try {
            if ($request->hasFile('bukti') && ! $hasDateRange) {
                $newEvidencePaths[] = $request->file('bukti')->store('absensi_guru/bukti', 'local');
                if (! $newEvidencePaths[0]) {
                    throw new \RuntimeException('Bukti absensi gagal disimpan.');
                }
            }

            $removedEvidencePaths = DB::transaction(function () use ($validated, $tanggalAbsensi, $hasDateRange, $tahun, $semester, $adminId, $request, &$newEvidencePaths) {
                $values = [
                    'pencatat_guru_id' => $adminId,
                    'status' => $validated['status'],
                    'keterangan' => $validated['keterangan'] ?? null,
                    'tahun_ajaran_id' => $tahun->id ?? null,
                    'semester_id' => $semester->id ?? null,
                ];

                $removedEvidencePaths = [];
                foreach ($tanggalAbsensi as $tanggalAbsensiSatuHari) {
                    $valuesUntukTanggal = $values;
                    if ($request->hasFile('bukti')) {
                        $evidencePath = $hasDateRange
                            ? $request->file('bukti')->store('absensi_guru/bukti', 'local')
                            : $newEvidencePaths[0];
                        if (! $evidencePath) {
                            throw new \RuntimeException('Bukti absensi gagal disimpan.');
                        }
                        if ($hasDateRange) {
                            $newEvidencePaths[] = $evidencePath;
                        }
                        $valuesUntukTanggal['bukti_path'] = $evidencePath;
                        $valuesUntukTanggal['bukti_token'] = (string) Str::uuid();
                    }

                    [$absensi, $oldStatus, $removedEvidencePath] = $this->saveAttendance(
                        [
                            'guru_id' => $validated['guru_id'],
                            'tanggal' => $tanggalAbsensiSatuHari,
                        ],
                        $valuesUntukTanggal
                    );
                    $this->recordAdminAudit($request, $absensi, $oldStatus);

                    if ($removedEvidencePath) {
                        $removedEvidencePaths[] = $removedEvidencePath;
                    }
                }

                if ($hasDateRange) {
                    $now = now();
                    $locks = collect($tanggalAbsensi)->map(fn (string $tanggalAbsensiSatuHari) => [
                        'guru_id' => $validated['guru_id'],
                        'tanggal' => $tanggalAbsensiSatuHari,
                        'locked_by_user_id' => $request->user()->id,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ])->all();
                    DB::table('absensi_guru_locks')->upsert(
                        $locks,
                        ['guru_id', 'tanggal'],
                        ['locked_by_user_id', 'updated_at']
                    );
                }

                return $removedEvidencePaths;
            });
        } catch (\Exception $e) {
            foreach ($newEvidencePaths as $path) {
                $this->deleteEvidenceFile($path);
            }

            return redirect()->back()
                ->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }

        foreach ($removedEvidencePaths as $path) {
            $this->deleteEvidenceFile($path);
        }

        $tanggalRedirect = $hasDateRange ? $tanggalMulai : $validated['tanggal'];
        $successMessage = $hasDateRange
            ? 'Absensi guru berhasil disimpan dan dikunci dari ' . Carbon::parse($tanggalMulai)->format('d/m/Y') . ' sampai ' . Carbon::parse($tanggalSelesai)->format('d/m/Y') . '.'
            : 'Absensi guru berhasil disimpan.';

        return redirect()->route($this->routeName($request, 'index'), ['tanggal' => $tanggalRedirect])
            ->with('success', $successMessage);
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'attendance' => 'required|array|min:1',
            'attendance.*.status' => 'nullable|in:hadir,tidak_hadir,izin,sakit,dinas_luar',
            'attendance.*.keterangan' => 'nullable|string|max:255',
        ]);

        $guruIds = collect($validated['attendance'])
            ->filter(fn ($item) => ! empty($item['status']))
            ->keys()
            ->map(fn ($id) => (int) $id)
            ->all();
        $this->ensurePiketCanChangeAttendance($request, $guruIds, $validated['tanggal']);

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        $adminId = auth()->user()->guru?->id;

        try {
            $removedEvidencePaths = DB::transaction(function () use ($validated, $tahun, $semester, $adminId, $request) {
                $removedEvidencePaths = [];
                foreach ($validated['attendance'] as $guruId => $item) {
                    $status = $item['status'] ?? null;

                    if (!$status) {
                        continue;
                    }
                    [$absensi, $oldStatus, $removedEvidencePath] = $this->saveAttendance(
                        [
                            'guru_id' => (int) $guruId,
                            'tanggal' => $validated['tanggal'],
                        ],
                        [
                            'pencatat_guru_id' => $adminId,
                            'status' => $status,
                            'keterangan' => $item['keterangan'] ?? null,
                            'tahun_ajaran_id' => $tahun->id ?? null,
                            'semester_id' => $semester->id ?? null,
                        ]
                    );
                    $this->recordAdminAudit($request, $absensi, $oldStatus);
                    if ($removedEvidencePath) {
                        $removedEvidencePaths[] = $removedEvidencePath;
                    }
                }

                return $removedEvidencePaths;
            });
            foreach ($removedEvidencePaths as $path) {
                $this->deleteEvidenceFile($path);
            }

            return redirect()->route($this->routeName($request, 'index'), ['tanggal' => $validated['tanggal']])
                ->with('success', 'Absensi guru berhasil disimpan untuk semua guru.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }
    }

    public function hadirSemua(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
        ]);
        $this->ensurePiketCanUseAttendanceDate($request, $validated['tanggal']);

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        $adminId = auth()->user()->guru?->id;

        $guruIds = Guru::where('is_active', 1)->pluck('id');
        $lockedGuruIds = $this->lockedGuruIds($guruIds, $validated['tanggal']);
        if ($request->routeIs('piket.absensi_guru.*')) {
            $guruIds = $guruIds->diff($lockedGuruIds);
        }

        if ($guruIds->isEmpty()) {
            return redirect()->route($this->routeName($request, 'index'), ['tanggal' => $validated['tanggal']])
                ->with('error', 'Tidak ada absensi yang diubah karena semua guru aktif terkunci oleh Admin.');
        }

        try {
            $removedEvidencePaths = DB::transaction(function () use ($guruIds, $validated, $tahun, $semester, $adminId, $request) {
                $removedEvidencePaths = [];
                $guruIds->each(function ($guruId) use ($validated, $tahun, $semester, $adminId, $request, &$removedEvidencePaths) {
                    [$absensi, $oldStatus, $removedEvidencePath] = $this->saveAttendance(
                        [
                            'guru_id' => $guruId,
                            'tanggal' => $validated['tanggal'],
                        ],
                        [
                            'pencatat_guru_id' => $adminId,
                            'status' => 'hadir',
                            'keterangan' => null,
                            'tahun_ajaran_id' => $tahun->id ?? null,
                            'semester_id' => $semester->id ?? null,
                        ]
                    );
                    $this->recordAdminAudit($request, $absensi, $oldStatus);
                    if ($removedEvidencePath) {
                        $removedEvidencePaths[] = $removedEvidencePath;
                    }
                });

                return $removedEvidencePaths;
            });
            foreach ($removedEvidencePaths as $path) {
                $this->deleteEvidenceFile($path);
            }

            $redirect = redirect()->route($this->routeName($request, 'index'), ['tanggal' => $validated['tanggal']]);
            if ($request->routeIs('piket.absensi_guru.*') && $lockedGuruIds->isNotEmpty()) {
                return $redirect->with('warning', 'Guru terkunci Admin dilewati; guru lainnya ditandai hadir.');
            }

            return $redirect->with('success', 'Absensi berhasil disimpan sebagai hadir untuk semua guru aktif yang tidak terkunci.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyimpan hadir semua: ' . $e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'guru_id' => 'required|exists:guru,id',
        ]);

        $this->ensurePiketCanChangeAttendance($request, [$validated['guru_id']], $validated['tanggal']);

        try {
            $absensi = AbsensiGuru::where('guru_id', $validated['guru_id'])
                ->where('tanggal', $validated['tanggal'])
                ->first();
            if ($absensi) {
                $evidencePath = $absensi->bukti_path;
                $absensi->delete();
                $this->deleteEvidenceFile($evidencePath);
            }

            return redirect()->route($this->routeName($request, 'index'), ['tanggal' => $validated['tanggal']])
                ->with('success', 'Absensi guru berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus absensi: ' . $e->getMessage());
        }
    }

    public function toggleLock(Request $request)
    {
        abort_unless(
            $request->routeIs('admin.absensi_guru.lock') && $request->user()->hasRole('Admin'),
            403
        );

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'guru_id' => 'required|exists:guru,id',
            'locked' => 'required|boolean',
        ]);

        $lock = DB::table('absensi_guru_locks')
            ->where('guru_id', $validated['guru_id'])
            ->whereDate('tanggal', $validated['tanggal']);

        if ($validated['locked']) {
            DB::table('absensi_guru_locks')->updateOrInsert(
                ['guru_id' => $validated['guru_id'], 'tanggal' => $validated['tanggal']],
                [
                    'locked_by_user_id' => $request->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        } else {
            $lock->delete();
        }

        return redirect()->route('admin.absensi_guru.index', ['tanggal' => $validated['tanggal']])
            ->with('success', $validated['locked'] ? 'Input absensi guru berhasil dikunci untuk Guru Piket.' : 'Kunci input absensi berhasil dibuka.');
    }

    public function toggleAllLocks(Request $request)
    {
        abort_unless(
            $request->routeIs('admin.absensi_guru.lock_all') && $request->user()->hasRole('Admin'),
            403
        );

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'locked' => 'required|boolean',
        ]);
        $tanggal = $validated['tanggal'];
        $guruIds = Guru::where('is_active', 1)->pluck('id');

        DB::transaction(function () use ($validated, $tanggal, $guruIds, $request) {
            $locks = DB::table('absensi_guru_locks')->whereDate('tanggal', $tanggal);

            if ($validated['locked']) {
                $now = now();
                $rows = $guruIds->map(fn ($guruId) => [
                    'guru_id' => $guruId,
                    'tanggal' => $tanggal,
                    'locked_by_user_id' => $request->user()->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all();

                foreach (array_chunk($rows, 500) as $chunk) {
                    DB::table('absensi_guru_locks')->upsert(
                        $chunk,
                        ['guru_id', 'tanggal'],
                        ['locked_by_user_id', 'updated_at']
                    );
                }
            } else {
                $locks->delete();
            }
        });

        return redirect()->route('admin.absensi_guru.index', ['tanggal' => $tanggal])
            ->with('success', $validated['locked']
                ? "Input absensi berhasil dikunci untuk {$guruIds->count()} guru aktif."
                : 'Semua kunci input absensi pada tanggal ini berhasil dibuka.');
    }

    public function laporan(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $isPersonalReport = $request->routeIs('guru.absensi_guru.laporan');
        $guruId = $isPersonalReport ? auth()->user()->guru_id : $request->input('guru_id');

        $guru = $isPersonalReport
            ? collect()
            : Guru::where('is_active', 1)->orderBy('nama')->get();
        $guruUntukRekap = Guru::with('user')
            ->where('is_active', 1)
            ->when($isPersonalReport || $request->filled('guru_id'), fn ($query) => $query->whereKey($guruId))
            ->orderBy('nama')
            ->get();

        $query = AbsensiGuru::whereNotNull('status')
            ->whereIn('guru_id', $guruUntukRekap->pluck('id'));

        if ($startDate) {
            $query->whereDate('tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('tanggal', '<=', $endDate);
        }

        $absensiPerGuru = $query->get()->groupBy('guru_id');
        $rekapAbsensi = $guruUntukRekap->map(function (Guru $item) use ($absensiPerGuru) {
            $catatan = $absensiPerGuru->get($item->id, collect());
            $counts = $catatan->countBy('status');
            $keteranganByStatus = $catatan
                ->filter(fn (AbsensiGuru $absensi) => filled($absensi->keterangan))
                ->groupBy('status')
                ->map(fn ($records) => $records->pluck('keterangan')->unique()->implode('; '));
            $buktiByStatus = $catatan
                ->filter(fn (AbsensiGuru $absensi) => filled($absensi->bukti_token))
                ->groupBy('status')
                ->map(fn ($records) => $records
                    ->map(fn (AbsensiGuru $absensi) => route('public.absensi-guru.evidence', $absensi->bukti_token))
                    ->unique()
                    ->values());
            $totalKehadiran = $catatan->count();

            return (object) [
                'guru' => $item,
                'hadir' => $counts->get('hadir', 0),
                'sakit' => $counts->get('sakit', 0),
                'izin' => $counts->get('izin', 0),
                'tanpa' => $counts->get('tidak_hadir', 0),
                'dinas_luar' => $counts->get('dinas_luar', 0),
                'keterangan_hadir' => $keteranganByStatus->get('hadir', '-'),
                'keterangan_sakit' => $keteranganByStatus->get('sakit', '-'),
                'keterangan_izin' => $keteranganByStatus->get('izin', '-'),
                'keterangan_tanpa' => $keteranganByStatus->get('tidak_hadir', '-'),
                'keterangan_dinas_luar' => $keteranganByStatus->get('dinas_luar', '-'),
                'bukti_hadir' => $buktiByStatus->get('hadir', collect()),
                'bukti_sakit' => $buktiByStatus->get('sakit', collect()),
                'bukti_izin' => $buktiByStatus->get('izin', collect()),
                'bukti_tanpa' => $buktiByStatus->get('tidak_hadir', collect()),
                'bukti_dinas_luar' => $buktiByStatus->get('dinas_luar', collect()),
                'tanggal_sakit' => $catatan->where('status', 'sakit')
                    ->sortBy('tanggal')
                    ->map(fn (AbsensiGuru $absensi) => $absensi->tanggal->format('d/m/Y'))
                    ->implode(', ') ?: '-',
                'tanggal_izin' => $catatan->where('status', 'izin')
                    ->sortBy('tanggal')
                    ->map(fn (AbsensiGuru $absensi) => $absensi->tanggal->format('d/m/Y'))
                    ->implode(', ') ?: '-',
                'tanggal_dinas_luar' => $catatan->where('status', 'dinas_luar')
                    ->sortBy('tanggal')
                    ->map(fn (AbsensiGuru $absensi) => $absensi->tanggal->format('d/m/Y'))
                    ->implode(', ') ?: '-',
                'rincian_ketidakhadiran' => $catatan
                    ->filter(fn (AbsensiGuru $absensi) => in_array($absensi->status, ['sakit', 'izin', 'tidak_hadir', 'dinas_luar'], true))
                    ->sortBy('tanggal')
                    ->map(fn (AbsensiGuru $absensi) => (object) [
                        'status' => match ($absensi->status) {
                            'sakit' => 'Sakit',
                            'izin' => 'Izin',
                            'tidak_hadir' => 'Tanpa Keterangan',
                            'dinas_luar' => 'Dinas Luar',
                        },
                        'tanggal' => $absensi->tanggal->format('d/m/Y'),
                        'keterangan' => $absensi->keterangan ?: '-',
                        'bukti_url' => $absensi->bukti_token
                            ? route('public.absensi-guru.evidence', $absensi->bukti_token)
                            : null,
                    ])
                    ->values(),
                'persentase' => $totalKehadiran > 0
                    ? round(($counts->get('hadir', 0) / $totalKehadiran) * 100, 1)
                    : 0,
            ];
        })->values();

        $routePrefix = $this->routePrefix($request);

        return view('admin.absensi_guru.laporan', compact(
            'rekapAbsensi',
            'guru',
            'startDate',
            'endDate',
            'guruId',
            'isPersonalReport',
            'routePrefix'
        ));
    }

    private function routePrefix(Request $request): string
    {
        return $request->routeIs('piket.absensi_guru.*')
            ? 'piket.absensi_guru'
            : 'admin.absensi_guru';
    }

    private function recordAdminAudit(Request $request, AbsensiGuru $absensi, ?string $oldStatus): void
    {
        if ($request->routeIs('piket.absensi_guru.*')) {
            return;
        }

        $user = $request->user();
        AbsensiGuruLog::create([
            'absensi_guru_id' => $absensi->id,
            'user_id' => $user?->id,
            'actor_name' => $user?->guru?->nama ?: ($user?->name ?? 'Pengguna'),
            'old_status' => $oldStatus,
            'new_status' => $absensi->status,
        ]);
    }

    private function saveAttendance(array $keys, array $values): array
    {
        $existing = AbsensiGuru::where('guru_id', $keys['guru_id'])
            ->whereDate('tanggal', $keys['tanggal'])
            ->first();
        $oldStatus = $existing?->status;
        $removedEvidencePath = null;
        $hasNewEvidence = array_key_exists('bukti_path', $values);
        $evidenceAllowed = in_array($values['status'], ['izin', 'sakit', 'dinas_luar'], true);

        if ($hasNewEvidence && $existing?->bukti_path !== $values['bukti_path']) {
            $removedEvidencePath = $existing?->bukti_path;
        }
        if (! $evidenceAllowed && $existing?->bukti_path) {
            $removedEvidencePath = $existing->bukti_path;
            $values['bukti_path'] = null;
            $values['bukti_token'] = null;
        }

        $absensi = $existing ?? new AbsensiGuru($keys);
        $absensi->fill($values)->save();

        return [$absensi, $oldStatus, $removedEvidencePath];
    }

    private function ensurePiketCanChangeAttendance(Request $request, array $guruIds, string $tanggal): void
    {
        if (! $request->routeIs('piket.absensi_guru.*') || $guruIds === []) {
            return;
        }

        $this->ensurePiketCanUseAttendanceDate($request, $tanggal);

        $lockedIds = $this->lockedGuruIds($guruIds, $tanggal);
        abort_if(
            $lockedIds->isNotEmpty(),
            403,
            'Absensi guru ini dikunci oleh Admin untuk tanggal yang dipilih.'
        );
    }

    private function ensurePiketCanUseAttendanceDate(Request $request, string $tanggal): void
    {
        if (
            $request->routeIs('piket.absensi_guru.*')
            && (bool) app(SettingsManager::class)->get('attendance.block_past_dates_for_guru_piket', false)
            && Carbon::parse($tanggal)->startOfDay()->lt(Carbon::today())
        ) {
            abort(403, 'Pengaturan Admin memblokir perubahan absensi Guru Piket untuk tanggal yang sudah terlewat.');
        }
    }

    private function lockedGuruIds(iterable $guruIds, string $tanggal)
    {
        return DB::table('absensi_guru_locks')
            ->whereDate('tanggal', $tanggal)
            ->whereIn('guru_id', collect($guruIds))
            ->pluck('guru_id');
    }

    private function validateEvidenceStatus(Request $request, string $status): void
    {
        if ($request->hasFile('bukti') && ! in_array($status, ['izin', 'sakit', 'dinas_luar'], true)) {
            throw ValidationException::withMessages([
                'bukti' => 'Bukti hanya dapat diunggah untuk status Izin, Sakit, atau Dinas Luar.',
            ]);
        }
    }

    private function deleteEvidenceFile(?string $path): void
    {
        if ($path && Storage::disk('local')->exists($path) && ! Storage::disk('local')->delete($path)) {
            Log::error('Gagal menghapus berkas bukti absensi guru.', ['path' => $path]);
        }
    }

    private function routeName(Request $request, string $action): string
    {
        return $this->routePrefix($request) . '.' . $action;
    }
}
