<?php

namespace App\Http\Controllers;

use App\Models\CapaianPembelajaran;
use Illuminate\Http\Request;

class CapaianPembelajaranController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_capaian_pembelajaran' => 'required|string|max:255|unique:capaian_pembelajarans,nama_capaian_pembelajaran',
            'deskripsi' => 'nullable|string',
            'fase' => 'nullable|string|max:1',
        ]);

        CapaianPembelajaran::create($validated);

        return back()->with('success', 'Capaian Pembelajaran berhasil ditambahkan');
    }

    public function update(Request $request, CapaianPembelajaran $capaianPembelajaran)
    {
        $validated = $request->validate([
            'nama_capaian_pembelajaran' => 'required|string|max:255|unique:capaian_pembelajarans,nama_capaian_pembelajaran,' . $capaianPembelajaran->id,
            'deskripsi' => 'nullable|string',
            'fase' => 'nullable|string|max:1',
        ]);

        $capaianPembelajaran->update($validated);

        return back()->with('success', 'Capaian Pembelajaran berhasil diperbarui');
    }

    public function destroy(CapaianPembelajaran $capaianPembelajaran)
    {
        $capaianPembelajaran->delete();

        return back()->with('success', 'Capaian Pembelajaran berhasil dihapus');
    }

    public function list()
    {
        $capaian = CapaianPembelajaran::orderBy('nama_capaian_pembelajaran')->get();
        return response()->json($capaian);
    }
}