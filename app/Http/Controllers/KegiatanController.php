<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kegiatan;

class KegiatanController extends Controller
{
    public function index()
    {
        $items = Kegiatan::orderBy('nama_kegiatan')->get();
        return view('kegiatan.index', compact('items'));
    }

    public function create()
    {
        return view('kegiatan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255|unique:kegiatan,nama_kegiatan',
            'kode_kegiatan' => 'nullable|string|max:50|unique:kegiatan,kode_kegiatan',
            'kategori' => 'required|string|in:Umum,Jurusan,Pilihan,Tingkat lanjut,Mulok',
        ]);

        Kegiatan::create($validated);

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil ditambahkan.');
    }

    public function show(Kegiatan $kegiatan)
    {
        return view('kegiatan.show', compact('kegiatan'));
    }

    public function edit(Kegiatan $kegiatan)
    {
        return view('kegiatan.edit', compact('kegiatan'));
    }

    public function update(Request $request, Kegiatan $kegiatan)
    {
        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255|unique:kegiatan,nama_kegiatan,' . $kegiatan->id,
            'kode_kegiatan' => 'nullable|string|max:50|unique:kegiatan,kode_kegiatan,' . $kegiatan->id,
            'kategori' => 'required|string|in:Umum,Jurusan,Pilihan,Tingkat lanjut,Mulok',
        ]);

        $kegiatan->update($validated);

        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy(Kegiatan $kegiatan)
    {
        $kegiatan->delete();
        return redirect()->route('kegiatan.index')->with('success', 'Kegiatan berhasil dihapus.');
    }
}
