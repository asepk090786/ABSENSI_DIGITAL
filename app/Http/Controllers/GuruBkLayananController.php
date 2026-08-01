<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKelas;
use App\Models\AgendaKelas;
use App\Models\Kelas;
use App\Models\KepalaSekolah;
use App\Models\JenisPelanggaran;
use App\Models\LayananBk;
use App\Models\LaporanSiswaGuru;
use App\Models\PembinaanBk;
use App\Models\Sekolah;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\TindakLanjutBk;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruBkLayananController extends Controller
{
    private function authorizeKelasBinaan(Kelas $kelas): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasRole('Guru BK') || empty($user->guru_id)) {
            abort(403, 'Akses hanya untuk Guru BK.');
        }

        if ((int) ($kelas->guru_bk_id ?? 0) !== (int) $user->guru_id) {
            abort(403, 'Kelas ini bukan kelas binaan Anda.');
        }
    }

    public static function resolveDateRange(string $periode, ?string $tanggal, ?string $tanggalMulai = null, ?string $tanggalSelesai = null): array
    {
        if (! empty($tanggalMulai) && ! empty($tanggalSelesai)) {
            return [
                'startDate' => Carbon::parse($tanggalMulai)->format('Y-m-d'),
                'endDate' => Carbon::parse($tanggalSelesai)->format('Y-m-d'),
                'selectedTanggal' => Carbon::parse($tanggalMulai)->format('Y-m-d'),
            ];
        }

        $date = Carbon::parse($tanggal ?? Carbon::today()->format('Y-m-d'));
        $selectedTanggal = $date->format('Y-m-d');

        switch ($periode) {
            case 'harian':
                return [
                    'startDate' => $selectedTanggal,
                    'endDate' => $selectedTanggal,
                    'selectedTanggal' => $selectedTanggal,
                ];
            case 'mingguan':
                $weekNumber = (int) ceil($date->day / 7);
                $startDate = $date->copy()->startOfMonth()->addDays(($weekNumber - 1) * 7);
                $endDate = $startDate->copy()->addDays(6);

                if ($endDate->greaterThan($date->copy()->endOfMonth())) {
                    $endDate = $date->copy()->endOfMonth();
                }

                return [
                    'startDate' => $startDate->format('Y-m-d'),
                    'endDate' => $endDate->format('Y-m-d'),
                    'selectedTanggal' => $startDate->format('Y-m-d'),
                ];
            case 'bulanan':
            default:
                $startDate = $date->copy()->startOfMonth();
                $endDate = $date->copy()->endOfMonth();

                return [
                    'startDate' => $startDate->format('Y-m-d'),
                    'endDate' => $endDate->format('Y-m-d'),
                    'selectedTanggal' => $startDate->format('Y-m-d'),
                ];
            case 'rentang':
                return [
                    'startDate' => $selectedTanggal,
                    'endDate' => $selectedTanggal,
                    'selectedTanggal' => $selectedTanggal,
                ];
        }
    }

    public function menu(Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        $tahunAjaran = TahunAjaran::where('is_active', true)->first();
        $semester = Semester::where('is_active', true)->first();

        $selectedPeriode = request('periode', 'bulanan');
        $selectedTanggal = request('tanggal', Carbon::today()->format('Y-m-d'));
        $tanggalMulai = request('tanggal_mulai');
        $tanggalSelesai = request('tanggal_selesai');

        $resolvedRange = self::resolveDateRange($selectedPeriode, $selectedTanggal, $tanggalMulai, $tanggalSelesai);
        $startDate = $resolvedRange['startDate'];
        $endDate = $resolvedRange['endDate'];
        $selectedTanggal = $resolvedRange['selectedTanggal'];

        $absensiQuery = AbsensiKelas::where('kelas_id', $kelas->id);
        $agendaQuery = AgendaKelas::where('kelas_id', $kelas->id);

        if ($tahunAjaran) {
            $absensiQuery->where('tahun_ajaran_id', $tahunAjaran->id);
            $agendaQuery->where('tahun_ajaran_id', $tahunAjaran->id);
        }
        if ($semester) {
            $absensiQuery->where('semester_id', $semester->id);
            $agendaQuery->where('semester_id', $semester->id);
        }

        $stats = (object) [
            'total_siswa' => Siswa::where('kelas_id', $kelas->id)->count(),
            'absensi_count' => $absensiQuery->count(),
            'agenda_count' => $agendaQuery->count(),
            'laporan_wali_kelas_count' => LaporanSiswaGuru::where('kelas_id', $kelas->id)
                ->whereNull('absensi_kelas_id')
                ->count(),
            'last_absensi_date' => AbsensiKelas::where('kelas_id', $kelas->id)
                ->when($tahunAjaran, fn($query) => $query->where('tahun_ajaran_id', $tahunAjaran->id))
                ->when($semester, fn($query) => $query->where('semester_id', $semester->id))
                ->max('tanggal'),
            'last_agenda_date' => AgendaKelas::where('kelas_id', $kelas->id)
                ->when($tahunAjaran, fn($query) => $query->where('tahun_ajaran_id', $tahunAjaran->id))
                ->when($semester, fn($query) => $query->where('semester_id', $semester->id))
                ->max('tanggal'),
        ];

        $siswaList = Siswa::where('kelas_id', $kelas->id)
            ->where('status_aktif', 1)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nis']);

        $isDailyDetail = ($selectedPeriode ?? 'bulanan') === 'harian';

        $rekapAbsensiQuery = DB::table('absensi_siswa as asw')
            ->join('absensi_kelas as ak', 'ak.id', '=', 'asw.absensi_kelas_id')
            ->where('ak.kelas_id', $kelas->id)
            ->whereBetween('ak.tanggal', [$startDate, $endDate])
            ->when($tahunAjaran, fn($query) => $query->where('ak.tahun_ajaran_id', $tahunAjaran->id))
            ->when($semester, fn($query) => $query->where('ak.semester_id', $semester->id));

        if ($isDailyDetail) {
            $rekapAbsensiQuery->select(
                'asw.siswa_id',
                DB::raw("SUM(CASE WHEN LOWER(asw.status) = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                DB::raw("SUM(CASE WHEN LOWER(asw.status) = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                DB::raw("SUM(CASE WHEN LOWER(asw.status) IN ('izin','ijin') THEN 1 ELSE 0 END) as izin"),
                DB::raw("SUM(CASE WHEN LOWER(asw.status) IN ('terlambat','telat') THEN 1 ELSE 0 END) as terlambat"),
                DB::raw("SUM(CASE WHEN LOWER(asw.status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 1 ELSE 0 END) as tidak_hadir")
            )->groupBy('asw.siswa_id');
        } else {
            $rekapAbsensiQuery->select(
                'asw.siswa_id',
                DB::raw("SUM(CASE WHEN LOWER(asw.status) = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                DB::raw("SUM(CASE WHEN LOWER(asw.status) = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                DB::raw("SUM(CASE WHEN LOWER(asw.status) IN ('izin','ijin') THEN 1 ELSE 0 END) as izin"),
                DB::raw("SUM(CASE WHEN LOWER(asw.status) IN ('terlambat','telat') THEN 1 ELSE 0 END) as terlambat"),
                DB::raw("SUM(CASE WHEN LOWER(asw.status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 1 ELSE 0 END) as tidak_hadir"),
                DB::raw("COUNT(DISTINCT DATE(ak.tanggal)) as total_hari")
            )->groupBy('asw.siswa_id');
        }

        $rekapAbsensiMap = $rekapAbsensiQuery->get()->keyBy('siswa_id');

        $rekapAbsensi = $siswaList->map(function ($siswa) use ($rekapAbsensiMap, $isDailyDetail) {
            $row = $rekapAbsensiMap->get($siswa->id, (object) [
                'hadir' => 0,
                'sakit' => 0,
                'izin' => 0,
                'terlambat' => 0,
                'tidak_hadir' => 0,
                'total_hari' => 0,
            ]);

            $hadirCount = (int) ($row->hadir ?? 0);
            $sakitCount = (int) ($row->sakit ?? 0);
            $izinCount = (int) ($row->izin ?? 0);
            $terlambatCount = (int) ($row->terlambat ?? 0);
            $tidakHadirCount = (int) ($row->tidak_hadir ?? 0);
            $totalHari = (int) ($row->total_hari ?? 0);

            if (! $isDailyDetail) {
                $hadirCount = max(0, min($totalHari, $hadirCount));
            }

            return (object) [
                'siswa_id' => $siswa->id,
                'nama_siswa' => $siswa->nama,
                'nis' => $siswa->nis,
                'hadir' => $hadirCount,
                'sakit' => $sakitCount,
                'izin' => $izinCount,
                'terlambat' => $terlambatCount,
                'tidak_hadir' => $tidakHadirCount,
                'total_rekap' => (int) ($hadirCount + $sakitCount + $izinCount + $terlambatCount + $tidakHadirCount),
            ];
        })->values();
        $jamColumns = [];
        $dailyAttendanceRows = collect();

        if ($isDailyDetail) {
            $hariMap = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu',
            ];
            $hariQuery = $hariMap[Carbon::parse($selectedTanggal)->format('l')] ?? Carbon::parse($selectedTanggal)->format('l');

            $jamRows = DB::table('jadwal_kbm as jk')
                ->leftJoin('jam_belajar as jb', 'jb.id', '=', 'jk.jam_belajar_id')
                ->where('jk.kelas_id', $kelas->id)
                ->where('jk.hari', $hariQuery)
                ->when($tahunAjaran, fn($query) => $query->where('jk.tahun_ajaran_id', $tahunAjaran->id))
                ->when($semester, fn($query) => $query->where('jk.semester_id', $semester->id))
                ->select('jk.jam_belajar_id', 'jk.jam_ke', 'jb.urutan', 'jb.jam_mulai', 'jb.jam_selesai')
                ->orderBy('jk.jam_ke')
                ->get();

            if ($jamRows->isEmpty()) {
                $jamRows = DB::table('absensi_kelas as ak')
                    ->leftJoin('jam_belajar as jb', 'jb.id', '=', 'ak.jam_belajar_id')
                    ->where('ak.kelas_id', $kelas->id)
                    ->whereDate('ak.tanggal', $selectedTanggal)
                    ->when($tahunAjaran, fn($query) => $query->where('ak.tahun_ajaran_id', $tahunAjaran->id))
                    ->when($semester, fn($query) => $query->where('ak.semester_id', $semester->id))
                    ->select('ak.jam_belajar_id', 'jb.urutan', 'jb.jam_mulai', 'jb.jam_selesai')
                    ->distinct()
                    ->orderBy('jb.urutan')
                    ->get();
            }

            $jamColumns = $jamRows->map(function ($jam, $index) {
                $jamKe = $jam->jam_ke ?? $jam->urutan ?? ($index + 1);
                $label = 'Jam ke-' . $jamKe;
                if (!empty($jam->jam_mulai) && !empty($jam->jam_selesai)) {
                    $label .= ' (' . $jam->jam_mulai . ' - ' . $jam->jam_selesai . ')';
                }

                return [
                    'id' => $jam->jam_belajar_id,
                    'label' => $label,
                ];
            })->values()->all();

            $dailyEntries = DB::table('absensi_siswa as asw')
                ->join('absensi_kelas as ak', 'ak.id', '=', 'asw.absensi_kelas_id')
                ->where('ak.kelas_id', $kelas->id)
                ->whereDate('ak.tanggal', $selectedTanggal)
                ->when($tahunAjaran, fn($query) => $query->where('ak.tahun_ajaran_id', $tahunAjaran->id))
                ->when($semester, fn($query) => $query->where('ak.semester_id', $semester->id))
                ->select('asw.siswa_id', 'asw.status', 'ak.jam_belajar_id')
                ->get();

            $detailMap = [];
            foreach ($dailyEntries as $entry) {
                $detailMap[$entry->siswa_id][$entry->jam_belajar_id] = $entry->status;
            }

            $dailyAttendanceRows = $siswaList->map(function ($siswa) use ($detailMap, $jamColumns) {
                $cells = [];

                foreach ($jamColumns as $jamColumn) {
                    $status = $detailMap[$siswa->id][$jamColumn['id']] ?? null;
                    $cells[] = $this->buildAttendanceCell($status);
                }

                return (object) [
                    'siswa_id' => $siswa->id,
                    'nama_siswa' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'cells' => $cells,
                ];
            })->values();
        }

        return view('guru_bk_layanan.menu', compact(
            'kelas',
            'stats',
            'rekapAbsensi',
            'selectedPeriode',
            'selectedTanggal',
            'tanggalMulai',
            'tanggalSelesai',
            'startDate',
            'endDate',
            'isDailyDetail',
            'jamColumns',
            'dailyAttendanceRows'
        ));
    }

    private function buildAttendanceCell(?string $status): array
    {
        $statusNormalized = strtolower(trim((string) ($status ?? '')));

        switch ($statusNormalized) {
            case 'hadir':
                return ['text' => 'H', 'style' => 'background:#16a34a;color:#fff;'];
            case 'sakit':
                return ['text' => 'S', 'style' => 'background:#93c5fd;color:#0f172a;'];
            case 'izin':
            case 'ijin':
                return ['text' => 'I', 'style' => 'background:#93c5fd;color:#0f172a;'];
            case 'terlambat':
            case 'telat':
                return ['text' => 'T', 'style' => 'background:#facc15;color:#713f12;'];
            case 'alpha':
            case 'alpa':
            case 'alfa':
            case 'absen':
            case 'tidak_hadir':
                return ['text' => 'A', 'style' => 'background:#dc2626;color:#fff;'];
            default:
                return ['text' => '-', 'style' => 'background:#f3f4f6;color:#6b7280;'];
        }
    }

    public function layanan(Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        $selectedTanggal = request('tanggal', Carbon::today()->format('Y-m-d'));

        $absensiItems = AbsensiKelas::with(['guru', 'jamBelajar', 'absensiSiswa'])
            ->where('kelas_id', $kelas->id)
            ->whereDate('tanggal', $selectedTanggal)
            ->orderBy('tanggal', 'desc')
            ->get();

        $siswaList = Siswa::where('kelas_id', $kelas->id)
            ->orderBy('nama')
            ->get();

        $layananItems = LayananBk::with('siswa')
            ->where('kelas_id', $kelas->id)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('guru_bk_layanan.layanan', compact('kelas', 'selectedTanggal', 'absensiItems', 'siswaList', 'layananItems'));
    }

    public function storeLayanan(Request $request, Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'siswa_id' => 'nullable|exists:siswa,id',
            'jenis_layanan' => 'required|string|max:100',
            'deskripsi_layanan' => 'required|string',
            'hasil_layanan' => 'nullable|string',
            'rencana_tindak_lanjut' => 'nullable|string',
        ]);

        if (!empty($validated['siswa_id'])) {
            $isSiswaDiKelas = Siswa::where('id', $validated['siswa_id'])
                ->where('kelas_id', $kelas->id)
                ->exists();

            if (! $isSiswaDiKelas) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['siswa_id' => 'Siswa yang dipilih bukan bagian dari kelas binaan ini.']);
            }
        }

        LayananBk::create([
            'kelas_id' => $kelas->id,
            'guru_bk_id' => auth()->user()->guru_id,
            'siswa_id' => $validated['siswa_id'] ?? null,
            'tanggal' => $validated['tanggal'],
            'jenis_layanan' => $validated['jenis_layanan'],
            'deskripsi_layanan' => $validated['deskripsi_layanan'],
            'hasil_layanan' => $validated['hasil_layanan'] ?? null,
            'rencana_tindak_lanjut' => $validated['rencana_tindak_lanjut'] ?? null,
        ]);

        return redirect()->route('guru_bk_layanan.layanan', ['kelas' => $kelas->id])
            ->with('success', 'Layanan BK berhasil disimpan.');
    }

    public function printLayanan(Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        ['sekolah' => $sekolah, 'guruBkNama' => $guruBkNama, 'guruBkNip' => $guruBkNip, 'kepalaSekolahNama' => $kepalaSekolahNama, 'kepalaSekolahNip' => $kepalaSekolahNip] = $this->getPrintProfileData();

        $layananItems = LayananBk::with('siswa')
            ->where('kelas_id', $kelas->id)
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $todayLabel = Carbon::now()->translatedFormat('d F Y');

        return view('guru_bk_layanan.print_layanan', compact(
            'kelas',
            'sekolah',
            'layananItems',
            'todayLabel',
            'guruBkNama',
            'guruBkNip',
            'kepalaSekolahNama',
            'kepalaSekolahNip'
        ));
    }

    public function printDaftarHadir(Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        ['sekolah' => $sekolah, 'guruBkNama' => $guruBkNama, 'guruBkNip' => $guruBkNip, 'kepalaSekolahNama' => $kepalaSekolahNama, 'kepalaSekolahNip' => $kepalaSekolahNip] = $this->getPrintProfileData();

        $selectedTanggal = request('tanggal');

        $query = LayananBk::with('siswa')
            ->where('kelas_id', $kelas->id)
            ->whereNotNull('siswa_id');

        if (! empty($selectedTanggal)) {
            $query->whereDate('tanggal', $selectedTanggal);
        }

        $daftarHadirItems = $query
            ->orderBy('tanggal', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $todayLabel = Carbon::now()->translatedFormat('d F Y');

        return view('guru_bk_layanan.print_daftar_hadir', compact(
            'kelas',
            'sekolah',
            'daftarHadirItems',
            'selectedTanggal',
            'todayLabel',
            'guruBkNama',
            'guruBkNip',
            'kepalaSekolahNama',
            'kepalaSekolahNip'
        ));
    }

    public function daftarHadir(Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        $selectedTanggal = request('tanggal');

        $query = LayananBk::with('siswa')
            ->where('kelas_id', $kelas->id)
            ->whereNotNull('siswa_id');

        if (! empty($selectedTanggal)) {
            $query->whereDate('tanggal', $selectedTanggal);
        }

        $daftarHadirItems = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        return view('guru_bk_layanan.daftar_hadir', compact('kelas', 'selectedTanggal', 'daftarHadirItems'));
    }

    public function pembinaan(Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        $selectedSiswaId = request('filter_siswa_id');
        $tanggalMulai = request('tanggal_mulai');
        $tanggalSelesai = request('tanggal_selesai');

        $siswaList = Siswa::where('kelas_id', $kelas->id)
            ->orderBy('nama')
            ->get();

        $pembinaanQuery = PembinaanBk::with('siswa')
            ->where('kelas_id', $kelas->id);

        if (! empty($selectedSiswaId)) {
            $pembinaanQuery->where('siswa_id', $selectedSiswaId);
        }

        if (! empty($tanggalMulai)) {
            $pembinaanQuery->whereDate('created_at', '>=', $tanggalMulai);
        }

        if (! empty($tanggalSelesai)) {
            $pembinaanQuery->whereDate('created_at', '<=', $tanggalSelesai);
        }

        $pembinaanItems = $pembinaanQuery
            ->orderBy('created_at', 'desc')
            ->get();

        $startMonth = now()->startOfMonth()->toDateString();
        $endMonth = now()->endOfMonth()->toDateString();

        $akumulasiTerlambatBulanan = DB::table('pelanggaran_siswa as ps')
            ->join('siswa as s', 's.id', '=', 'ps.siswa_id')
            ->where('ps.kelas_id', $kelas->id)
            ->whereDate('ps.tanggal', '>=', $startMonth)
            ->whereDate('ps.tanggal', '<=', $endMonth)
            ->whereIn(DB::raw('LOWER(ps.status_absensi)'), ['terlambat', 'telat'])
            ->select(
                'ps.siswa_id',
                's.nama as nama_siswa',
                DB::raw('COUNT(ps.id) as total_terlambat'),
                DB::raw('SUM(ps.terlambat_menit) as total_menit_terlambat')
            )
            ->groupBy('ps.siswa_id', 's.nama')
            ->orderByDesc('total_terlambat')
            ->orderByDesc('total_menit_terlambat')
            ->get();

        $waliKelasNama = $kelas->waliKelas->nama ?? '-';

        return view('guru_bk_layanan.pembinaan', compact(
            'kelas',
            'siswaList',
            'pembinaanItems',
            'waliKelasNama',
            'selectedSiswaId',
            'tanggalMulai',
            'tanggalSelesai',
            'akumulasiTerlambatBulanan'
        ));
    }

    public function printPembinaan(Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        ['sekolah' => $sekolah, 'guruBkNama' => $guruBkNama, 'guruBkNip' => $guruBkNip, 'kepalaSekolahNama' => $kepalaSekolahNama, 'kepalaSekolahNip' => $kepalaSekolahNip] = $this->getPrintProfileData();

        $selectedSiswaId = request('filter_siswa_id');
        $tanggalMulai = request('tanggal_mulai');
        $tanggalSelesai = request('tanggal_selesai');

        $pembinaanQuery = PembinaanBk::with('siswa')
            ->where('kelas_id', $kelas->id);

        if (! empty($selectedSiswaId)) {
            $pembinaanQuery->where('siswa_id', $selectedSiswaId);
        }

        if (! empty($tanggalMulai)) {
            $pembinaanQuery->whereDate('created_at', '>=', $tanggalMulai);
        }

        if (! empty($tanggalSelesai)) {
            $pembinaanQuery->whereDate('created_at', '<=', $tanggalSelesai);
        }

        $pembinaanItems = $pembinaanQuery
            ->orderBy('created_at', 'asc')
            ->get();

        $selectedSiswa = null;
        if (! empty($selectedSiswaId)) {
            $selectedSiswa = Siswa::where('id', $selectedSiswaId)
                ->where('kelas_id', $kelas->id)
                ->first();
        }

        $todayLabel = Carbon::now()->translatedFormat('d F Y');

        return view('guru_bk_layanan.print_pembinaan', compact(
            'kelas',
            'sekolah',
            'pembinaanItems',
            'selectedSiswa',
            'tanggalMulai',
            'tanggalSelesai',
            'todayLabel',
            'guruBkNama',
            'guruBkNip',
            'kepalaSekolahNama',
            'kepalaSekolahNip'
        ));
    }

    public function kartuKendali(Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        $selectedSiswaId = request('siswa_id');
        $tanggalMulai = request('tanggal_mulai');
        $tanggalSelesai = request('tanggal_selesai');

        $siswaList = Siswa::where('kelas_id', $kelas->id)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nis', 'nisn']);

        $pelanggaranQuery = DB::table('pelanggaran_siswa as ps')
            ->join('siswa as s', 'ps.siswa_id', '=', 's.id')
            ->where('ps.kelas_id', $kelas->id)
            ->select(
                'ps.id',
                'ps.tanggal',
                'ps.siswa_id',
                DB::raw("COALESCE(s.nama, '-') as nama_siswa"),
                DB::raw("COALESCE(s.nis, '-') as nis"),
                DB::raw("COALESCE(s.nisn, '-') as nisn"),
                DB::raw("COALESCE(ps.deskripsi_pelanggaran, '-') as deskripsi_pelanggaran"),
                DB::raw('COALESCE(ps.poin_pelanggaran, 0) as poin_pelanggaran'),
                DB::raw("COALESCE(ps.status_absensi, '-') as status_absensi")
            );

        if (!empty($selectedSiswaId)) {
            $pelanggaranQuery->where('ps.siswa_id', $selectedSiswaId);
        }

        if (!empty($tanggalMulai)) {
            $pelanggaranQuery->whereDate('ps.tanggal', '>=', $tanggalMulai);
        }

        if (!empty($tanggalSelesai)) {
            $pelanggaranQuery->whereDate('ps.tanggal', '<=', $tanggalSelesai);
        }

        $kartuItems = $pelanggaranQuery
            ->orderBy('ps.tanggal', 'asc')
            ->orderBy('ps.id', 'asc')
            ->get();

        $jenisPelanggaranOptions = JenisPelanggaran::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama', 'poin_default']);

        $selectedSiswa = null;
        if (!empty($selectedSiswaId)) {
            $selectedSiswa = $siswaList->firstWhere('id', (int) $selectedSiswaId);
        }

        return view('guru_bk_layanan.kartu_kendali', compact(
            'kelas',
            'siswaList',
            'selectedSiswaId',
            'selectedSiswa',
            'tanggalMulai',
            'tanggalSelesai',
            'kartuItems',
            'jenisPelanggaranOptions'
        ));
    }

    public function storeKartuKendali(Request $request, Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tanggal' => 'required|date',
            'status_absensi' => 'required|in:hadir,terlambat,sakit,izin,alpa',
            'jenis_pelanggaran_id' => 'required|exists:jenis_pelanggaran,id',
            'deskripsi_pelanggaran' => 'nullable|string|max:1000',
            'poin_pelanggaran' => 'nullable|integer|min:0|max:1000',
            'terlambat_menit' => 'nullable|integer|min:0|max:1000',
        ]);

        $siswa = Siswa::where('id', $validated['siswa_id'])
            ->where('kelas_id', $kelas->id)
            ->first();

        if (! $siswa) {
            return back()->withInput()->withErrors([
                'siswa_id' => 'Siswa yang dipilih bukan bagian dari kelas binaan ini.',
            ]);
        }

        $tahunAjaranAktif = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semesterAktif = DB::table('semester')->where('is_active', 1)->first();

        $statusMap = [
            'alpa' => 'absen',
            'terlambat' => 'terlambat',
            'hadir' => 'hadir',
            'izin' => 'izin',
            'sakit' => 'sakit',
        ];

        $statusAbsensi = $statusMap[$validated['status_absensi']] ?? 'hadir';
        $terlambatMenit = $statusAbsensi === 'terlambat'
            ? (int) ($validated['terlambat_menit'] ?? 0)
            : 0;

        $jenisPelanggaran = JenisPelanggaran::find($validated['jenis_pelanggaran_id']);
        $pointFinal = $validated['poin_pelanggaran'] ?? null;
        if ($pointFinal === null && $jenisPelanggaran) {
            $pointFinal = (int) $jenisPelanggaran->poin_default;
        }
        if ($pointFinal === null) {
            $pointFinal = 0;
        }

        $deskripsiFinal = $jenisPelanggaran->nama ?? 'Pelanggaran';
        if (!empty($validated['deskripsi_pelanggaran'])) {
            $deskripsiFinal .= ' - ' . $validated['deskripsi_pelanggaran'];
        }

        DB::table('pelanggaran_siswa')->updateOrInsert(
            [
                'kelas_id' => $kelas->id,
                'siswa_id' => $validated['siswa_id'],
                'tanggal' => $validated['tanggal'],
            ],
            [
                'guru_piket_id' => auth()->user()->guru_id,
                'status_absensi' => $statusAbsensi,
                'deskripsi_pelanggaran' => $deskripsiFinal,
                'poin_pelanggaran' => (int) $pointFinal,
                'terlambat_menit' => $terlambatMenit,
                'waktu_input_pelanggaran' => now(),
                'tahun_ajaran_id' => $tahunAjaranAktif->id ?? null,
                'semester_id' => $semesterAktif->id ?? null,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return redirect()->route('guru_bk_layanan.kartu_kendali', [
            'kelas' => $kelas->id,
            'siswa_id' => $validated['siswa_id'],
        ])->with('success', 'Data pelanggaran dan point berhasil disimpan.');
    }

    public function printKartuKendali(Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        ['sekolah' => $sekolah, 'guruBkNama' => $guruBkNama, 'guruBkNip' => $guruBkNip, 'kepalaSekolahNama' => $kepalaSekolahNama, 'kepalaSekolahNip' => $kepalaSekolahNip] = $this->getPrintProfileData();

        $selectedSiswaId = request('siswa_id');
        $tanggalMulai = request('tanggal_mulai');
        $tanggalSelesai = request('tanggal_selesai');

        $selectedSiswa = null;
        if (!empty($selectedSiswaId)) {
            $selectedSiswa = Siswa::where('id', $selectedSiswaId)
                ->where('kelas_id', $kelas->id)
                ->first();
        }

        $pelanggaranQuery = DB::table('pelanggaran_siswa as ps')
            ->where('ps.kelas_id', $kelas->id)
            ->select(
                'ps.id',
                'ps.tanggal',
                DB::raw("COALESCE(ps.deskripsi_pelanggaran, '-') as deskripsi_pelanggaran"),
                DB::raw('COALESCE(ps.poin_pelanggaran, 0) as poin_pelanggaran'),
                DB::raw("COALESCE(ps.status_absensi, '-') as status_absensi")
            );

        if (!empty($selectedSiswaId)) {
            $pelanggaranQuery->where('ps.siswa_id', $selectedSiswaId);
        }

        if (!empty($tanggalMulai)) {
            $pelanggaranQuery->whereDate('ps.tanggal', '>=', $tanggalMulai);
        }

        if (!empty($tanggalSelesai)) {
            $pelanggaranQuery->whereDate('ps.tanggal', '<=', $tanggalSelesai);
        }

        $kartuItems = $pelanggaranQuery
            ->orderBy('ps.tanggal', 'asc')
            ->orderBy('ps.id', 'asc')
            ->get();

        $todayLabel = Carbon::now()->translatedFormat('d F Y');

        return view('guru_bk_layanan.print_kartu_kendali', compact(
            'kelas',
            'sekolah',
            'guruBkNama',
            'guruBkNip',
            'kepalaSekolahNama',
            'kepalaSekolahNip',
            'selectedSiswa',
            'kartuItems',
            'tanggalMulai',
            'tanggalSelesai',
            'todayLabel'
        ));
    }

    public function rekapAbsensiSiswa(Request $request, Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
        ]);

        $siswa = Siswa::where('id', $validated['siswa_id'])
            ->where('kelas_id', $kelas->id)
            ->first();

        if (! $siswa) {
            return response()->json([
                'message' => 'Siswa tidak ditemukan di kelas binaan ini.',
            ], 422);
        }

        $rekap = $this->calculateRekapAbsensi($kelas->id, (int) $siswa->id);

        return response()->json([
            'hadir' => $rekap['hadir'],
            'sakit' => $rekap['sakit'],
            'izin' => $rekap['izin'],
            'alpa' => $rekap['alpa'],
            'terlambat' => $rekap['terlambat'],
            'bukti_dukung_absensi' => $this->buildAbsensiSummaryText($rekap),
            'laporan_guru' => $this->buildGuruReportSummaryText($kelas->id, (int) $siswa->id),
            'laporan_wali_kelas' => $this->buildWaliKelasReportSummaryText($kelas->id, (int) $siswa->id),
            'wali_kelas_nama' => $kelas->waliKelas->nama ?? '-',
        ]);
    }

    public function storePembinaan(Request $request, Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'deskripsi_permasalahan' => 'required|string',
            'penanganan' => 'required|string',
            'tindak_lanjut' => 'nullable|string',
            'bukti_dukung_absensi' => 'nullable|string',
            'laporan_guru' => 'nullable|string',
            'laporan_wali_kelas' => 'nullable|string',
            'bukti_dukung_files' => 'nullable|array',
            'bukti_dukung_files.*' => 'image|mimes:jpeg,png,jpg,webp|max:4096',
            'bukti_dukung_kamera' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ]);

        $siswa = Siswa::where('id', $validated['siswa_id'])
            ->where('kelas_id', $kelas->id)
            ->first();

        if (! $siswa) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['siswa_id' => 'Siswa yang dipilih bukan bagian dari kelas binaan ini.']);
        }

        $rekap = $this->calculateRekapAbsensi($kelas->id, (int) $siswa->id);

        $uploadedPaths = [];
        if ($request->hasFile('bukti_dukung_files')) {
            foreach ($request->file('bukti_dukung_files') as $file) {
                $uploadedPaths[] = $file->store('pembinaan_bk', 'public');
            }
        }

        if ($request->hasFile('bukti_dukung_kamera')) {
            $uploadedPaths[] = $request->file('bukti_dukung_kamera')->store('pembinaan_bk', 'public');
        }

        PembinaanBk::create([
            'kelas_id' => $kelas->id,
            'guru_bk_id' => auth()->user()->guru_id,
            'siswa_id' => $siswa->id,
            'wali_kelas_nama' => $kelas->waliKelas->nama ?? null,
            'hadir' => $rekap['hadir'],
            'sakit' => $rekap['sakit'],
            'izin' => $rekap['izin'],
            'alpa' => $rekap['alpa'],
            'terlambat' => $rekap['terlambat'],
            'deskripsi_permasalahan' => $validated['deskripsi_permasalahan'],
            'penanganan' => $validated['penanganan'],
            'tindak_lanjut' => $validated['tindak_lanjut'] ?? null,
            'bukti_dukung_absensi' => !empty($validated['bukti_dukung_absensi'])
                ? $validated['bukti_dukung_absensi']
                : $this->buildAbsensiSummaryText($rekap),
            'laporan_guru' => !empty($validated['laporan_guru'])
                ? $validated['laporan_guru']
                : $this->buildGuruReportSummaryText($kelas->id, (int) $siswa->id),
            'laporan_wali_kelas' => !empty($validated['laporan_wali_kelas'])
                ? $validated['laporan_wali_kelas']
                : $this->buildWaliKelasReportSummaryText($kelas->id, (int) $siswa->id),
            'bukti_dukung_files' => $uploadedPaths,
        ]);

        return redirect()->route('guru_bk_layanan.pembinaan', ['kelas' => $kelas->id])
            ->with('success', 'Data pembinaan BK berhasil disimpan.');
    }

    public function tindakLanjut(Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        $user = auth()->user();
        $guruBkNama = $user->guru->nama ?? $user->name ?? '-';
        $waliKelasNama = $kelas->waliKelas->nama ?? '-';

        $kelasBinaan = Kelas::query()
            ->where('guru_bk_id', $user->guru_id)
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas']);

        $selectedSiswaId = (int) (old('siswa_id') ?: request('siswa_id'));

        $siswaList = Siswa::query()
            ->where('kelas_id', $kelas->id)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nis', 'nisn']);

        $siswaIds = $siswaList->pluck('id')->all();

        $layananBySiswa = collect();
        $pelanggaranBySiswa = collect();
        $absensiBySiswa = collect();
        $laporanGuruBySiswa = [];
        $laporanWaliBySiswa = [];

        $tahunAjaranAktif = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semesterAktif = DB::table('semester')->where('is_active', 1)->first();

        if (! empty($siswaIds)) {
            $layananBySiswa = DB::table('layanan_bk')
                ->where('kelas_id', $kelas->id)
                ->whereIn('siswa_id', $siswaIds)
                ->select('siswa_id', DB::raw('COUNT(id) as total_layanan'))
                ->groupBy('siswa_id')
                ->get()
                ->keyBy('siswa_id');

            $pelanggaranBySiswa = DB::table('pelanggaran_siswa')
                ->where('kelas_id', $kelas->id)
                ->whereIn('siswa_id', $siswaIds)
                ->when($tahunAjaranAktif, function ($query) use ($tahunAjaranAktif) {
                    $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
                })
                ->when($semesterAktif, function ($query) use ($semesterAktif) {
                    $query->where('semester_id', $semesterAktif->id);
                })
                ->select(
                    'siswa_id',
                    DB::raw('COUNT(id) as total_pelanggaran'),
                    DB::raw("SUM(CASE WHEN LOWER(status_absensi) IN ('terlambat', 'telat') THEN 1 ELSE 0 END) as total_terlambat"),
                    DB::raw("SUM(CASE WHEN LOWER(status_absensi) IN ('terlambat', 'telat') THEN terlambat_menit ELSE 0 END) as total_menit_terlambat")
                )
                ->groupBy('siswa_id')
                ->get()
                ->keyBy('siswa_id');

            $absensiBySiswa = DB::table('absensi_siswa as asi')
                ->join('absensi_kelas as ak', 'ak.id', '=', 'asi.absensi_kelas_id')
                ->where('ak.kelas_id', $kelas->id)
                ->whereIn('asi.siswa_id', $siswaIds)
                ->when($tahunAjaranAktif, function ($query) use ($tahunAjaranAktif) {
                    $query->where('ak.tahun_ajaran_id', $tahunAjaranAktif->id);
                })
                ->when($semesterAktif, function ($query) use ($semesterAktif) {
                    $query->where('ak.semester_id', $semesterAktif->id);
                })
                ->select(
                    'asi.siswa_id',
                    DB::raw("SUM(CASE WHEN LOWER(asi.status) = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                    DB::raw("SUM(CASE WHEN LOWER(asi.status) = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                    DB::raw("SUM(CASE WHEN LOWER(asi.status) IN ('izin', 'ijin') THEN 1 ELSE 0 END) as izin"),
                    DB::raw("SUM(CASE WHEN LOWER(asi.status) IN ('alpa', 'alpha', 'alfa', 'absen') THEN 1 ELSE 0 END) as alpa"),
                    DB::raw("SUM(CASE WHEN LOWER(asi.status) IN ('terlambat', 'telat') THEN 1 ELSE 0 END) as terlambat")
                )
                ->groupBy('asi.siswa_id')
                ->get()
                ->keyBy('siswa_id');

            $laporanRows = DB::table('laporan_siswa_guru')
                ->where('kelas_id', $kelas->id)
                ->whereIn('siswa_id', $siswaIds)
                ->orderByDesc('created_at')
                ->get(['siswa_id', 'absensi_kelas_id', 'deskripsi_permasalahan', 'created_at']);

            foreach ($laporanRows as $row) {
                if (! isset($laporanGuruBySiswa[$row->siswa_id]) && ! empty($row->absensi_kelas_id)) {
                    $laporanGuruBySiswa[$row->siswa_id] = [
                        'tanggal' => Carbon::parse($row->created_at)->format('d/m/Y'),
                        'deskripsi' => (string) $row->deskripsi_permasalahan,
                    ];
                }

                if (! isset($laporanWaliBySiswa[$row->siswa_id]) && empty($row->absensi_kelas_id)) {
                    $laporanWaliBySiswa[$row->siswa_id] = [
                        'tanggal' => Carbon::parse($row->created_at)->format('d/m/Y'),
                        'deskripsi' => (string) $row->deskripsi_permasalahan,
                    ];
                }

                if (
                    isset($laporanGuruBySiswa[$row->siswa_id])
                    && isset($laporanWaliBySiswa[$row->siswa_id])
                ) {
                    continue;
                }
            }
        }

        $ringkasanSiswa = $siswaList->map(function ($siswa) use ($layananBySiswa, $pelanggaranBySiswa, $absensiBySiswa, $laporanGuruBySiswa, $laporanWaliBySiswa) {
            $layanan = $layananBySiswa->get($siswa->id);
            $pelanggaran = $pelanggaranBySiswa->get($siswa->id);
            $absensi = $absensiBySiswa->get($siswa->id);
            $lapGuru = $laporanGuruBySiswa[$siswa->id] ?? null;
            $lapWali = $laporanWaliBySiswa[$siswa->id] ?? null;

            return (object) [
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'total_layanan' => (int) ($layanan->total_layanan ?? 0),
                'total_pelanggaran' => (int) ($pelanggaran->total_pelanggaran ?? 0),
                'total_terlambat' => (int) ($pelanggaran->total_terlambat ?? 0),
                'total_menit_terlambat' => (int) ($pelanggaran->total_menit_terlambat ?? 0),
                'hadir' => (int) ($absensi->hadir ?? 0),
                'sakit' => (int) ($absensi->sakit ?? 0),
                'izin' => (int) ($absensi->izin ?? 0),
                'alpa' => (int) ($absensi->alpa ?? 0),
                'terlambat_absensi' => (int) ($absensi->terlambat ?? 0),
                'laporan_guru' => $lapGuru,
                'laporan_wali' => $lapWali,
            ];
        });

        $selectedSiswa = $ringkasanSiswa->firstWhere('id', $selectedSiswaId);
        if (! $selectedSiswa && $ringkasanSiswa->isNotEmpty()) {
            $selectedSiswa = $ringkasanSiswa->first();
            $selectedSiswaId = $selectedSiswa->id;
        }

        $tindakLanjutItems = TindakLanjutBk::query()
            ->where('kelas_id', $kelas->id)
            ->orderByDesc('created_at')
            ->get();

        return view('guru_bk_layanan.tindak_lanjut', compact(
            'kelas',
            'kelasBinaan',
            'waliKelasNama',
            'guruBkNama',
            'ringkasanSiswa',
            'selectedSiswaId',
            'selectedSiswa',
            'tindakLanjutItems'
        ));
    }

    public function storeTindakLanjut(Request $request, Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'waktu' => 'required|string|max:255',
            'rencana_kegiatan' => 'required|array|min:1',
            'rencana_kegiatan.*' => 'nullable|string|max:1000',
            'waktu_tempat' => 'required|array|min:1',
            'waktu_tempat.*' => 'nullable|string|max:1000',
            'pihak_terkait' => 'required|array|min:1',
            'pihak_terkait.*' => 'nullable|string|max:500',
        ]);

        $siswa = Siswa::query()
            ->where('id', $validated['siswa_id'])
            ->where('kelas_id', $kelas->id)
            ->first();

        if (! $siswa) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['siswa_id' => 'Siswa tidak berada di kelas binaan ini.']);
        }

        $rencanaItems = [];
        $kegiatanList = $validated['rencana_kegiatan'] ?? [];
        $waktuTempatList = $validated['waktu_tempat'] ?? [];
        $pihakTerkaitList = $validated['pihak_terkait'] ?? [];
        $totalBaris = max(count($kegiatanList), count($waktuTempatList), count($pihakTerkaitList));

        for ($index = 0; $index < $totalBaris; $index++) {
            $kegiatan = trim((string) ($kegiatanList[$index] ?? ''));
            $waktuTempat = trim((string) ($waktuTempatList[$index] ?? ''));
            $pihakTerkait = trim((string) ($pihakTerkaitList[$index] ?? ''));

            if ($kegiatan === '' && $waktuTempat === '' && $pihakTerkait === '') {
                continue;
            }

            $rencanaItems[] = [
                'no' => count($rencanaItems) + 1,
                'rencana_kegiatan' => $kegiatan,
                'waktu_tempat' => $waktuTempat,
                'pihak_terkait' => $pihakTerkait,
            ];
        }

        if (empty($rencanaItems)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['rencana_kegiatan' => 'Isi minimal satu rencana kegiatan tindak lanjut.']);
        }

        $user = auth()->user();
        $guruBkNama = $user->guru->nama ?? $user->name ?? '-';

        TindakLanjutBk::create([
            'kelas_id' => $kelas->id,
            'siswa_id' => $siswa->id,
            'guru_bk_id' => $user->guru_id,
            'nama_siswa' => $siswa->nama,
            'nama_kelas' => $kelas->nama_kelas,
            'nis' => $siswa->nis,
            'nisn' => $siswa->nisn,
            'nama_wali_kelas' => $kelas->waliKelas->nama ?? null,
            'nama_guru_bk' => $guruBkNama,
            'waktu' => $validated['waktu'],
            'nama_penyusun' => $guruBkNama,
            'rencana_items' => $rencanaItems,
        ]);

        return redirect()->route('guru_bk_layanan.tindak_lanjut', [
            'kelas' => $kelas->id,
            'siswa_id' => $siswa->id,
        ])->with('success', 'Rencana tindak lanjut berhasil disimpan.');
    }

    public function printTindakLanjut(Kelas $kelas, TindakLanjutBk $tindakLanjut)
    {
        $this->authorizeKelasBinaan($kelas);

        if ((int) $tindakLanjut->kelas_id !== (int) $kelas->id) {
            abort(404, 'Data tindak lanjut tidak ditemukan pada kelas ini.');
        }

        ['sekolah' => $sekolah, 'guruBkNama' => $guruBkNama, 'guruBkNip' => $guruBkNip, 'kepalaSekolahNama' => $kepalaSekolahNama, 'kepalaSekolahNip' => $kepalaSekolahNip] = $this->getPrintProfileData();

        $todayLabel = Carbon::now()->translatedFormat('d F Y');

        return view('guru_bk_layanan.print_tindak_lanjut', compact(
            'kelas',
            'tindakLanjut',
            'sekolah',
            'todayLabel',
            'guruBkNama',
            'guruBkNip',
            'kepalaSekolahNama',
            'kepalaSekolahNip'
        ));
    }

    public function pdfTindakLanjut(Kelas $kelas, TindakLanjutBk $tindakLanjut)
    {
        $this->authorizeKelasBinaan($kelas);

        if ((int) $tindakLanjut->kelas_id !== (int) $kelas->id) {
            abort(404, 'Data tindak lanjut tidak ditemukan pada kelas ini.');
        }

        ['sekolah' => $sekolah, 'guruBkNama' => $guruBkNama, 'guruBkNip' => $guruBkNip, 'kepalaSekolahNama' => $kepalaSekolahNama, 'kepalaSekolahNip' => $kepalaSekolahNip] = $this->getPrintProfileData();

        $todayLabel = Carbon::now()->translatedFormat('d F Y');

        $pdf = \PDF::loadView('guru_bk_layanan.print_tindak_lanjut', compact(
            'kelas',
            'tindakLanjut',
            'sekolah',
            'todayLabel',
            'guruBkNama',
            'guruBkNip',
            'kepalaSekolahNama',
            'kepalaSekolahNip'
        ))->setPaper('a4', 'portrait');

        $safeNamaSiswa = preg_replace('/[^A-Za-z0-9\-_]/', '_', (string) $tindakLanjut->nama_siswa);
        $filename = 'Rencana_Tindak_Lanjut_' . ($safeNamaSiswa ?: 'Siswa') . '_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    private function getPrintProfileData(): array
    {
        $sekolah = Sekolah::first();
        $user = auth()->user();

        $guruBkNama = $user->guru->nama ?? $user->name ?? 'NAMA';
        $guruBkNip = $user->guru->nip ?? $user->nip ?? '-';

        $kepalaSekolah = KepalaSekolah::query()
            ->where('status', 'Aktif')
            ->orderBy('tanggal_mulai_jabatan', 'desc')
            ->first();

        if (! $kepalaSekolah) {
            $kepalaSekolah = KepalaSekolah::query()
                ->orderBy('tanggal_mulai_jabatan', 'desc')
                ->first();
        }

        $kepalaSekolahNama = $kepalaSekolah->nama ?? ($sekolah->nama_kepala_sekolah ?? 'NAMA');
        $kepalaSekolahNip = $kepalaSekolah->nip ?? '-';

        return compact('sekolah', 'guruBkNama', 'guruBkNip', 'kepalaSekolahNama', 'kepalaSekolahNip');
    }

    private function calculateRekapAbsensi(int $kelasId, int $siswaId): array
    {
        $tahunAktif = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semesterAktif = DB::table('semester')->where('is_active', 1)->first();

        $rows = DB::table('absensi_siswa')
            ->join('absensi_kelas', 'absensi_kelas.id', '=', 'absensi_siswa.absensi_kelas_id')
            ->where('absensi_kelas.kelas_id', $kelasId)
            ->where('absensi_siswa.siswa_id', $siswaId)
            ->when($tahunAktif, function ($query) use ($tahunAktif) {
                $query->where('absensi_kelas.tahun_ajaran_id', $tahunAktif->id);
            })
            ->when($semesterAktif, function ($query) use ($semesterAktif) {
                $query->where('absensi_kelas.semester_id', $semesterAktif->id);
            })
            ->select('absensi_siswa.status')
            ->get();

        $normalize = function ($status) {
            return strtolower(trim((string) $status));
        };

        return [
            'hadir' => $rows->filter(fn ($r) => $normalize($r->status) === 'hadir')->count(),
            'sakit' => $rows->filter(fn ($r) => $normalize($r->status) === 'sakit')->count(),
            'izin' => $rows->filter(fn ($r) => in_array($normalize($r->status), ['izin', 'ijin'], true))->count(),
            'alpa' => $rows->filter(fn ($r) => in_array($normalize($r->status), ['alpa', 'alpha', 'alfa', 'absen'], true))->count(),
            'terlambat' => $rows->filter(fn ($r) => in_array($normalize($r->status), ['terlambat', 'telat'], true))->count(),
        ];
    }

    private function buildAbsensiSummaryText(array $rekap): string
    {
        return sprintf(
            'Rekap Absensi: Hadir %d, Sakit %d, Izin %d, Alpa %d, Terlambat %d.',
            $rekap['hadir'] ?? 0,
            $rekap['sakit'] ?? 0,
            $rekap['izin'] ?? 0,
            $rekap['alpa'] ?? 0,
            $rekap['terlambat'] ?? 0
        );
    }

    private function buildGuruReportSummaryText(int $kelasId, int $siswaId): ?string
    {
        $laporan = LaporanSiswaGuru::query()
            ->with('guruPelapor')
            ->where('kelas_id', $kelasId)
            ->where('siswa_id', $siswaId)
            ->whereNotNull('absensi_kelas_id')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($laporan->isEmpty()) {
            return null;
        }

        return $laporan->map(function ($item, $index) {
            $tanggal = optional($item->created_at)->format('d/m/Y');
            $guruNama = $item->guruPelapor->nama ?? 'Guru';
            return ($index + 1) . '. [' . $tanggal . '] ' . $guruNama . ': ' . $item->deskripsi_permasalahan;
        })->implode("\n");
    }

    private function buildWaliKelasReportSummaryText(int $kelasId, int $siswaId): ?string
    {
        $laporan = LaporanSiswaGuru::query()
            ->with('guruPelapor')
            ->where('kelas_id', $kelasId)
            ->where('siswa_id', $siswaId)
            ->whereNull('absensi_kelas_id')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        if ($laporan->isEmpty()) {
            return null;
        }

        return $laporan->map(function ($item, $index) {
            $tanggal = optional($item->created_at)->format('d/m/Y');
            $waliNama = $item->guruPelapor->nama ?? 'Wali Kelas';
            return ($index + 1) . '. [' . $tanggal . '] ' . $waliNama . ': ' . $item->deskripsi_permasalahan;
        })->implode("\n");
    }
}
