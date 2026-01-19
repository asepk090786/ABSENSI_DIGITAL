<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AbsensiKelas;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\JamBelajar;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\JadwalKbm;

class AbsensiController extends Controller
{
    public function index()
    {
        $tahun = DB::table('tahun_ajaran')->where('is_active',1)->first();
        $semester = DB::table('semester')->where('is_active',1)->first();

        if (! $tahun || ! $semester) {
            $items = collect();
            return view('absensi.index', compact('items'))
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $user = auth()->user();
        $query = AbsensiKelas::with(['kelas', 'guru', 'jamBelajar', 'tahunAjaran', 'semester', 'absensiSiswa'])
            ->where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id);
        
        // Filter by guru_id if user is a teacher (not admin or kepala sekolah)
        if ($user->guru_id && !in_array(strtolower($user->role->role_name ?? ''), ['admin', 'kepala sekolah'])) {
            $query->where('guru_id', $user->guru_id);
        }
        
        $items = $query->orderBy('tanggal', 'desc')->get();
        
        // Get quick access classes for teacher
        $kelasQuickAccess = collect();
        if ($user->guru_id && !in_array(strtolower($user->role->role_name ?? ''), ['admin', 'kepala sekolah'])) {
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
        }
        
        return view('absensi.index', compact('items', 'kelasQuickAccess'));
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
        $selectedKelasId = $request->get('kelas_id');
        $selectedJamBelajarId = null;
        $isQuickAccess = !empty($selectedKelasId);
        
        // Get jadwal for current user if they are a teacher
        if ($user->guru_id && !in_array(strtolower($user->role->role_name ?? ''), ['admin', 'kepala sekolah'])) {
            // Get today's day for showing today's schedule
            $hariIni = date('l');
            $hariIndonesia = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu'
            ];
            
            // Get today's schedule for display
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
            
            // Get ALL classes taught by this teacher (all days)
            $allJadwal = JadwalKbm::with(['kelas'])
                ->where('guru_id', $user->guru_id)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('semester_id', $semester->id)
                ->get();
            
            // Get unique classes from all schedules
            $kelasList = $allJadwal->pluck('kelas')->unique('id')->sortBy('nama_kelas')->values();
            
            $jamBelajarList = JamBelajar::orderBy('urutan')->get();
            $guruList = Guru::where('id', $user->guru_id)->get();
            $jadwalList = $jadwalHariIni;
        } else {
            // Admin or kepala sekolah can see all
            $kelasList = Kelas::orderBy('nama_kelas')->get();
            $guruList = Guru::orderBy('nama')->get();
            $jamBelajarList = JamBelajar::orderBy('urutan')->get();
            $jadwalList = collect();
        }

        return view('absensi.create', compact('kelasList', 'guruList', 'jamBelajarList', 'tahunAjaran', 'semester', 'jadwalList', 'selectedKelasId', 'selectedJamBelajarId', 'isQuickAccess'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:guru,id',
            'jam_belajar_id' => 'required|exists:jam_belajar,id',
            'tanggal' => 'required|date',
            'status_kelas' => 'nullable|string|max:100',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'semester_id' => 'required|exists:semester,id',
        ]);

        $absensi = AbsensiKelas::create($validated);

        return redirect()->route('absensi.show', $absensi->id)
            ->with('success', 'Absensi kelas berhasil dibuat.');
    }

    public function show($id)
    {
        $absensi = AbsensiKelas::with(['kelas', 'guru', 'jamBelajar', 'tahunAjaran', 'semester', 'absensiSiswa.siswa'])
            ->findOrFail($id);

        return view('absensi.show', compact('absensi'));
    }

    public function edit($id)
    {
        $absensi = AbsensiKelas::findOrFail($id);
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

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:guru,id',
            'jam_belajar_id' => 'required|exists:jam_belajar,id',
            'tanggal' => 'required|date',
            'status_kelas' => 'nullable|string|max:100',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'semester_id' => 'required|exists:semester,id',
        ]);

        $absensi->update($validated);

        return redirect()->route('absensi.show', $absensi->id)
            ->with('success', 'Absensi kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $absensi = AbsensiKelas::findOrFail($id);
        $absensi->delete();

        return redirect()->route('absensi.index')
            ->with('success', 'Absensi kelas berhasil dihapus.');
    }
}
