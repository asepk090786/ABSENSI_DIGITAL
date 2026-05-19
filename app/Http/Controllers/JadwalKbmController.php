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
use App\Models\TugasGuru;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JadwalKbmController extends Controller
{
    protected function authorizeJadwalKbmManagement()
    {
        $user = auth()->user();
        if ($user && $user->hasAnyRole(['Siswa','Guru','Guru Mapel','Guru Kelas','Wali Kelas','Guru BK','Guru Piket'])) {
            return redirect()->route('home')->with('error', 'Akses ditolak. Hanya pengelola pusat yang dapat mengatur jadwal.');
        }

        return null;
    }

    /**
     * Display jadwal KBM index
     */
    public function index()
    {
        $kelasList = Kelas::with('waliKelas')->orderBy('tingkat_kelas')->orderBy('nama_kelas')->get();
        $guruList = Guru::orderBy('nama')->get();
        $sekolah = \App\Models\Sekolah::first();
        
        return view('jadwal_kbm.index', compact('kelasList', 'guruList', 'sekolah'));
    }

    /**
     * Show form untuk mengatur jadwal per kelas
     */
    public function createByKelas($kelasId)
    {
        if ($redirect = $this->authorizeJadwalKbmManagement()) {
            return $redirect;
        }

        $kelas = Kelas::with('waliKelas')->findOrFail($kelasId);
        $guruList = Guru::with('user')->orderBy('nama')->get();
        $mataPelajaranList = MataPelajaran::orderBy('nama_mapel')->get();
        
        // Get tugas guru untuk filtering
        $tugasGuruList = TugasGuru::with(['guru.user', 'mataPelajaran'])
            ->where('tingkat_kelas', $kelas->tingkat_kelas)
            ->where('is_active', true)
            ->where(function($query) use ($kelas) {
                $query->whereNull('kelas_id')
                      ->orWhere('kelas_id', $kelas->id);
            })
            ->get();
        
        // Group by mata pelajaran for easier lookup
        $guruByMapel = $tugasGuruList->groupBy('mata_pelajaran_id')->map(function($group) {
            return $group->pluck('guru_id')->toArray();
        });
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
            'semesterAktif',
            'guruByMapel',
            'tugasGuruList'
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
        $paperSize = request()->get('paper_size', 'a4'); // Default A4
        
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
        
        // Group jadwal by hari
        $jadwalByHari = $jadwalSorted->groupBy(function($item) {
            return $item->jamBelajar->hari ?? 'Tidak Diketahui';
        });
        
        // Get jam belajar grouped by hari
        $jamBelajarByHari = JamBelajar::orderByDay()->get()->groupBy('hari');
        
        // Get unique guru from jadwal
        $guruList = $jadwalSorted
            ->pluck('guru')
            ->unique('id')
            ->values();
        
        // Convert logo to base64 for embedded display in PDF
        $logoBase64 = null;
        $logoHeaderKiriBase64 = null;
        
        if ($sekolah && $sekolah->logo) {
            $logoPath = public_path('storage/' . $sekolah->logo);
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }
        
        if ($sekolah && $sekolah->logo_header_kiri) {
            $logoPath = public_path('storage/' . $sekolah->logo_header_kiri);
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoHeaderKiriBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }
        
        // Determine paper size dimensions
        $paperSizes = [
            'a4' => [210, 297], // mm
            'f4' => [210, 330], // mm (Folio)
            'folio' => [210, 330], // mm
            'legal' => [216, 356], // mm
        ];
        
        $dimensions = $paperSizes[$paperSize] ?? $paperSizes['a4'];
        $paperSizeArray = [0, 0, $dimensions[0] * 2.83465, $dimensions[1] * 2.83465]; // Convert mm to points
        
        // Generate PDF using dompdf
        $pdf = \PDF::loadView('jadwal_kbm.print-pdf', compact(
            'kelas',
            'sekolah',
            'tahunAjaranAktif',
            'semesterAktif',
            'jadwalSorted',
            'jadwalByHari',
            'jamBelajarByHari',
            'guruList',
            'logoBase64',
            'logoHeaderKiriBase64',
            'paperSize'
        ));
        
        $pdf->setPaper($paperSizeArray);
        
        $filename = 'Jadwal_' . str_replace(' ', '_', $kelas->nama_kelas) . '_' . strtoupper($paperSize) . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export jadwal per guru as PDF
     */
    public function exportPdfByGuru($guruId)
    {
        $paperSize = request()->get('paper_size', 'a4');
        
        $guru = Guru::findOrFail($guruId);
        $sekolah = \App\Models\Sekolah::first();
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
        
        // Convert logo to base64 for embedded display in PDF
        $logoBase64 = null;
        $logoHeaderKiriBase64 = null;
        
        if ($sekolah && $sekolah->logo) {
            $logoPath = public_path('storage/' . $sekolah->logo);
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }
        
        if ($sekolah && $sekolah->logo_header_kiri) {
            $logoPath = public_path('storage/' . $sekolah->logo_header_kiri);
            if (file_exists($logoPath)) {
                $logoData = file_get_contents($logoPath);
                $logoMime = mime_content_type($logoPath);
                $logoHeaderKiriBase64 = 'data:' . $logoMime . ';base64,' . base64_encode($logoData);
            }
        }
        
        // Determine paper size dimensions
        $paperSizes = [
            'a4' => [210, 297],
            'f4' => [210, 330],
            'folio' => [210, 330],
            'legal' => [216, 356],
        ];
        
        $dimensions = $paperSizes[$paperSize] ?? $paperSizes['a4'];
        $paperSizeArray = [0, 0, $dimensions[0] * 2.83465, $dimensions[1] * 2.83465];
        
        // Generate PDF using dompdf
        $pdf = \Pdf::loadView('jadwal_kbm.print-pdf-guru', compact(
            'guru',
            'sekolah',
            'tahunAjaranAktif',
            'semesterAktif',
            'jadwalGuru',
            'logoBase64',
            'logoHeaderKiriBase64',
            'paperSize'
        ));
        
        $pdf->setPaper($paperSizeArray);
        
        $filename = 'Jadwal_' . str_replace(' ', '_', $guru->nama) . '_' . strtoupper($paperSize) . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Show jadwal per guru
     */
    public function showByGuru($guruId)
    {
        $guru = Guru::findOrFail($guruId);
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        
        $query = JadwalKbm::where('guru_id', $guru->id);
        
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
        
        // Get tugas guru
        $tugasGuru = TugasGuru::with(['mataPelajaran', 'kelas'])
            ->where('guru_id', $guruId)
            ->where('is_active', true)
            ->orderBy('tingkat_kelas')
            ->orderBy('mata_pelajaran_id')
            ->get();
        
        return view('jadwal_kbm.show_by_guru', compact('guru', 'jadwalGuru', 'tahunAjaranAktif', 'semesterAktif', 'tugasGuru'));
    }

    /**
     * Show overall schedule (Jadwal Keseluruhan)
     */
    public function showKeseluruhan(Request $request)
    {
        $viewType = $request->get('view', 'full'); // 'full' atau 'compact'
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        
        $query = JadwalKbm::query();
        
        if ($tahunAjaranAktif) {
            $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $query->where('semester_id', $semesterAktif->id);
        }
        
        $jadwalKeseluruhan = $query
            ->with(['kelas', 'guru', 'mataPelajaran', 'jamBelajar'])
            ->orderBySchedule()
            ->get()
            ->groupBy('hari');
        
        // Get all jam belajar (termasuk UPACARA, ISTIRAHAT, PEMBIASAAN)
        $jamBelajarByHari = JamBelajar::orderByDay()->get()->groupBy('hari');
        
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $sekolah = \App\Models\Sekolah::first();

        // Ambil master kegiatan untuk kode kegiatan non-KBM
        $kegiatanList = \App\Models\Kegiatan::select('kode_kegiatan', 'nama_kegiatan')->get();
        
        // Get current user guru id for highlighting
        $currentUserGuruId = auth()->user()->guru_id ?? null;
        $currentUserGuruKode = null;
        if ($currentUserGuruId) {
            $guru = \App\Models\Guru::find($currentUserGuruId);
            $currentUserGuruKode = $guru->kode_guru ?? null;
        }
        
        return view('jadwal_kbm.keseluruhan', compact('jadwalKeseluruhan', 'jamBelajarByHari', 'hariList', 'tahunAjaranAktif', 'semesterAktif', 'sekolah', 'viewType', 'kegiatanList', 'currentUserGuruId', 'currentUserGuruKode'));
    }

    /**
     * Export jadwal keseluruhan as PDF
     */
    public function exportPdfKeseluruhan(Request $request)
    {
        $paperSize = $request->get('paper_size', 'a4');
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        
        $query = JadwalKbm::query();
        
        if ($tahunAjaranAktif) {
            $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $query->where('semester_id', $semesterAktif->id);
        }
        
        $jadwalKeseluruhan = $query
            ->with(['kelas', 'guru', 'mataPelajaran', 'jamBelajar'])
            ->orderBySchedule()
            ->get()
            ->groupBy('hari');
        
        // Get all jam belajar
        $jamBelajarByHari = JamBelajar::orderByDay()->get()->groupBy('hari');
        
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $sekolah = \App\Models\Sekolah::first();
        
        // Set paper size dimensions (in mm)
        $paperSizes = [
            'a4' => [210, 297],
            'f4' => [210, 330],
            'folio' => [210, 330],
        ];
        
        $dimensions = $paperSizes[$paperSize] ?? $paperSizes['a4'];
        
        // Ambil master kegiatan untuk kode kegiatan non-KBM
        $kegiatanList = \App\Models\Kegiatan::select('kode_kegiatan', 'nama_kegiatan')->get();

        $pdf = \Pdf::loadView('jadwal_kbm.pdf-keseluruhan', compact(
            'jadwalKeseluruhan',
            'jamBelajarByHari',
            'hariList',
            'tahunAjaranAktif',
            'semesterAktif',
            'sekolah',
            'paperSize',
            'kegiatanList'
        ));
        
        // Set paper size
        $pdf->setPaper($dimensions[0], $dimensions[1], 'mm');
        $pdf->setOption('margin-top', 5);
        $pdf->setOption('margin-right', 5);
        $pdf->setOption('margin-bottom', 5);
        $pdf->setOption('margin-left', 5);
        
        return $pdf->download('Jadwal_Keseluruhan_KBM_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export jadwal keseluruhan with kode mapel (compact) as PDF
     * Disiapkan landscape dengan font kecil agar muat 1 halaman seperti jadwal kode
     */
    public function exportPdfKeseluruhanMapel(Request $request)
    {
        $paperSize = $request->get('paper_size', 'a4');
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        
        $query = JadwalKbm::query();
        
        if ($tahunAjaranAktif) {
            $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $query->where('semester_id', $semesterAktif->id);
        }
        
        $jadwalKeseluruhan = $query
            ->with(['kelas', 'guru', 'mataPelajaran', 'jamBelajar'])
            ->orderBySchedule()
            ->get()
            ->groupBy('hari');
        
        // Get all jam belajar
        $jamBelajarByHari = JamBelajar::orderByDay()->get()->groupBy('hari');
        
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $sekolah = \App\Models\Sekolah::first();
        
        $paperSizes = [
            'a4' => 'a4',
            'f4' => [0, 0, 595.28, 935.43],
            'folio' => [0, 0, 595.28, 935.43],
        ];
        
        $paperSizeOption = $paperSizes[$paperSize] ?? 'a4';
        
        $pdf = \Pdf::loadView('jadwal_kbm.pdf-keseluruhan-mapel', compact(
            'jadwalKeseluruhan',
            'jamBelajarByHari',
            'hariList',
            'tahunAjaranAktif',
            'semesterAktif',
            'sekolah',
            'paperSize'
        ));
        
        $pdf->setPaper($paperSizeOption, 'landscape');
        $pdf->setOption('margin-top', 5);
        $pdf->setOption('margin-right', 5);
        $pdf->setOption('margin-bottom', 5);
        $pdf->setOption('margin-left', 5);
        
        return $pdf->download('Jadwal_KBM_Mapel_' . strtoupper($paperSize) . '_' . date('Y-m-d') . '.pdf');
    }
    public function store(Request $request)
    {
        if ($redirect = $this->authorizeJadwalKbmManagement()) {
            return $redirect;
        }

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'jadwal' => 'required|array',
        ]);

        $jadwalItems = collect($request->input('jadwal', []))->filter(function ($item) {
            return isset($item['hari'], $item['jam_ke'], $item['jam_belajar_id'])
                && (!empty($item['guru_id']) || !empty($item['mata_pelajaran_id']));
        })->map(function ($item) {
            return [
                'guru_id' => $item['guru_id'] ?? null,
                'mata_pelajaran_id' => $item['mata_pelajaran_id'] ?? null,
                'jam_belajar_id' => $item['jam_belajar_id'] ?? null,
                'hari' => $item['hari'] ?? null,
                'jam_ke' => $item['jam_ke'] ?? null,
            ];
        })->values()->all();

        if (empty($jadwalItems)) {
            return redirect()->back()->withInput()->with('error', 'Silakan isi minimal satu jadwal KBM sebelum menyimpan.');
        }

        $validator = Validator::make(['jadwal' => $jadwalItems], [
            'jadwal.*.guru_id' => 'required|exists:guru,id',
            'jadwal.*.mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'jadwal.*.jam_belajar_id' => 'required|exists:jam_belajar,id',
            'jadwal.*.hari' => 'required|string',
            'jadwal.*.jam_ke' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        
        // Get kelas info for validation
        $kelas = Kelas::findOrFail($validated['kelas_id']);

        DB::beginTransaction();
        try {
            // Validate tugas guru for each entry
            $warnings = [];
            foreach ($jadwalItems as $index => $item) {
                $hasTugas = TugasGuru::where('guru_id', $item['guru_id'])
                    ->where('mata_pelajaran_id', $item['mata_pelajaran_id'])
                    ->where('tingkat_kelas', $kelas->tingkat_kelas)
                    ->where('is_active', true)
                    ->where(function($query) use ($kelas) {
                        $query->whereNull('kelas_id')
                              ->orWhere('kelas_id', $kelas->id);
                    })
                    ->exists();
                
                if (!$hasTugas) {
                    $guru = Guru::find($item['guru_id']);
                    $mapel = MataPelajaran::find($item['mata_pelajaran_id']);
                    $warnings[] = "Guru {$guru->nama} tidak memiliki tugas mengajar {$mapel->nama_mapel} di tingkat {$kelas->tingkat_kelas}";
                }
            }
            
            // Delete existing jadwal for this kelas in this semester
            JadwalKbm::where('kelas_id', $validated['kelas_id'])
                ->where('tahun_ajaran_id', optional($tahunAjaranAktif)->id)
                ->where('semester_id', optional($semesterAktif)->id)
                ->delete();

            // Insert new jadwal
            foreach ($jadwalItems as $item) {
                JadwalKbm::create([
                    'kelas_id' => $validated['kelas_id'],
                    'guru_id' => $item['guru_id'],
                    'mata_pelajaran_id' => $item['mata_pelajaran_id'],
                    'jam_belajar_id' => $item['jam_belajar_id'],
                    'hari' => $item['hari'],
                    'jam_ke' => $item['jam_ke'],
                    'tahun_ajaran_id' => optional($tahunAjaranAktif)->id,
                    'semester_id' => optional($semesterAktif)->id,
                ]);
            }
            
            // Auto-generate/update Tugas Guru from jadwal
            $uniqueAssignments = collect($jadwalItems)
                ->unique(function ($item) {
                    return $item['guru_id'] . '-' . $item['mata_pelajaran_id'];
                });
            
            foreach ($uniqueAssignments as $item) {
                TugasGuru::updateOrCreate(
                    [
                        'guru_id' => $item['guru_id'],
                        'mata_pelajaran_id' => $item['mata_pelajaran_id'],
                        'tingkat_kelas' => $kelas->tingkat_kelas,
                        'kelas_id' => $kelas->id
                    ],
                    [
                        'is_active' => 1,
                        'keterangan' => 'Auto-generated from jadwal KBM',
                        'updated_at' => now()
                    ]
                );
            }

            DB::commit();
            
            if (count($warnings) > 0) {
                return redirect()->route('jadwal-kbm.index')
                    ->with('warning', 'Jadwal KBM berhasil disimpan dengan peringatan:')
                    ->with('warnings', $warnings);
            }
            
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
        if ($redirect = $this->authorizeJadwalKbmManagement()) {
            return $redirect;
        }

        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'jam_belajar_id' => 'required|exists:jam_belajar,id',
        ]);

        $jadwal = JadwalKbm::findOrFail($id);
        
        // Validate tugas guru
        $kelas = $jadwal->kelas;
        $hasTugas = TugasGuru::where('guru_id', $validated['guru_id'])
            ->where('mata_pelajaran_id', $validated['mata_pelajaran_id'])
            ->where('tingkat_kelas', $kelas->tingkat_kelas)
            ->where('is_active', true)
            ->where(function($query) use ($kelas) {
                $query->whereNull('kelas_id')
                      ->orWhere('kelas_id', $kelas->id);
            })
            ->exists();
        
        if (!$hasTugas) {
            $guru = Guru::find($validated['guru_id']);
            $mapel = MataPelajaran::find($validated['mata_pelajaran_id']);
            return response()->json([
                'success' => false, 
                'message' => "Peringatan: Guru {$guru->nama} tidak memiliki tugas mengajar {$mapel->nama_mapel} di tingkat {$kelas->tingkat_kelas}",
                'warning' => true
            ]);
        }
        
        $jadwal->update($validated);

        return response()->json(['success' => true, 'message' => 'Jadwal berhasil diupdate']);
    }

    /**
     * Delete single jadwal entry
     */
    public function destroy($id)
    {
        if ($redirect = $this->authorizeJadwalKbmManagement()) {
            return $redirect;
        }

        $jadwal = JadwalKbm::findOrFail($id);
        $jadwal->delete();

        return response()->json(['success' => true, 'message' => 'Jadwal berhasil dihapus']);
    }

    /**
     * Get jadwal by kelas (AJAX)
     */
    public function getJadwalByKelas($kelasId)
    {
        try {
            $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
            $semesterAktif = Semester::where('is_active', true)->first();
            
            $jadwal = JadwalKbm::where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', optional($tahunAjaranAktif)->id)
                ->where('semester_id', optional($semesterAktif)->id)
                ->with(['guru', 'mataPelajaran', 'jamBelajar'])
                ->orderBySchedule()
                ->get();

            // Format response untuk JavaScript
            $data = $jadwal->map(function($j) {
                return [
                    'id' => $j->id,
                    'hari' => $j->hari,
                    'jam_ke' => $j->jam_ke,
                    'jam_belajar' => [
                        'jam_mulai' => $j->jamBelajar->jam_mulai ?? '-',
                        'jam_selesai' => $j->jamBelajar->jam_selesai ?? '-'
                    ],
                    'mata_pelajaran' => [
                        'nama_mapel' => $j->mataPelajaran->nama_mapel ?? '-'
                    ],
                    'guru' => [
                        'nama' => $j->guru->nama ?? '-'
                    ]
                ];
            });

            return response()->json($data, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
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
            ->where('tahun_ajaran_id', optional($tahunAjaranAktif)->id)
            ->where('semester_id', optional($semesterAktif)->id)
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
        if ($redirect = $this->authorizeJadwalKbmManagement()) {
            return $redirect;
        }

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

    /**
     * Update header settings (sekolah info dan logo)
     */
    public function updateHeader(Request $request)
    {
        if ($redirect = $this->authorizeJadwalKbmManagement()) {
            return $redirect;
        }

        $validated = $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'alamat_jalan' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'website' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'logo_header_kiri' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $sekolah = \App\Models\Sekolah::first();
            
            if (!$sekolah) {
                $sekolah = new \App\Models\Sekolah();
            }

            $sekolah->nama_sekolah = $validated['nama_sekolah'];
            $sekolah->alamat_jalan = $validated['alamat_jalan'] ?? '';
            $sekolah->telepon = $validated['telepon'] ?? '';
            $sekolah->website = $validated['website'] ?? '';
            $sekolah->email = $validated['email'] ?? '';

            // Handle logo_header_kiri (Logo Kiri - Tidak dari sekolah)
            if ($request->hasFile('logo_header_kiri')) {
                try {
                    // Delete old logo_header_kiri if exists
                    if ($sekolah->logo_header_kiri && \Storage::exists('public/' . $sekolah->logo_header_kiri)) {
                        \Storage::delete('public/' . $sekolah->logo_header_kiri);
                    }
                    $logoPath = $request->file('logo_header_kiri')->store('logos', 'public');
                    $sekolah->logo_header_kiri = $logoPath;
                } catch (\Exception $e) {
                    \Log::error('Error uploading logo_header_kiri: ' . $e->getMessage());
                    throw new \Exception('Gagal upload logo kiri: ' . $e->getMessage());
                }
            }

            // Handle logo (Logo Kanan - Logo Sekolah)
            if ($request->hasFile('logo')) {
                try {
                    // Delete old logo if exists
                    if ($sekolah->logo && \Storage::exists('public/' . $sekolah->logo)) {
                        \Storage::delete('public/' . $sekolah->logo);
                    }
                    $logoPath = $request->file('logo')->store('logos', 'public');
                    $sekolah->logo = $logoPath;
                } catch (\Exception $e) {
                    \Log::error('Error uploading logo: ' . $e->getMessage());
                    throw new \Exception('Gagal upload logo sekolah: ' . $e->getMessage());
                }
            }

            $sekolah->save();

            return redirect()->route('jadwal-kbm.index', ['tab' => 'setting'])
                ->with('success', 'Pengaturan header berhasil disimpan');
        } catch (\Exception $e) {
            \Log::error('Error updating header: ' . $e->getMessage());
            return redirect()->route('jadwal-kbm.index', ['tab' => 'setting'])
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }

    /**
     * Get eligible guru for specific mata pelajaran and kelas (AJAX)
     */
    public function getGuruByMapel(Request $request)
    {
        $kelasId = $request->input('kelas_id');
        $mataPelajaranId = $request->input('mata_pelajaran_id');
        
        if (!$kelasId || !$mataPelajaranId) {
            return response()->json(['error' => 'Missing parameters'], 400);
        }
        
        $kelas = Kelas::findOrFail($kelasId);
        
        // Get guru yang memiliki tugas mengajar mata pelajaran ini di tingkat kelas ini
        $guruList = TugasGuru::with('guru.user')
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->where('tingkat_kelas', $kelas->tingkat_kelas)
            ->where('is_active', true)
            ->where(function($query) use ($kelas) {
                $query->whereNull('kelas_id')
                      ->orWhere('kelas_id', $kelas->id);
            })
            ->get()
            ->pluck('guru')
            ->unique('id')
            ->values()
            ->map(function($guru) {
                return [
                    'id' => $guru->id,
                    'nama' => $guru->user->name ?? $guru->nama,
                    'nip' => $guru->nip
                ];
            });
        
        return response()->json($guruList);
    }
}
