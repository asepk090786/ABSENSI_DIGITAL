<?php

namespace App\Http\Controllers;

use App\Models\TugasTambahan;
use App\Models\TenagaPendidikan;
use Illuminate\Http\Request;

/**
 * Tugas Tambahan Controller
 * 
 * Mengelola tugas-tugas tambahan/ekstrakurikuler yang diberikan kepada tenaga pendidikan.
 */
class TugasTambahanController extends Controller
{
    public function index()
    {
        $items = TugasTambahan::with('tenagaPendidikan')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        return view('tugas_tambahan.index', compact('items'));
    }

    public function create()
    {
        $tenagaPendidikan = TenagaPendidikan::where('is_active', true)
            ->orderBy('nama')
            ->get();
        return view('tugas_tambahan.create', compact('tenagaPendidikan'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenaga_pendidikan_id' => 'required|exists:tenaga_pendidikan,id',
            'tugas' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        TugasTambahan::create($validated);

        return redirect()->route('tugas_tambahan.index')
            ->with('success', 'Tugas tambahan berhasil ditambahkan');
    }

    public function show(TugasTambahan $tugaTambahan)
    {
        return view('tugas_tambahan.show', compact('tugaTambahan'));
    }

    public function edit(TugasTambahan $tugaTambahan)
    {
        $tenagaPendidikan = TenagaPendidikan::where('is_active', true)
            ->orderBy('nama')
            ->get();
        return view('tugas_tambahan.edit', compact('tugaTambahan', 'tenagaPendidikan'));
    }

    public function update(Request $request, TugasTambahan $tugaTambahan)
    {
        $validated = $request->validate([
            'tenaga_pendidikan_id' => 'required|exists:tenaga_pendidikan,id',
            'tugas' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $validated['is_active'] ?? true;

        $tugaTambahan->update($validated);

        return redirect()->route('tugas_tambahan.index')
            ->with('success', 'Tugas tambahan berhasil diperbarui');
    }

    public function destroy(TugasTambahan $tugaTambahan)
    {
        $tugaTambahan->delete();

        return redirect()->route('tugas_tambahan.index')
            ->with('success', 'Tugas tambahan berhasil dihapus');
    }
}
