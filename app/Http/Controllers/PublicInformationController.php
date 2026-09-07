<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKelas;
use App\Models\AbsensiGuru;
use App\Models\AgendaKelas;
use App\Models\Guru;
use App\Models\JadwalKbm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicInformationController extends Controller
{
    public function rekapAbsensi(Request $request)
    {
        $tanggal = $request->filled('tanggal') ? Carbon::parse($request->input('tanggal')) : Carbon::today();
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        $absensi = collect();
        if ($tahun && $semester) {
            $absensi = AbsensiKelas::with(['kelas', 'guru', 'jamBelajar', 'absensiSiswa.siswa'])
                ->where('tahun_ajaran_id', $tahun->id)
                ->where('semester_id', $semester->id)
                ->whereDate('tanggal', $tanggal)
                ->orderBy('kelas_id')
                ->orderBy('jam_belajar_id')
                ->get();
        }

        $guru = DB::table('guru')
            ->where('is_active', 1)
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode_guru']);

        $normalizeStatus = static function ($status): string {
            $status = strtolower(trim((string) $status));

            return match ($status) {
                'alpha', 'absen' => 'alpa',
                'telat' => 'terlambat',
                default => $status ?: 'tanpa_status',
            };
        };

        $rowsPerKelas = $absensi->groupBy('kelas_id')->map(function ($items) {
            return $items->flatMap->absensiSiswa
                ->sortByDesc('id')
                ->unique('siswa_id')
                ->values();
        });

        $rekapPerKelas = $absensi->groupBy('kelas_id')->map(function ($items) use ($normalizeStatus, $rowsPerKelas) {
            $rows = $rowsPerKelas->get($items->first()->kelas_id, collect());
            $counts = $rows->groupBy(fn ($row) => $normalizeStatus($row->status))->map->count();
            $total = $rows->count();

            return (object) [
                'kelas' => $items->first()->kelas,
                'total' => $total,
                'hadir' => (int) ($counts['hadir'] ?? 0),
                'sakit' => (int) ($counts['sakit'] ?? 0),
                'izin' => (int) ($counts['izin'] ?? 0),
                'alpa' => (int) ($counts['alpa'] ?? 0),
                'terlambat' => (int) ($counts['terlambat'] ?? 0),
                'persentase' => $total > 0 ? round(($counts['hadir'] ?? 0) / $total * 100, 2) : 0,
            ];
        })->sortBy(fn ($item) => $item->kelas->nama_kelas ?? '')->values();

        $statusLabels = ['hadir', 'sakit', 'izin', 'alpa', 'terlambat'];
        $buatStatistik = function ($rows) use ($normalizeStatus, $statusLabels): array {
            $counts = $rows->groupBy(fn ($row) => $normalizeStatus($row->status))->map->count();

            return collect($statusLabels)->mapWithKeys(fn ($status) => [$status => (int) ($counts[$status] ?? 0)])->all();
        };
        $semuaSiswa = $rowsPerKelas->flatten(1);
        $statistikKehadiran = ['Semua Siswa' => $buatStatistik($semuaSiswa)];
        foreach ([10, 11, 12] as $tingkat) {
            $kelasTingkat = $absensi->filter(function ($item) use ($tingkat) {
                return preg_match('/^' . $tingkat . '(?:\D|$)/', trim((string) ($item->kelas->nama_kelas ?? '')));
            })->pluck('kelas_id')->unique();
            $rowsTingkat = $kelasTingkat->flatMap(fn ($kelasId) => $rowsPerKelas->get($kelasId, collect()));
            $statistikKehadiran['Kelas ' . $tingkat] = $buatStatistik($rowsTingkat);
        }

        $detailKelas = null;
        $detailRows = collect();
        if ($request->filled('kelas_id')) {
            $detailKelas = $rekapPerKelas->first(fn ($item) => (string) $item->kelas->id === (string) $request->input('kelas_id'))?->kelas;
            $detailRows = $rowsPerKelas->get((int) $request->input('kelas_id'), collect());
        }

        return view('public.rekap_absensi', compact('absensi', 'guru', 'tanggal', 'tahun', 'semester', 'rekapPerKelas', 'detailKelas', 'detailRows', 'statistikKehadiran', 'rowsPerKelas'));
    }

    public function agendaKelas(Request $request)
    {
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        $tanggal = $request->filled('tanggal') ? Carbon::parse($request->input('tanggal')) : Carbon::today();

        $agenda = collect();
        if ($tahun && $semester) {
            $agenda = AgendaKelas::with(['kelas', 'guru', 'jamBelajar'])
                ->where('tahun_ajaran_id', $tahun->id)
                ->where('semester_id', $semester->id)
                ->when($request->filled('tanggal'), fn ($query) => $query->whereDate('tanggal', $tanggal))
                ->orderByDesc('tanggal')
                ->orderBy('kelas_id')
                ->orderBy('jam_belajar_id')
                ->get();
        }

        $guru = $agenda->pluck('guru')->filter()->unique('id')->sortBy('nama')->values();

        return view('public.agenda_kelas', compact('agenda', 'guru', 'tanggal', 'tahun', 'semester'));
    }

    public function rekapAbsensiGuru(Request $request)
    {
        $tanggal = $request->filled('tanggal') ? Carbon::parse($request->input('tanggal')) : Carbon::today();
        $guru = Guru::query()
            ->where('is_active', 1)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip', 'kode_guru']);
        $absensi = AbsensiGuru::query()
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('guru_id');

        $statusCounts = $guru->map(function ($item) use ($absensi) {
            return strtolower((string) ($absensi->get($item->id)->status ?? 'belum_dicatat'));
        })->countBy();

        return view('public.rekap_absensi_guru', compact('guru', 'absensi', 'tanggal', 'statusCounts'));
    }

    public function jadwalPembelajaranDaring(Request $request)
    {
        $tanggal = $request->filled('tanggal') ? Carbon::parse($request->input('tanggal')) : Carbon::today();
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        $hariMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
        ];

        $jadwal = collect();
        $agenda = collect();
        if ($tahun && $semester) {
            $hari = $hariMap[$tanggal->format('l')] ?? $tanggal->format('l');
            $jadwal = JadwalKbm::with(['kelas', 'guru', 'mataPelajaran', 'jamBelajar'])
                ->where('hari', $hari)
                ->where('tahun_ajaran_id', $tahun->id)
                ->where('semester_id', $semester->id)
                ->orderBy('kelas_id')
                ->orderBy('jam_ke')
                ->get();

            $agenda = AgendaKelas::whereDate('tanggal', $tanggal)
                ->where('tahun_ajaran_id', $tahun->id)
                ->where('semester_id', $semester->id)
                ->get()
                ->keyBy(fn ($item) => $item->kelas_id . '-' . $item->guru_id . '-' . $item->jam_belajar_id);
        }

        $jadwalPerKelas = $jadwal->groupBy(fn ($item) => $item->kelas->nama_kelas ?? 'Kelas');

        return view('public.jadwal_pembelajaran_daring', compact('jadwalPerKelas', 'agenda', 'tanggal', 'tahun', 'semester'));
    }
}