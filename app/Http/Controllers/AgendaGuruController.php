<?php

namespace App\Http\Controllers;

use App\Models\AgendaGuru;
use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\JamBelajar;
use App\Exports\GuruPiketAbsensiExport;
use App\Exports\GuruPiketAbsensiMonthExport;
use App\Exports\GuruPiketAbsensiRangeExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

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

    public function exportAbsensiGuruPdf(Request $request)
    {
        $user = auth()->user();
        $pencatatGuru = $user?->guru;

        if (!$pencatatGuru) {
            abort(403, 'Akun guru tidak ditemukan.');
        }

        $hariPiketArr = (array) ($pencatatGuru->hari_piket ?? []);
        $todayIndo = $this->getHariIndonesiaFromDate($request->get('tanggal', now()->format('Y-m-d')));
        $isGuruPiket = $user->hasRole('Guru Piket') || in_array($todayIndo, $hariPiketArr, true);
        if (!$isGuruPiket) {
            abort(403, 'Anda tidak memiliki akses rekap absensi guru.');
        }

        $selectedTanggal = $request->get('tanggal', now()->format('Y-m-d'));

        $daftarGuru = Guru::query()
            ->where('is_active', 1)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip']);

        $absensiHariIni = DB::table('absensi_guru')
            ->whereDate('tanggal', $selectedTanggal)
            ->get()
            ->keyBy('guru_id');

        $agendaHariIni = DB::table('agenda_guru')
            ->whereDate('tanggal', $selectedTanggal)
            ->get()
            ->pluck('id', 'guru_id')
            ->toArray();

        $hariAgenda = $this->getHariIndonesiaFromDate($selectedTanggal);

        $jadwalQuery = DB::table('jadwal_kbm')
            ->join('kelas', 'jadwal_kbm.kelas_id', '=', 'kelas.id')
            ->join('jam_belajar', 'jadwal_kbm.jam_belajar_id', '=', 'jam_belajar.id')
            ->where('jadwal_kbm.guru_id', '!=', 0)
            ->where('jadwal_kbm.hari', $hariAgenda)
            ->select(
                'jadwal_kbm.guru_id',
                'kelas.nama_kelas',
                'jam_belajar.urutan',
                'jam_belajar.jam_mulai',
                'jam_belajar.jam_selesai'
            )
            ->orderBy('jam_belajar.urutan')
            ->get();

        $jadwalPerGuru = [];
        $semuaJamUrutan = [];
        foreach ($jadwalQuery as $jadwal) {
            $jadwalPerGuru[$jadwal->guru_id][] = $jadwal;
            $semuaJamUrutan[$jadwal->urutan] = (int) $jadwal->urutan;
        }
        $jamColumns = array_values($semuaJamUrutan);
        $jamColumns = array_values(array_unique($jamColumns));
        sort($jamColumns);

        $rows = [];
        $no = 1;
        $summary = [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'tidak_hadir' => 0,
            'total' => 0,
        ];
        foreach ($daftarGuru as $item) {
            $record = $absensiHariIni->get($item->id);
            $status = $record->status ?? '';
            $isHadir = $status === 'hadir';

            $hasAgenda = isset($agendaHariIni[$item->id]);
            $hasAbsensi = !empty($status);

            if ($hasAbsensi && $hasAgenda) {
                $rowColor = 'green';
            } elseif ($hasAbsensi || $hasAgenda) {
                $rowColor = 'yellow';
            } else {
                $rowColor = 'red';
            }

            $jadwalList = $jadwalPerGuru[$item->id] ?? [];
            $jamStatus = [];
            $jamColors = [];
            foreach ($jamColumns as $urutan) {
                $matched = collect($jadwalList)->firstWhere('urutan', $urutan);
                if ($matched) {
                    $cellValue = $isHadir ? $matched->nama_kelas : 'X';
                    $jamStatus[$urutan] = $cellValue;
                    if ($hasAbsensi && $hasAgenda) {
                        $jamColors[$urutan] = '#d4edda';
                    } elseif ($hasAbsensi || $hasAgenda) {
                        $jamColors[$urutan] = '#fff3cd';
                    } else {
                        $jamColors[$urutan] = '#f8d7da';
                    }
                } else {
                    $jamStatus[$urutan] = '';
                    $jamColors[$urutan] = '#ffffff';
                }
            }

            $keteranganParts = [];
            foreach ($jadwalList as $jadwal) {
                if ($isHadir) {
                    $keteranganParts[] = 'Hadir: ' . $jadwal->nama_kelas;
                }
            }
            $keterangan = $keteranganParts ? implode(', ', $keteranganParts) : '-';

            $rows[] = [
                'no' => $no++,
                'nama' => $item->nama,
                'nip' => $item->nip ?: '-',
                'jam_status' => $jamStatus,
                'jam_colors' => $jamColors,
                'keterangan' => $keterangan,
            ];

            if ($status && isset($summary[$status])) {
                $summary[$status]++;
            }
            $summary['total']++;
        }

        $sekolah = DB::table('sekolah')->first();
        $sekolahNama = $sekolah->nama_sekolah ?? '';
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        $tahunAjaran = $tahun->nama_tahun ?? $tahun->nama_tahun_ajaran ?? '';
        $semesterNama = $semester->nama_semester ?? '';

        $namaGuruPiket = $pencatatGuru->nama ?? '-';
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

        $pdf = \PDF::loadView('agenda_guru.absensi_piket_pdf', compact(
            'rows', 'selectedTanggal', 'sekolahNama', 'tahunAjaran', 'semesterNama', 'summary',
            'namaGuruPiket', 'namaKepalaSekolah', 'pencatatGuru', 'kepalaSekolah', 'jamColumns'
        ));
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap-Absensi-Guru-' . Carbon::parse($selectedTanggal)->format('Ymd') . '.pdf');
    }

    public function exportAbsensiGuruExcel(Request $request)
    {
        $user = auth()->user();
        $pencatatGuru = $user?->guru;

        if (!$pencatatGuru) {
            abort(403, 'Akun guru tidak ditemukan.');
        }

        $hariPiketArr = (array) ($pencatatGuru->hari_piket ?? []);
        $todayIndo = $this->getHariIndonesiaFromDate($request->get('tanggal', now()->format('Y-m-d')));
        $isGuruPiket = $user->hasRole('Guru Piket') || in_array($todayIndo, $hariPiketArr, true);
        if (!$isGuruPiket) {
            abort(403, 'Anda tidak memiliki akses rekap absensi guru.');
        }

        $selectedTanggal = $request->get('tanggal', now()->format('Y-m-d'));

        $filename = 'Rekap-Absensi-Guru-' . Carbon::parse($selectedTanggal)->format('Ymd') . '.xlsx';

        return Excel::download(new GuruPiketAbsensiExport($selectedTanggal), $filename);
    }

    public function exportAbsensiGuruRange(Request $request, $type)
    {
        $user = auth()->user();
        $pencatatGuru = $user?->guru;

        if (!$pencatatGuru) {
            abort(403, 'Akun guru tidak ditemukan.');
        }

        $validated = $request->validate([
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
        ]);

        $startDate = Carbon::parse($validated['start']);
        $endDate = Carbon::parse($validated['end']);

        $daftarGuru = Guru::query()
            ->where('is_active', 1)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip']);

        $absensiRange = DB::table('absensi_guru')
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy('guru_id');

        $agendaRange = DB::table('agenda_guru')
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy('guru_id')
            ->map(fn($items) => $items->pluck('id')->toArray());

        $dates = [];
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dates[] = $d->format('Y-m-d');
        }

        $rows = [];
        $no = 1;
        $summary = [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'tidak_hadir' => 0,
            'total' => 0,
        ];

        foreach ($daftarGuru as $item) {
            $absensiMap = $absensiRange->get($item->id, collect())->keyBy('tanggal');
            $hasAgenda = !empty($agendaRange->get($item->id, []));

            $row = [
                $no++,
                $item->nama,
                $item->nip ?: '-',
            ];

            foreach ($dates as $date) {
                $record = $absensiMap->get($date);
                $status = $record->status ?? '';
                $row[] = $status ? match ($status) {
                    'hadir' => 'H',
                    'tidak_hadir' => 'A',
                    'izin' => 'I',
                    'sakit' => 'S',
                    default => '-',
                } : '-';
            }

            $row[] = $absensiMap->where('status', 'hadir')->count() ?? 0;
            $row[] = $absensiMap->where('status', 'tidak_hadir')->count() ?? 0;
            $row[] = $absensiMap->whereIn('status', ['izin', 'sakit'])->count() ?? 0;

            $rows[] = $row;

            foreach ($absensiMap as $record) {
                $status = $record->status;
                if ($status && isset($summary[$status])) {
                    $summary[$status]++;
                }
            }
            $summary['total']++;
        }

        $sekolah = DB::table('sekolah')->first();
        $sekolahNama = $sekolah->nama_sekolah ?? '';
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        $tahunAjaran = $tahun->nama_tahun ?? $tahun->nama_tahun_ajaran ?? '';
        $semesterNama = $semester->nama_semester ?? '';

        $namaGuruPiket = $pencatatGuru->nama ?? '-';
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

        if ($type === 'pdf') {
            $pdf = \PDF::loadView('agenda_guru.absensi_piket_range_pdf', compact(
                'rows', 'dates', 'startDate', 'endDate', 'sekolahNama', 'tahunAjaran', 'semesterNama', 'summary',
                'namaGuruPiket', 'namaKepalaSekolah', 'pencatatGuru', 'kepalaSekolah'
            ));
            $pdf->setPaper('a4', 'landscape');
            return $pdf->stream('Rekap-Absensi-Guru-Range-' . $startDate->format('Ymd') . '.pdf');
        }

        return Excel::download(new GuruPiketAbsensiRangeExport($startDate, $endDate), 'Rekap-Absensi-Guru-Range-' . $startDate->format('Ymd') . '.xlsx');
    }

    public function exportAbsensiGuruMonth(Request $request, $type)
    {
        $user = auth()->user();
        $pencatatGuru = $user?->guru;

        if (!$pencatatGuru) {
            abort(403, 'Akun guru tidak ditemukan.');
        }

        $validated = $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $month = (int) $validated['month'];
        $year = (int) $validated['year'];

        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = Carbon::create($year, $month, 1)->endOfMonth();

        $daftarGuru = Guru::query()
            ->where('is_active', 1)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip']);

        $absensiRange = DB::table('absensi_guru')
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy('guru_id');

        $agendaRange = DB::table('agenda_guru')
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy('guru_id')
            ->map(fn($items) => $items->pluck('id')->toArray());

        $dates = [];
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dates[] = $d->format('Y-m-d');
        }

        $rows = [];
        $no = 1;
        $summary = [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'tidak_hadir' => 0,
            'total' => 0,
        ];

        foreach ($daftarGuru as $item) {
            $absensiMap = $absensiRange->get($item->id, collect())->keyBy('tanggal');
            $hasAgenda = !empty($agendaRange->get($item->id, []));

            $row = [
                $no++,
                $item->nama,
                $item->nip ?: '-',
            ];

            foreach ($dates as $date) {
                $record = $absensiMap->get($date);
                $status = $record->status ?? '';
                $row[] = $status ? match ($status) {
                    'hadir' => 'H',
                    'tidak_hadir' => 'A',
                    'izin' => 'I',
                    'sakit' => 'S',
                    default => '-',
                } : '-';
            }

            $row[] = $absensiMap->where('status', 'hadir')->count() ?? 0;
            $row[] = $absensiMap->where('status', 'tidak_hadir')->count() ?? 0;
            $row[] = $absensiMap->whereIn('status', ['izin', 'sakit'])->count() ?? 0;

            $rows[] = $row;

            foreach ($absensiMap as $record) {
                $status = $record->status;
                if ($status && isset($summary[$status])) {
                    $summary[$status]++;
                }
            }
            $summary['total']++;
        }

        $sekolah = DB::table('sekolah')->first();
        $sekolahNama = $sekolah->nama_sekolah ?? '';
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        $tahunAjaran = $tahun->nama_tahun ?? $tahun->nama_tahun_ajaran ?? '';
        $semesterNama = $semester->nama_semester ?? '';

        $namaGuruPiket = $pencatatGuru->nama ?? '-';
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

        if ($type === 'pdf') {
            $pdf = \PDF::loadView('agenda_guru.absensi_piket_month_pdf', compact(
                'rows', 'dates', 'month', 'year', 'sekolahNama', 'tahunAjaran', 'semesterNama', 'summary',
                'namaGuruPiket', 'namaKepalaSekolah', 'pencatatGuru', 'kepalaSekolah'
            ));
            $pdf->setPaper('a4', 'landscape');
            return $pdf->stream('Rekap-Absensi-Guru-Bulan-' . $month . '-' . $year . '.pdf');
        }

        return Excel::download(new GuruPiketAbsensiMonthExport($month, $year), 'Rekap-Absensi-Guru-Bulan-' . $month . '-' . $year . '.xlsx');
    }
}
