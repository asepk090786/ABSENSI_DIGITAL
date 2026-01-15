<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisKegiatan;

class JenisKegiatanController extends Controller
{
    public function index()
    {
        $data = JenisKegiatan::all();
        return view('jenis_kegiatan.index', compact('data'));
    }

    public function create()
    {
        return view('jenis_kegiatan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:jenis_kegiatan,kode',
        ]);
        JenisKegiatan::create($request->only(['nama', 'kode']));
        return redirect()->route('jenis_kegiatan.index')->with('success', 'Jenis Kegiatan berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $item = JenisKegiatan::findOrFail($id);
        return view('jenis_kegiatan.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = JenisKegiatan::findOrFail($id);
        $request->validate([
            'nama' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:jenis_kegiatan,kode,' . $id,
        ]);
        $item->update($request->only(['nama', 'kode']));
        return redirect()->route('jenis_kegiatan.index')->with('success', 'Jenis Kegiatan berhasil diupdate!');
    }

    public function destroy($id)
    {
        $item = JenisKegiatan::findOrFail($id);
        $item->delete();
        return redirect()->route('jenis_kegiatan.index')->with('success', 'Jenis Kegiatan berhasil dihapus!');
    }
}
