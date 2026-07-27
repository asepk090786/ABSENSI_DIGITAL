<?php

namespace App\Http\Controllers;

use App\Models\AgendaGuru;
use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\JamBelajar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AgendaGuruController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $guru = $user->guru;

        if (!$guru) {
            return redirect()->route('home')
                ->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        $mode = $request->get('mode');
        $hariPiketArr = (array) ($guru->hari_piket ?? []);
        $todayEng = \Carbon\Carbon::now()->format('l');
        $map = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $todayIndo = $map[$todayEng] ?? null;
        $isGuruPiket = in_array($todayIndo, $hariPiketArr, true) && $mode !== 'academic';

        if ($isGuruPiket) {
            $selectedTanggal = $request->get('tanggal', now()->format('Y-m-d'));

            $daftarGuru = Guru::query()
                ->where('is_active', 1)
                ->orderBy('nama')
                ->get(['id', 'nama', 'nip', 'foto'])
                ->load('user');

            $absensiHariIni = AbsensiGuru::query()
                ->whereDate('tanggal', $selectedTanggal)
                ->get()
                ->keyBy('guru_id');

            $totalGuru = $daftarGuru->count();
            $hadirCount = $absensiHariIni->where('status', 'hadir')->count();
            $izinCount = $absensiHariIni->where('status', 'izin')->count();
            $sakitCount = $absensiHariIni->where('status', 'sakit')->count();
            $tidakHadirCount = $absensiHariIni->where('status', 'tidak_hadir')->count();

            return view('agenda_guru.absensi_piket', compact(
                'guru',
                'selectedTanggal',
                'daftarGuru',
                'absensiHariIni',
                'totalGuru',
                'hadirCount',
                'izinCount',
                'sakitCount',
                'tidakHadirCount'
            ));
        }

        // Get active tahun and semester
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        if (!$tahun || !$semester) {
            return redirect()->route('home')
                ->with('error', 'Tahun ajaran atau semester belum di-set aktif.');
        }

        // Get selected month and year (default current month)
        $bulan = $request->get('bulan', now()->month);
        $tahunFilter = $request->get('tahun', now()->year);

        $agendaList = $this->buildAgendaGuruQuery($guru, $tahun, $semester, $tahunFilter, $bulan)->get();

        // Get guru's mata pelajaran
        $mataPelajaran = DB::table('jadwal_kbm')
            ->join('mata_pelajaran', 'jadwal_kbm.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->where('jadwal_kbm.guru_id', $guru->id)
            ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id)
            ->where('jadwal_kbm.semester_id', $semester->id)
            ->select('mata_pelajaran.nama_mapel')
            ->distinct()
            ->first();

        // Group agenda by date for easy display
        $agendaByDate = $agendaList->groupBy(function ($item) {
            return $item->tanggal->format('Y-m-d');
        });

        // Get holiday list for styling
        $holidays = [];

        // Month info
        $monthName = [
            'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        return view('agenda_guru.index', compact(
            'guru',
            'agendaList',
            'agendaByDate',
            'bulan',
            'tahunFilter',
            'monthName',
            'mataPelajaran'
        ));
    }

    public function storeAbsensiGuru(Request $request)
    {
        $user = auth()->user();
        $pencatatGuru = $user?->guru;

        if (!$pencatatGuru) {
            return redirect()->route('agenda_guru.index')
                ->with('error', 'Akun guru tidak ditemukan.');
        }

        $hariPiketArr = (array) ($pencatatGuru->hari_piket ?? []);
        $todayEng = \Carbon\Carbon::now()->format('l');
        $map = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $todayIndo = $map[$todayEng] ?? null;
        $isGuruPiket = in_array($todayIndo, $hariPiketArr, true);
        if (!$isGuruPiket) {
            abort(403, 'Anda tidak memiliki akses absensi guru.');
        }

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'attendance' => 'required|array|min:1',
            'attendance.*.status' => 'nullable|in:hadir,tidak_hadir,izin,sakit',
            'attendance.*.keterangan' => 'nullable|string|max:255',
        ]);

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        DB::transaction(function () use ($validated, $pencatatGuru, $tahun, $semester) {
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
                        'pencatat_guru_id' => $pencatatGuru->id,
                        'status' => $status,
                        'keterangan' => $item['keterangan'] ?? null,
                        'tahun_ajaran_id' => $tahun->id ?? null,
                        'semester_id' => $semester->id ?? null,
                    ]
                );
            }
        });

        return redirect()->route('agenda_guru.index', ['tanggal' => $validated['tanggal']])
            ->with('success', 'Absensi guru berhasil disimpan.');
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $guru = $user->guru;

        if (!$guru) {
            return redirect()->route('agenda_guru.index')
                ->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        // Get active tahun and semester
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        if (!$tahun || !$semester) {
            return redirect()->route('agenda_guru.index')
                ->with('error', 'Tahun ajaran atau semester belum di-set aktif.');
        }

        // Get jam belajar relevant to this guru and selected date
        $selectedTanggal = $request->get('tanggal', now()->format('Y-m-d'));
        $jamBelajar = $this->getAvailableJamBelajarForGuruAndDate($guru, $selectedTanggal, $tahun, $semester);

        // Get Rencana Pembelajaran authored by this guru (for selection)
        $rencanaPembelajaranList = \App\Models\RencanaPembelajaran::where('guru_id', $guru->id)
            ->orderBy('judul')
            ->get(['id', 'judul', 'mata_pelajaran_id', 'kelas_id']);

        // Get daftar kegiatan dari database
        $kegiatanList = DB::table('kegiatan')->orderBy('nama_kegiatan')->get();

        return view('agenda_guru.create', compact(
            'guru',
            'jamBelajar',
            'selectedTanggal',
            'rencanaPembelajaranList',
            'kegiatanList'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $guru = $user->guru;

        if (!$guru) {
            return redirect()->route('agenda_guru.index')
                ->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        // Validate
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jam_belajar_id' => 'required|exists:jam_belajar,id',
            'kegiatan' => 'required|string|max:1000',
            'rencana_pembelajaran_id' => 'nullable|exists:rencana_pembelajaran,id',
        ], [
            'tanggal.required' => 'Tanggal harus diisi',
            'jam_belajar_id.required' => 'Jam pelajaran harus dipilih',
            'kegiatan.required' => 'Kegiatan harus diisi',
        ]);

        if ($this->isPastDate($validated['tanggal']) && ! $this->canEditPastAgenda($user)) {
            return redirect()->route('agenda_guru.index')
                ->with('error', 'Akses ditolak. Agenda guru tanggal lampau hanya dapat ditambahkan oleh Admin, Wali Kelas, atau Guru BK.');
        }

        // Get active tahun and semester
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        $availableJamBelajar = $this->getAvailableJamBelajarForGuruAndDate($guru, $validated['tanggal'], $tahun, $semester);
        $availableJamBelajarIds = $availableJamBelajar->pluck('id')->map(fn ($id) => (int) $id)->all();
        $requestedJamBelajarId = (int) $validated['jam_belajar_id'];
        $selectedJamBelajar = JamBelajar::find($requestedJamBelajarId);
        $expectedHari = $this->getHariIndonesiaFromDate($validated['tanggal']);

        if (!in_array($requestedJamBelajarId, $availableJamBelajarIds, true)) {
            return back()->withInput()->withErrors([
                'jam_belajar_id' => 'Jam pelajaran yang dipilih sudah terpakai atau tidak tersedia untuk guru ini pada tanggal tersebut.',
            ]);
        }

        if (!$selectedJamBelajar || $selectedJamBelajar->hari !== $expectedHari) {
            return back()->withInput()->withErrors([
                'jam_belajar_id' => 'Jam pelajaran yang dipilih tidak sesuai dengan hari yang Anda pilih.',
            ]);
        }

        // Prepare data
        $agendaData = [
            'guru_id' => $guru->id,
            'jam_belajar_id' => $validated['jam_belajar_id'],
            'tanggal' => $validated['tanggal'],
            'kegiatan' => $validated['kegiatan'] ?? null,
            'tahun_ajaran_id' => $tahun->id,
            'semester_id' => $semester->id,
        ];

        if (!empty($validated['rencana_pembelajaran_id'])) {
            $agendaData['rencana_pembelajaran_id'] = $validated['rencana_pembelajaran_id'];
            $rencana = \App\Models\RencanaPembelajaran::find($validated['rencana_pembelajaran_id']);
            if ($rencana && empty($agendaData['kegiatan'])) {
                // Fill kegiatan with rencana's judul + capaian pembelajaran (short)
                $agendaData['kegiatan'] = trim(($rencana->judul ?? '') . "\n" . ($rencana->capaian_pembelajaran ?? ''));
            }
        }

        try {
            DB::transaction(function () use ($agendaData) {
                AgendaGuru::create($agendaData);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'kegiatan' => 'Gagal menyimpan agenda guru. Silakan cek data yang Anda masukkan.',
            ]);
        }

        return redirect()->route('agenda_guru.index')
            ->with('success', 'Agenda guru berhasil ditambahkan');
    }

    public function edit(AgendaGuru $agendaGuru)
    {
        $user = auth()->user();
        $guru = $user->guru;

        // Auth check
        if (!$guru || $agendaGuru->guru_id != $guru->id) {
            abort(403, 'Tidak diizinkan mengakses agenda guru ini');
        }

        if ($this->isPastDate($agendaGuru->tanggal) && ! $this->canEditPastAgenda($user)) {
            abort(403, 'Akses ditolak. Agenda guru tanggal lampau hanya dapat diedit oleh Admin, Wali Kelas, atau Guru BK.');
        }

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        if (!$tahun || !$semester) {
            return redirect()->route('agenda_guru.index')
                ->with('error', 'Tahun ajaran atau semester belum di-set aktif.');
        }

        $selectedTanggal = $agendaGuru->tanggal->format('Y-m-d');
        $jamBelajar = $this->getAvailableJamBelajarForGuruAndDate($guru, $selectedTanggal, $tahun, $semester);
        $rencanaPembelajaranList = \App\Models\RencanaPembelajaran::where('guru_id', $guru->id)
            ->orderBy('judul')
            ->get(['id', 'judul', 'mata_pelajaran_id', 'kelas_id']);

        return view('agenda_guru.edit', compact(
            'agendaGuru',
            'jamBelajar',
            'rencanaPembelajaranList'
        ));
    }

    public function update(Request $request, AgendaGuru $agendaGuru)
    {
        $user = auth()->user();
        $guru = $user->guru;

        // Auth check
        if (!$guru || $agendaGuru->guru_id != $guru->id) {
            abort(403, 'Tidak diizinkan mengubah agenda guru ini');
        }

        if ($this->isPastDate($agendaGuru->tanggal) && ! $this->canEditPastAgenda($user)) {
            abort(403, 'Akses ditolak. Agenda guru tanggal lampau hanya dapat diedit oleh Admin, Wali Kelas, atau Guru BK.');
        }

        // Validate
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jam_belajar_id' => 'required|exists:jam_belajar,id',
            'kegiatan' => 'required|string|max:1000',
            'rencana_pembelajaran_id' => 'nullable|exists:rencana_pembelajaran,id',
        ], [
            'tanggal.required' => 'Tanggal harus diisi',
            'jam_belajar_id.required' => 'Jam pelajaran harus dipilih',
            'kegiatan.required' => 'Kegiatan harus diisi',
        ]);

        // Update
        $updateData = $validated;
        if (!empty($validated['rencana_pembelajaran_id'])) {
            $rencana = \App\Models\RencanaPembelajaran::find($validated['rencana_pembelajaran_id']);
                if ($rencana && empty($updateData['kegiatan'])) {
                    $updateData['kegiatan'] = trim(($rencana->judul ?? '') . '\n' . ($rencana->capaian_pembelajaran ?? ''));
                }
        }

        if ($this->isPastDate($updateData['tanggal']) && ! $this->canEditPastAgenda($user)) {
            abort(403, 'Akses ditolak. Agenda guru tanggal lampau hanya dapat diedit oleh Admin, Wali Kelas, atau Guru BK.');
        }

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        if (!$tahun || !$semester) {
            return redirect()->route('agenda_guru.index')
                ->with('error', 'Tahun ajaran atau semester belum di-set aktif.');
        }

        $availableJamBelajar = $this->getAvailableJamBelajarForGuruAndDate($guru, $updateData['tanggal'], $tahun, $semester);
        $availableJamBelajarIds = $availableJamBelajar->pluck('id')->map(fn ($id) => (int) $id)->all();
        $requestedJamBelajarId = (int) $updateData['jam_belajar_id'];
        $selectedJamBelajar = JamBelajar::find($requestedJamBelajarId);
        $expectedHari = $this->getHariIndonesiaFromDate($updateData['tanggal']);

        if (!in_array($requestedJamBelajarId, $availableJamBelajarIds, true)) {
            return back()->withInput()->withErrors([
                'jam_belajar_id' => 'Jam pelajaran yang dipilih sudah terpakai atau tidak tersedia untuk guru ini pada tanggal tersebut.',
            ]);
        }

        if (!$selectedJamBelajar || $selectedJamBelajar->hari !== $expectedHari) {
            return back()->withInput()->withErrors([
                'jam_belajar_id' => 'Jam pelajaran yang dipilih tidak sesuai dengan hari yang Anda pilih.',
            ]);
        }

        try {
            DB::transaction(function () use ($agendaGuru, $updateData) {
                $agendaGuru->update($updateData);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->withErrors([
                'kegiatan' => 'Gagal memperbarui agenda guru. Silakan cek data yang Anda masukkan.',
            ]);
        }

        return redirect()->route('agenda_guru.index')
            ->with('success', 'Agenda guru berhasil diperbarui');
    }

    public function destroy(AgendaGuru $agendaGuru)
    {
        $user = auth()->user();
        $guru = $user->guru;

        // Auth check
        if (!$guru || $agendaGuru->guru_id != $guru->id) {
            abort(403, 'Tidak diizinkan menghapus agenda guru ini');
        }

        if ($this->isPastDate($agendaGuru->tanggal) && ! $this->canEditPastAgenda($user)) {
            abort(403, 'Akses ditolak. Agenda guru tanggal lampau hanya dapat dihapus oleh Admin, Wali Kelas, atau Guru BK.');
        }

        $agendaGuru->delete();

        return redirect()->route('agenda_guru.index')
            ->with('success', 'Agenda guru berhasil dihapus');
    }

    private function canEditPastAgenda($user): bool
    {
        return $user && (
            $user->hasAnyRole(['Admin', 'Kepala Sekolah']) ||
            $user->hasRole('Wali Kelas') ||
            $user->hasRole('Guru BK')
        );
    }

    private function getAvailableJamBelajarForGuruAndDate($guru, $selectedTanggal, $tahun, $semester)
    {
        $hariAgenda = $this->getHariIndonesiaFromDate($selectedTanggal);

        $scheduledJamIds = DB::table('jadwal_kbm')
            ->where('guru_id', $guru->id)
            ->where('hari', $hariAgenda)
            ->where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id)
            ->pluck('jam_belajar_id')
            ->unique()
            ->values()
            ->all();

        $query = JamBelajar::where('hari', $hariAgenda)
            ->orderBy('urutan');

        if (!empty($scheduledJamIds)) {
            $query->whereNotIn('id', $scheduledJamIds);
        }

        return $query->get();
    }

    private function getHariIndonesiaFromDate($date)
    {
        $hariIndonesia = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];

        $hariEnglish = Carbon::parse($date)->format('l');

        return $hariIndonesia[$hariEnglish] ?? $hariEnglish;
    }

    private function isPastDate($date): bool
    {
        return Carbon::parse($date)->startOfDay()->lt(Carbon::today());
    }

    private function buildAgendaGuruQuery($guru, $tahun, $semester, $tahunFilter, $bulan)
    {
        return AgendaGuru::select('agenda_guru.*')
            ->where('agenda_guru.guru_id', $guru->id)
            ->where('agenda_guru.tahun_ajaran_id', $tahun->id)
            ->where('agenda_guru.semester_id', $semester->id)
            ->whereYear('agenda_guru.tanggal', $tahunFilter)
            ->whereMonth('agenda_guru.tanggal', $bulan)
            ->leftJoin('jam_belajar', 'agenda_guru.jam_belajar_id', '=', 'jam_belajar.id')
            ->with(['jamBelajar'])
            ->orderBy('agenda_guru.tanggal', 'asc')
            ->orderBy('jam_belajar.urutan', 'asc');
    }

    public function export(Request $request)
    {
        $user = auth()->user();
        $guru = $user->guru;

        if (!$guru) {
            return redirect()->route('agenda_guru.index')
                ->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        // Get selected month
        $bulan = $request->get('bulan', now()->month);
        $tahunFilter = $request->get('tahun', now()->year);

        // Get active tahun and semester
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        if (!$tahun || !$semester) {
            return redirect()->route('agenda_guru.index')
                ->with('error', 'Tahun ajaran atau semester belum di-set aktif.');
        }

        // Get agenda guru entries for export using same query as index
        $agendaList = $this->buildAgendaGuruQuery($guru, $tahun, $semester, $tahunFilter, $bulan)->get();

        // Get guru's mata pelajaran
        $mataPelajaran = DB::table('jadwal_kbm')
            ->join('mata_pelajaran', 'jadwal_kbm.mata_pelajaran_id', '=', 'mata_pelajaran.id')
            ->where('jadwal_kbm.guru_id', $guru->id)
            ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id)
            ->where('jadwal_kbm.semester_id', $semester->id)
            ->select('mata_pelajaran.nama_mapel')
            ->distinct()
            ->first();

        // Sekolah info
        $sekolah = DB::table('sekolah')->first();

        // Ambil langsung dari data kepala sekolah
        $kepalaSekolah = DB::table('kepala_sekolah')
            ->where('status', 'Aktif')
            ->orderBy('tanggal_mulai_jabatan', 'desc')
            ->first();

        if (!$kepalaSekolah) {
            $kepalaSekolah = DB::table('kepala_sekolah')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        $namaKepalaSekolah = $kepalaSekolah->nama ?? '-';
        $nipKepalaSekolah = $kepalaSekolah->nip ?? '';
        $nipGuru = $guru->nip ?? '';

        // Month name
        $monthName = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];

        $viewData = compact(
            'guru',
            'agendaList',
            'mataPelajaran',
            'sekolah',
            'namaKepalaSekolah',
            'nipKepalaSekolah',
            'nipGuru',
            'bulan',
            'tahunFilter',
            'monthName'
        );

        // If requested format is PDF, generate PDF download using Dompdf
        if (strtolower($request->get('format', '')) === 'pdf') {
            $filename = 'agenda_guru_' . ($guru->id ?? 'guru') . '_' . ($bulan ?? now()->month) . '.pdf';
            $pdf = \PDF::loadView('agenda_guru.export_pdf', $viewData);
            $pdf->setPaper('a4', 'landscape');
            return $pdf->download($filename);
        }

        // Render normal HTML view
        return view('agenda_guru.export', array_merge($viewData, ['forPdf' => false]));
    }
}
