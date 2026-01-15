<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataPelajaran;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\MataPelajaranExport;
use App\Exports\MataPelajaranTemplateExport;
use App\Imports\MataPelajaranImport;

class MataPelajaranController extends Controller
{
    public function index()
    {
        $items = MataPelajaran::orderBy('nama_mapel')->get();
        return view('mata_pelajaran.index', compact('items'));
    }

    public function create()
    {
        $jenisKegiatanList = \App\Models\Kegiatan::orderBy('nama_kegiatan')->get();
        return view('mata_pelajaran.create', compact('jenisKegiatanList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:255|unique:mata_pelajaran,nama_mapel',
            'kode_mapel' => 'nullable|string|max:50|unique:mata_pelajaran,kode_mapel',
            'kategori' => 'required|string|in:Umum,Jurusan,Pilihan,Tingkat lanjut,Mulok',
            'jenis_kegiatan_id' => 'nullable|exists:kegiatan,id',
        ]);

        MataPelajaran::create($validated);

        return redirect()->route('mata_pelajaran.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function show(MataPelajaran $mata_pelajaran)
    {
        return view('mata_pelajaran.show', compact('mata_pelajaran'));
    }

    public function edit(MataPelajaran $mata_pelajaran)
    {
        $jenisKegiatanList = \App\Models\Kegiatan::orderBy('nama_kegiatan')->get();
        return view('mata_pelajaran.edit', compact('mata_pelajaran', 'jenisKegiatanList'));
    }

    public function update(Request $request, MataPelajaran $mata_pelajaran)
    {
        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:255|unique:mata_pelajaran,nama_mapel,' . $mata_pelajaran->id,
            'kode_mapel' => 'nullable|string|max:50|unique:mata_pelajaran,kode_mapel,' . $mata_pelajaran->id,
            'kategori' => 'required|string|in:Umum,Jurusan,Pilihan,Tingkat lanjut,Mulok',
            'jenis_kegiatan_id' => 'nullable|exists:kegiatan,id',
        ]);

        $mata_pelajaran->update($validated);

        return redirect()->route('mata_pelajaran.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mata_pelajaran)
    {
        $mata_pelajaran->delete();
        return redirect()->route('mata_pelajaran.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new MataPelajaranExport, 'data_mata_pelajaran_' . date('Ymd_His') . '.xlsx');
    }

    public function templateDownload()
    {
        return Excel::download(new MataPelajaranTemplateExport, 'template_import_mata_pelajaran.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        try {
            $import = new MataPelajaranImport();
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();

            if (count($errors) > 0) {
                return redirect()->route('mata_pelajaran.index')
                    ->with('warning', 'Import selesai dengan beberapa error.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('mata_pelajaran.index')->with('success', 'Data mata pelajaran berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('mata_pelajaran.index')->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
