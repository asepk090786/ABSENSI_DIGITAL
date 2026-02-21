<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKelas;
use App\Models\Kelas;
use App\Models\KepalaSekolah;
use App\Models\LayananBk;
use App\Models\LaporanSiswaGuru;
use App\Models\PembinaanBk;
use App\Models\Sekolah;
use App\Models\Siswa;
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

    public function menu(Kelas $kelas)
    {
        $this->authorizeKelasBinaan($kelas);

        return view('guru_bk_layanan.menu', compact('kelas'));
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

        $waliKelasNama = $kelas->waliKelas->nama ?? '-';

        return view('guru_bk_layanan.pembinaan', compact(
            'kelas',
            'siswaList',
            'pembinaanItems',
            'waliKelasNama',
            'selectedSiswaId',
            'tanggalMulai',
            'tanggalSelesai'
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

        return view('guru_bk_layanan.tindak_lanjut', compact('kelas'));
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
