<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JadwalKbm;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\JamBelajar;
use App\Models\TahunAjaran;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;

class JadwalKbmController extends Controller
{
    /**
     * Display jadwal KBM index
     */
    public function index()
    {
        $kelasList = Kelas::with('waliKelas')->orderBy('tingkat_kelas')->orderBy('nama_kelas')->get();
        $guruList = Guru::orderBy('nama')->get();
        
        return view('jadwal_kbm.index', compact('kelasList', 'guruList'));
    }

    /**
     * Show form untuk mengatur jadwal per kelas
     */
    public function createByKelas($kelasId)
    {
        $kelas = Kelas::with('waliKelas')->findOrFail($kelasId);
        $guruList = Guru::orderBy('nama')->get();
        $mataPelajaranList = MataPelajaran::orderBy('nama_mapel')->get();
        $jamBelajarList = JamBelajar::orderByDay()->get();
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        
        // Get existing jadwal for this kelas
        // If no active tahun ajaran/semester, get the latest ones instead
        $query = JadwalKbm::where('kelas_id', $kelasId);
        
        if ($tahunAjaranAktif) {
            $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $query->where('semester_id', $semesterAktif->id);
        }
        
        $existingJadwal = $query
            ->with(['guru', 'mataPelajaran', 'jamBelajar'])
            ->orderBySchedule()
            ->get()
            ->groupBy('hari');
        
        // Get jam belajar grouped by hari
        $jamBelajarByHari = $jamBelajarList->groupBy('hari');
        
        return view('jadwal_kbm.create_by_kelas', compact(
            'kelas', 
            'guruList', 
            'mataPelajaranList', 
            'jamBelajarList',
            'jamBelajarByHari',
            'existingJadwal',
            'tahunAjaranAktif',
            'semesterAktif'
        ));
    }

    /**
     * Print jadwal per kelas
     */
    public function printByKelas($kelasId)
    {
        $kelas = Kelas::findOrFail($kelasId);
        $sekolah = \App\Models\Sekolah::first();
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        
        // Get existing jadwal for this kelas
        $query = JadwalKbm::where('kelas_id', $kelasId);
        
        if ($tahunAjaranAktif) {
            $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $query->where('semester_id', $semesterAktif->id);
        }
        
        $jadwalSorted = $query
            ->with(['guru', 'mataPelajaran', 'jamBelajar'])
            ->orderBySchedule()
            ->get();
        
        // Get jam belajar grouped by hari (untuk tampilkan UPACARA, ISTIRAHAT, PEMBIASAAN)
        $jamBelajarByHari = JamBelajar::orderByDay()->get()->groupBy('hari');
        
        // Get unique guru from jadwal
        $guruList = $jadwalSorted
            ->pluck('guru')
            ->unique('id')
            ->values();
        
        return view('jadwal_kbm.print', compact(
            'kelas',
            'sekolah',
            'tahunAjaranAktif',
            'semesterAktif',
            'jadwalSorted',
            'jamBelajarByHari',
            'guruList'
        ));
    }

    /**
     * Export jadwal per kelas as PDF
     */
    public function exportPdfByKelas($kelasId)
    {
        $kelas = Kelas::findOrFail($kelasId);
        $sekolah = \App\Models\Sekolah::first();
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        
        // Get existing jadwal for this kelas
        $query = JadwalKbm::where('kelas_id', $kelasId);
        
        if ($tahunAjaranAktif) {
            $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $query->where('semester_id', $semesterAktif->id);
        }
        
        $jadwalSorted = $query
            ->with(['guru', 'mataPelajaran', 'jamBelajar'])
            ->orderBySchedule()
            ->get();
        
        // Get jam belajar grouped by hari
        $jamBelajarByHari = JamBelajar::orderByDay()->get()->groupBy('hari');
        
        // Get unique guru from jadwal
        $guruList = $jadwalSorted
            ->pluck('guru')
            ->unique('id')
            ->values();
        
        // Convert logo to base64 for embedded display in PDF
        $logoBase64 = null;
        if ($sekolah && $sekolah->logo) {
            $logoPath = public_path('storage/' . $sekolah->logo);
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }
        
        $html = view('jadwal_kbm.print-pdf-modern', compact(
            'kelas',
            'sekolah',
            'tahunAjaranAktif',
            'semesterAktif',
            'jadwalSorted',
            'jamBelajarByHari',
            'guruList',
            'logoBase64'
        ))->render();
        
        // Simple PDF generation using inline HTML to PDF conversion
        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8')
            ->header('Content-Disposition', 'inline; filename="Jadwal_' . str_replace(' ', '_', $kelas->nama_kelas) . '.pdf"');
    }

