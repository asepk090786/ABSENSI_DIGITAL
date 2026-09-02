<?php

namespace App\Http\Controllers;

use App\Models\AbsensiGuru;
use App\Models\AgendaKelas;
use App\Models\Guru;
use App\Models\JadwalKbm;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAbsensiGuruController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']), 403);
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
        $absensiHariIni = AbsensiGuru::where('tanggal', $tanggal)
            ->get()
            ->keyBy('guru_id');

        // Hitung rekap statistik
        $rekapCount = $absensiHariIni->groupBy('status')->map(function ($items) {
            return $items->count();
        })->toArray();

        $totalGuru = $guru->count();
        $sudahAbsensi = $absensiHariIni->count();
        $belumAbsensi = $totalGuru - $sudahAbsensi;

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
            'belumAbsensi'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'guru_id' => 'required|exists:guru,id',
            'status' => 'required|in:hadir,tidak_hadir,izin,sakit',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        $adminId = auth()->user()->guru?->id;

        try {
            DB::transaction(function () use ($validated, $tahun, $semester, $adminId) {
                AbsensiGuru::updateOrCreate(
                    [
                        'guru_id' => $validated['guru_id'],
                        'tanggal' => $validated['tanggal'],
                    ],
                    [
                        'pencatat_guru_id' => $adminId,
                        'status' => $validated['status'],
                        'keterangan' => $validated['keterangan'] ?? null,
                        'tahun_ajaran_id' => $tahun->id ?? null,
                        'semester_id' => $semester->id ?? null,
                    ]
                );
            });

            return redirect()->route('admin.absensi_guru.index', ['tanggal' => $validated['tanggal']])
                ->with('success', 'Absensi guru berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyimpan absensi: ' . $e->getMessage());
        }
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'attendance' => 'required|array|min:1',
            'attendance.*.status' => 'nullable|in:hadir,tidak_hadir,izin,sakit',
            'attendance.*.keterangan' => 'nullable|string|max:255',
        ]);

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        $adminId = auth()->user()->guru?->id;

        try {
            DB::transaction(function () use ($validated, $tahun, $semester, $adminId) {
                foreach ($validated['attendance'] as $guruId => $item) {
                    $status = $item['status'] ?? null;

                    if (!$status) {
                        continue;
                    }

                    AbsensiGuru::updateOrCreate(
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
                }
            });

            return redirect()->route('admin.absensi_guru.index', ['tanggal' => $validated['tanggal']])
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

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        $adminId = auth()->user()->guru?->id;

        try {
            DB::transaction(function () use ($validated, $tahun, $semester, $adminId) {
                Guru::where('is_active', 1)->pluck('id')->each(function ($guruId) use ($validated, $tahun, $semester, $adminId) {
                    AbsensiGuru::updateOrCreate(
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
                });
            });

            return redirect()->route('admin.absensi_guru.index', ['tanggal' => $validated['tanggal']])
                ->with('success', 'Absensi berhasil disimpan sebagai hadir untuk semua guru aktif.');
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

        try {
            AbsensiGuru::where('guru_id', $validated['guru_id'])
                ->where('tanggal', $validated['tanggal'])
                ->delete();

            return redirect()->route('admin.absensi_guru.index', ['tanggal' => $validated['tanggal']])
                ->with('success', 'Absensi guru berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menghapus absensi: ' . $e->getMessage());
        }
    }

    public function laporan(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $guruId = $request->input('guru_id');

        $query = AbsensiGuru::with(['guru.user'])
            ->join('guru', 'absensi_guru.guru_id', '=', 'guru.id')
            ->select('absensi_guru.*', 'guru.nama as nama_guru')
            ->whereNotNull('absensi_guru.status');

        if ($startDate) {
            $query->whereDate('absensi_guru.tanggal', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('absensi_guru.tanggal', '<=', $endDate);
        }
        if ($guruId) {
            $query->where('absensi_guru.guru_id', $guruId);
        }

        $absensi = $query->orderBy('absensi_guru.tanggal', 'desc')
            ->orderBy('guru.nama')
            ->paginate(50);

        $guru = Guru::where('is_active', 1)->orderBy('nama')->get();

        return view('admin.absensi_guru.laporan', compact('absensi', 'guru', 'startDate', 'endDate', 'guruId'));
    }
}
