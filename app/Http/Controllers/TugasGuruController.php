<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TugasGuru;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\JadwalKbm;
use App\Models\TahunAjaran;
use App\Models\Semester;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;
use PDF;

class TugasGuruController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = TugasGuru::with(['guru.user', 'mataPelajaran', 'kelas'])
            ->whereHas('guru')
            ->orderBy('tingkat_kelas')
            ->orderBy('guru_id')
            ->orderBy('mata_pelajaran_id')
            ->get();
        
        // Group by tingkat_kelas
        $itemsByTingkat = $items->groupBy('tingkat_kelas');
        
        // Get list of all guru with active tugas count
        $guruList = Guru::with('user')
            ->whereHas('tugasGuru', function($query) {
                $query->where('is_active', true);
            })
            ->withCount(['tugasGuru' => function($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('nama')
            ->get();
        
        // Get active tahun ajaran & semester
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        
        // Beban Kerja: Data untuk tabel beban kerja guru
        $guruBebanKerja = Guru::with(['user', 'tugasGuru.kelas', 'tugasGuru.mataPelajaran'])
            ->whereHas('tugasGuru', function($query) {
                $query->where('is_active', true);
            })
            ->orderBy('nama')
            ->get();
        
        // Get kelas yang sebenarnya ada di jadwal KBM
        $jadwalKelasList = JadwalKbm::select('kelas_id');
        if ($tahunAjaranAktif) {
            $jadwalKelasList->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $jadwalKelasList->where('semester_id', $semesterAktif->id);
        }
        $jadwalKelasIds = $jadwalKelasList->distinct()->pluck('kelas_id')->filter()->toArray();
        
        if (!empty($jadwalKelasIds)) {
            $kelasList = Kelas::whereIn('id', $jadwalKelasIds)->orderBy('tingkat_kelas')->orderBy('nama_kelas')->get();
        } else {
            $kelasList = Kelas::orderBy('tingkat_kelas')->orderBy('nama_kelas')->get();
        }
        
        // Hitung jumlah jam KBM per guru per kelas per mata pelajaran
        $jadwalKbmJumlah = $this->buildJadwalKbmJumlah($guruBebanKerja, $tahunAjaranAktif, $semesterAktif);
        $totalJamPerGuru = $this->buildTotalJamPerGuru($guruBebanKerja, $tahunAjaranAktif, $semesterAktif);
        $totalJamPerKelas = [];
        
        // Initialize total jam per kelas
        foreach ($kelasList as $kelas) {
            $totalJamPerKelas[$kelas->id] = 0;
        }
        
        // Hitung total jam per kelas
        foreach ($kelasList as $kelas) {
            $query = JadwalKbm::where('kelas_id', $kelas->id);
            
            if ($tahunAjaranAktif) {
                $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
            }
            if ($semesterAktif) {
                $query->where('semester_id', $semesterAktif->id);
            }
            
            $totalJamPerKelas[$kelas->id] = $query->count();
        }
        
        return view('tugas_guru.index', compact('items', 'itemsByTingkat', 'guruList', 'guruBebanKerja', 'kelasList', 'jadwalKbmJumlah', 'totalJamPerGuru', 'totalJamPerKelas'));
    }

    /**
     * Build jadwal KBM counts by guru, mapel, and kelas for active term
     */
    private function buildJadwalKbmJumlah($guruBebanKerja, $tahunAjaranAktif, $semesterAktif)
    {
        $jadwalKbmJumlah = [];

        foreach ($guruBebanKerja as $guru) {
            $tasksByMapel = $guru->tugasGuru->groupBy('mata_pelajaran_id');

            foreach ($tasksByMapel as $mapelId => $tasks) {
                $hasGeneralTask = $tasks->contains(function ($task) {
                    return $task->kelas_id === null;
                });

                if ($hasGeneralTask) {
                    $query = JadwalKbm::where('guru_id', $guru->id)
                        ->where('mata_pelajaran_id', $mapelId);

                    if ($tahunAjaranAktif) {
                        $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
                    }
                    if ($semesterAktif) {
                        $query->where('semester_id', $semesterAktif->id);
                    }

                    $counts = $query->select('kelas_id')
                        ->selectRaw('count(*) as jumlah')
                        ->groupBy('kelas_id')
                        ->pluck('jumlah', 'kelas_id')
                        ->toArray();

                    foreach ($counts as $kelasId => $jumlahJam) {
                        $key = $guru->id . '_' . $mapelId . '_' . ($kelasId ?? 'all');
                        $jadwalKbmJumlah[$key] = $jumlahJam;
                    }

                    continue;
                }

                foreach ($tasks as $tugas) {
                    $query = JadwalKbm::where('guru_id', $guru->id)
                        ->where('mata_pelajaran_id', $tugas->mata_pelajaran_id);

                    if ($tugas->kelas_id) {
                        $query->where('kelas_id', $tugas->kelas_id);
                    }
                    if ($tahunAjaranAktif) {
                        $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
                    }
                    if ($semesterAktif) {
                        $query->where('semester_id', $semesterAktif->id);
                    }

                    $jumlahJam = $query->count();
                    $key = $guru->id . '_' . $tugas->mata_pelajaran_id . '_' . ($tugas->kelas_id ?? 'all');
                    $jadwalKbmJumlah[$key] = $jumlahJam;
                }
            }
        }

        return $jadwalKbmJumlah;
    }

    /**
     * Build total jam KBM per guru for active term
     */
    private function buildTotalJamPerGuru($guruBebanKerja, $tahunAjaranAktif, $semesterAktif)
    {
        $totalJamPerGuru = [];

        foreach ($guruBebanKerja as $guru) {
            $query = JadwalKbm::where('guru_id', $guru->id);

            if ($tahunAjaranAktif) {
                $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
            }
            if ($semesterAktif) {
                $query->where('semester_id', $semesterAktif->id);
            }

            $totalJamPerGuru[$guru->id] = $query->count();
        }

        return $totalJamPerGuru;
    }

    /**
     * Export beban kerja to Excel
     */
    public function exportBebanKerjaExcel()
    {
        // Reuse logic from index to compute dataset
        $guruBebanKerja = Guru::with(['user', 'tugasGuru.kelas', 'tugasGuru.mataPelajaran'])
            ->whereHas('tugasGuru', function($query) {
                $query->where('is_active', true);
            })
            ->orderBy('nama')
            ->get();

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();

        $jadwalKelasList = JadwalKbm::select('kelas_id');
        if ($tahunAjaranAktif) $jadwalKelasList->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        if ($semesterAktif) $jadwalKelasList->where('semester_id', $semesterAktif->id);
        $jadwalKelasIds = $jadwalKelasList->distinct()->pluck('kelas_id')->filter()->toArray();

        if (!empty($jadwalKelasIds)) {
            $kelasList = Kelas::whereIn('id', $jadwalKelasIds)->orderBy('tingkat_kelas')->orderBy('nama_kelas')->get();
        } else {
            $kelasList = Kelas::orderBy('tingkat_kelas')->orderBy('nama_kelas')->get();
        }

        $jadwalKbmJumlah = $this->buildJadwalKbmJumlah($guruBebanKerja, $tahunAjaranAktif, $semesterAktif);

        // Prepare header
        $header = ['NO','NAMA GURU','GOL/RUANG','MATA PELAJARAN'];
        foreach ($kelasList as $kelas) {
            $header[] = $kelas->nama_kelas;
        }
        $header[] = 'JUMLAH JAM KBM';

        $rows = [];
        $no = 0;
        foreach ($guruBebanKerja as $guru) {
            $guruMapels = $guru->tugasGuru->groupBy('mata_pelajaran_id');
            foreach ($guruMapels as $mapelId => $tugasPerMapel) {
                $no++;
                $mapel = $tugasPerMapel->first()->mataPelajaran;
                $row = [];
                $row[] = $no;
                $row[] = $guru->user->name ?? $guru->nama;
                $row[] = ($guru->golongan ?? '-') . ' / ' . ($guru->ruang ?? '-');
                $row[] = $mapel->nama_mapel ?? '-';
                $sumJam = 0;
                foreach ($kelasList as $kelas) {
                    $jumlahJam = 0;
                    $hasSpesificTask = $tugasPerMapel->contains(function($task) use ($kelas) {
                        return $task->kelas_id === $kelas->id;
                    });
                    if ($hasSpesificTask) {
                        $key = $guru->id . '_' . $mapel->id . '_' . $kelas->id;
                        $jumlahJam = $jadwalKbmJumlah[$key] ?? 0;
                    } else {
                        $hasGeneralTask = $tugasPerMapel->contains(function($task) { return $task->kelas_id === null; });
                        if ($hasGeneralTask) {
                            $key = $guru->id . '_' . $mapel->id . '_' . $kelas->id;
                            $jumlahJam = $jadwalKbmJumlah[$key] ?? 0;
                        }
                    }
                    $row[] = $jumlahJam;
                    $sumJam += $jumlahJam;
                }
                $row[] = $sumJam;
                $rows[] = $row;
            }
        }

        $export = new ArrayExport($rows, $header);
        return Excel::download($export, 'beban_kerja_guru.xlsx');
    }

    /**
     * Export beban kerja to PDF
     */
    public function exportBebanKerjaPdf()
    {
        // reuse same data building as Excel
        $guruBebanKerja = Guru::with(['user', 'tugasGuru.kelas', 'tugasGuru.mataPelajaran'])
            ->whereHas('tugasGuru', function($query) {
                $query->where('is_active', true);
            })
            ->orderBy('nama')
            ->get();

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();

        $jadwalKelasList = JadwalKbm::select('kelas_id');
        if ($tahunAjaranAktif) $jadwalKelasList->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        if ($semesterAktif) $jadwalKelasList->where('semester_id', $semesterAktif->id);
        $jadwalKelasIds = $jadwalKelasList->distinct()->pluck('kelas_id')->filter()->toArray();

        if (!empty($jadwalKelasIds)) {
            $kelasList = Kelas::whereIn('id', $jadwalKelasIds)->orderBy('tingkat_kelas')->orderBy('nama_kelas')->get();
        } else {
            $kelasList = Kelas::orderBy('tingkat_kelas')->orderBy('nama_kelas')->get();
        }

        $jadwalKbmJumlah = $this->buildJadwalKbmJumlah($guruBebanKerja, $tahunAjaranAktif, $semesterAktif);

        $pdf = PDF::loadView('tugas_guru.pdf_beban_kerja', compact('guruBebanKerja','kelasList','jadwalKbmJumlah'));
        return $pdf->download('beban_kerja_guru.pdf');
    }

    /**
     * Print view for beban kerja
     */
    public function printBebanKerja()
    {
        $guruBebanKerja = Guru::with(['user', 'tugasGuru.kelas', 'tugasGuru.mataPelajaran'])
            ->whereHas('tugasGuru', function($query) {
                $query->where('is_active', true);
            })
            ->orderBy('nama')
            ->get();

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();

        $jadwalKelasList = JadwalKbm::select('kelas_id');
        if ($tahunAjaranAktif) $jadwalKelasList->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        if ($semesterAktif) $jadwalKelasList->where('semester_id', $semesterAktif->id);
        $jadwalKelasIds = $jadwalKelasList->distinct()->pluck('kelas_id')->filter()->toArray();

        if (!empty($jadwalKelasIds)) {
            $kelasList = Kelas::whereIn('id', $jadwalKelasIds)->orderBy('tingkat_kelas')->orderBy('nama_kelas')->get();
        } else {
            $kelasList = Kelas::orderBy('tingkat_kelas')->orderBy('nama_kelas')->get();
        }

        $jadwalKbmJumlah = $this->buildJadwalKbmJumlah($guruBebanKerja, $tahunAjaranAktif, $semesterAktif);

        return view('tugas_guru.print_beban_kerja', compact('guruBebanKerja','kelasList','jadwalKbmJumlah'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $guruList = Guru::with('user')->orderBy('nama')->get();
        $mataPelajaranList = MataPelajaran::orderBy('nama_mapel')->get();
        
        // Daftar tingkat kelas yang umum di SMA
        $tingkatList = ['X', 'XI', 'XII'];
        
        // Kelas diambil berdasarkan tingkat yang dipilih (via AJAX atau select)
        $kelasList = Kelas::orderBy('tingkat_kelas')->orderBy('nama_kelas')->get();
        
        return view('tugas_guru.create', compact('guruList', 'mataPelajaranList', 'tingkatList', 'kelasList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'tingkat_kelas' => 'required|string|max:10',
            'kelas_id' => 'nullable|exists:kelas,id',
            'is_active' => 'boolean',
            'keterangan' => 'nullable|string',
        ]);

        // Set default value for is_active if not provided
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Check for duplicate assignment
        $exists = TugasGuru::where('guru_id', $validated['guru_id'])
            ->where('mata_pelajaran_id', $validated['mata_pelajaran_id'])
            ->where('tingkat_kelas', $validated['tingkat_kelas'])
            ->where('kelas_id', $validated['kelas_id'])
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tugas guru ini sudah ada dalam sistem.');
        }

        TugasGuru::create($validated);

        return redirect()->route('tugas_guru.index')->with('success', 'Tugas guru berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TugasGuru $tugas_guru)
    {
        $tugas_guru->load(['guru.user', 'mataPelajaran', 'kelas']);
        return view('tugas_guru.show', compact('tugas_guru'));
    }

    /**
     * Show tugas for specific guru
     */
    public function showByGuru($guruId)
    {
        $guru = Guru::with('user')->findOrFail($guruId);
        
        $tugasGuru = TugasGuru::with(['mataPelajaran', 'kelas'])
            ->where('guru_id', $guruId)
            ->orderBy('tingkat_kelas')
            ->orderBy('mata_pelajaran_id')
            ->get()
            ->groupBy('tingkat_kelas');
        
        return view('tugas_guru.show_by_guru', compact('guru', 'tugasGuru'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TugasGuru $tugas_guru)
    {
        $tugas_guru->load(['guru', 'mataPelajaran', 'kelas']);
        
        $guruList = Guru::with('user')->orderBy('nama')->get();
        $mataPelajaranList = MataPelajaran::orderBy('nama_mapel')->get();
        $tingkatList = ['X', 'XI', 'XII'];
        $kelasList = Kelas::orderBy('tingkat_kelas')->orderBy('nama_kelas')->get();
        
        return view('tugas_guru.edit', compact('tugas_guru', 'guruList', 'mataPelajaranList', 'tingkatList', 'kelasList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TugasGuru $tugas_guru)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'tingkat_kelas' => 'required|string|max:10',
            'kelas_id' => 'nullable|exists:kelas,id',
            'is_active' => 'boolean',
            'keterangan' => 'nullable|string',
        ]);

        // Set default value for is_active if not provided
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Check for duplicate assignment (except current record)
        $exists = TugasGuru::where('guru_id', $validated['guru_id'])
            ->where('mata_pelajaran_id', $validated['mata_pelajaran_id'])
            ->where('tingkat_kelas', $validated['tingkat_kelas'])
            ->where('kelas_id', $validated['kelas_id'])
            ->where('id', '!=', $tugas_guru->id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Tugas guru ini sudah ada dalam sistem.');
        }

        $tugas_guru->update($validated);

        return redirect()->route('tugas_guru.index')->with('success', 'Tugas guru berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TugasGuru $tugas_guru)
    {
        $tugas_guru->delete();

        return redirect()->route('tugas_guru.index')->with('success', 'Tugas guru berhasil dihapus.');
    }

    /**
     * Get kelas by tingkat (for AJAX request)
     */
    public function getKelasByTingkat(Request $request)
    {
        $tingkat = $request->input('tingkat');
        
        $kelasList = Kelas::where('tingkat_kelas', $tingkat)
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas']);
        
        return response()->json($kelasList);
    }
}
