<?php

namespace App\Http\Controllers;

use App\Exports\JenisPelanggaranExport;
use App\Exports\JenisPelanggaranTemplateExport;
use App\Imports\JenisPelanggaranImport;
use App\Models\JenisPelanggaran;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class JenisPelanggaranController extends Controller
{
    public function index()
    {
        $items = JenisPelanggaran::orderBy('nama')->get();
        return view('jenis_pelanggaran.index', compact('items'));
    }

    public function create()
    {
        return view('jenis_pelanggaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:30|unique:jenis_pelanggaran,kode',
            'nama' => 'required|string|max:150',
            'poin_default' => 'required|integer|min:0|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        JenisPelanggaran::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'poin_default' => $validated['poin_default'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()->route('jenis_pelanggaran.index')->with('success', 'Jenis pelanggaran berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = JenisPelanggaran::findOrFail($id);
        return view('jenis_pelanggaran.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = JenisPelanggaran::findOrFail($id);

        $validated = $request->validate([
            'kode' => 'required|string|max:30|unique:jenis_pelanggaran,kode,' . $id,
            'nama' => 'required|string|max:150',
            'poin_default' => 'required|integer|min:0|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        $item->update([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'poin_default' => $validated['poin_default'],
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ]);

        return redirect()->route('jenis_pelanggaran.index')->with('success', 'Jenis pelanggaran berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = JenisPelanggaran::findOrFail($id);
        $item->delete();

        return redirect()->route('jenis_pelanggaran.index')->with('success', 'Jenis pelanggaran berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new JenisPelanggaranExport, 'jenis_pelanggaran_' . date('Y-m-d') . '.xlsx');
    }

    public function template()
    {
        return Excel::download(new JenisPelanggaranTemplateExport, 'template_jenis_pelanggaran.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new JenisPelanggaranImport, $request->file('file'));

            $errors = session()->get('import_errors', []);
            if (!empty($errors)) {
                return back()->with('warning', 'Import selesai dengan beberapa error. ' . count($errors) . ' baris gagal.')->with('import_errors', $errors);
            }

            return back()->with('success', 'Jenis pelanggaran berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error import: ' . $e->getMessage());
        }
    }
}
