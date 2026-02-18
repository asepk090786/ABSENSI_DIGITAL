<?php

namespace App\Http\Controllers;

use App\Models\AgendaKelas;
use App\Models\AgendaGuru;
use App\Models\Guru;
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

        // Get all agenda kelas for the selected month (source utama agenda guru)
        $agendaList = AgendaKelas::where('guru_id', $guru->id)
            ->where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id)
            ->whereYear('tanggal', $tahunFilter)
            ->whereMonth('tanggal', $bulan)
            ->with(['jamBelajar', 'kelas'])
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_belajar_id', 'asc')
            ->get();

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

        // Get jam belajar
        $jamBelajar = DB::table('jam_belajar')->get();

        // Get selected tanggal jika ada dari request
        $selectedTanggal = $request->get('tanggal', now()->format('Y-m-d'));

        return view('agenda_guru.create', compact(
            'guru',
            'jamBelajar',
            'selectedTanggal'
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
        ], [
            'tanggal.required' => 'Tanggal harus diisi',
            'jam_belajar_id.required' => 'Jam pelajaran harus dipilih',
            'kegiatan.required' => 'Kegiatan harus diisi',
        ]);

        // Get active tahun and semester
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        // Create agenda
        AgendaGuru::create([
            'guru_id' => $guru->id,
            'jam_belajar_id' => $validated['jam_belajar_id'],
            'tanggal' => $validated['tanggal'],
            'kegiatan' => $validated['kegiatan'],
            'tahun_ajaran_id' => $tahun->id,
            'semester_id' => $semester->id,
        ]);

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

        $jamBelajar = DB::table('jam_belajar')->get();

        return view('agenda_guru.edit', compact(
            'agendaGuru',
            'jamBelajar'
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

        // Validate
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jam_belajar_id' => 'required|exists:jam_belajar,id',
            'kegiatan' => 'required|string|max:1000',
        ], [
            'tanggal.required' => 'Tanggal harus diisi',
            'jam_belajar_id.required' => 'Jam pelajaran harus dipilih',
            'kegiatan.required' => 'Kegiatan harus diisi',
        ]);

        // Update
        $agendaGuru->update($validated);

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

        $agendaGuru->delete();

        return redirect()->route('agenda_guru.index')
            ->with('success', 'Agenda guru berhasil dihapus');
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

        // Get agenda kelas (source utama agenda guru)
        $agendaList = AgendaKelas::where('guru_id', $guru->id)
            ->where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id)
            ->whereYear('tanggal', $tahunFilter)
            ->whereMonth('tanggal', $bulan)
            ->with(['jamBelajar', 'kelas'])
            ->orderBy('tanggal', 'asc')
            ->orderBy('jam_belajar_id', 'asc')
            ->get();

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

        // Generate PDF view
        return view('agenda_guru.export', compact(
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
        ));
    }
}