    /**
     * Show jadwal per guru
     */
    public function showByGuru($guruId)
    {
        $guru = Guru::findOrFail($guruId);
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        
        $query = JadwalKbm::where('guru_id', $guruId);
        
        if ($tahunAjaranAktif) {
            $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $query->where('semester_id', $semesterAktif->id);
        }
        
        $jadwalGuru = $query
            ->with(['kelas', 'mataPelajaran', 'jamBelajar'])
            ->orderBySchedule()
            ->get()
            ->groupBy('hari');
        
        return view('jadwal_kbm.show_by_guru', compact('guru', 'jadwalGuru', 'tahunAjaranAktif', 'semesterAktif'));
    }

    /**
     * Store jadwal KBM
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'jadwal' => 'required|array',
            'jadwal.*.guru_id' => 'required|exists:guru,id',
            'jadwal.*.mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'jadwal.*.jam_belajar_id' => 'required|exists:jam_belajar,id',
            'jadwal.*.hari' => 'required|string',
            'jadwal.*.jam_ke' => 'required|integer',
        ]);

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();

        DB::beginTransaction();
        try {
            // Delete existing jadwal for this kelas in this semester
            JadwalKbm::where('kelas_id', $validated['kelas_id'])
                ->where('tahun_ajaran_id', $tahunAjaranAktif?->id)
                ->where('semester_id', $semesterAktif?->id)
                ->delete();

            // Insert new jadwal
            foreach ($validated['jadwal'] as $item) {
                JadwalKbm::create([
                    'kelas_id' => $validated['kelas_id'],
                    'guru_id' => $item['guru_id'],
                    'mata_pelajaran_id' => $item['mata_pelajaran_id'],
                    'jam_belajar_id' => $item['jam_belajar_id'],
                    'hari' => $item['hari'],
                    'jam_ke' => $item['jam_ke'],
                    'tahun_ajaran_id' => $tahunAjaranAktif?->id,
                    'semester_id' => $semesterAktif?->id,
                ]);
            }

            DB::commit();
            return redirect()->route('jadwal-kbm.index')->with('success', 'Jadwal KBM berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menyimpan jadwal: ' . $e->getMessage());
        }
    }

    /**
     * Update single jadwal entry
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'jam_belajar_id' => 'required|exists:jam_belajar,id',
        ]);

        $jadwal = JadwalKbm::findOrFail($id);
        $jadwal->update($validated);

        return response()->json(['success' => true, 'message' => 'Jadwal berhasil diupdate']);
    }

    /**
     * Delete single jadwal entry
     */
    public function destroy($id)
    {
        $jadwal = JadwalKbm::findOrFail($id);
        $jadwal->delete();

        return response()->json(['success' => true, 'message' => 'Jadwal berhasil dihapus']);
    }

    /**
     * Get jadwal by kelas (AJAX)
     */
    public function getJadwalByKelas($kelasId)
    {
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        
        $jadwal = JadwalKbm::where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $tahunAjaranAktif?->id)
            ->where('semester_id', $semesterAktif?->id)
            ->with(['guru', 'mataPelajaran', 'jamBelajar'])
            ->orderBySchedule()
            ->get();

        return response()->json($jadwal);
    }

    /**
     * Check konflik jadwal guru (AJAX)
     */
    public function checkKonflikGuru(Request $request)
    {
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();

        $konflik = JadwalKbm::where('guru_id', $request->guru_id)
            ->where('hari', $request->hari)
            ->where('jam_ke', $request->jam_ke)
            ->where('tahun_ajaran_id', $tahunAjaranAktif?->id)
            ->where('semester_id', $semesterAktif?->id)
            ->when($request->kelas_id, function($query, $kelasId) {
                return $query->where('kelas_id', '!=', $kelasId);
            })
            ->with(['kelas'])
            ->first();

        return response()->json([
            'konflik' => $konflik ? true : false,
            'data' => $konflik
        ]);
    }

    /**
     * Delete all jadwal KBM
     */
    public function destroyAll()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::table('jadwal_kbm')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            return redirect()->route('jadwal_kbm.index')
                ->with('success', 'Semua jadwal KBM berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('jadwal_kbm.index')
                ->with('error', 'Gagal menghapus jadwal: ' . $e->getMessage());
        }
    }
}
