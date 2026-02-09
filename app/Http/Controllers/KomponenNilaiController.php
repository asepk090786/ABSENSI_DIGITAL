<?php

namespace App\Http\Controllers;

use App\Models\KomponenNilai;
use Illuminate\Http\Request;

class KomponenNilaiController extends Controller
{
    public function index()
    {
        $items = KomponenNilai::orderBy('nama_komponen')->get();
        return view('komponen_nilai.index', compact('items'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_komponen' => 'required|string|max:255|unique:komponen_nilai,nama_komponen',
            'bobot' => 'nullable|numeric|min:0|max:100',
        ]);

        KomponenNilai::create($validated);

        return redirect()->route('komponen_nilai.index')->with('success', 'Komponen penilaian berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = KomponenNilai::findOrFail($id);
        return view('komponen_nilai.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = KomponenNilai::findOrFail($id);

        $validated = $request->validate([
            'nama_komponen' => 'required|string|max:255|unique:komponen_nilai,nama_komponen,' . $item->id,
            'bobot' => 'nullable|numeric|min:0|max:100',
        ]);

        $item->update($validated);

        return redirect()->route('komponen_nilai.index')->with('success', 'Komponen penilaian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = KomponenNilai::findOrFail($id);
        $item->delete();

        return redirect()->route('komponen_nilai.index')->with('success', 'Komponen penilaian berhasil dihapus.');
    }
}
