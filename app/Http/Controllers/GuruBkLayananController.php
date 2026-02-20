<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKelas;
use App\Models\Kelas;
use App\Models\KepalaSekolah;
use App\Models\LayananBk;
use App\Models\Sekolah;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;

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

        return view('guru_bk_layanan.pembinaan', compact('kelas'));
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
}
