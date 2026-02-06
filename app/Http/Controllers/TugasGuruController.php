<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TugasGuru;
use App\Models\Guru;
use App\Models\MataPelajaran;
use App\Models\Kelas;

class TugasGuruController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = TugasGuru::with(['guru.user', 'mataPelajaran', 'kelas'])
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
        
        return view('tugas_guru.index', compact('items', 'itemsByTingkat', 'guruList'));
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
