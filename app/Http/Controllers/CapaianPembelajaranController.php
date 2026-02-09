<?php

namespace App\Http\Controllers;

use App\Models\CapaianPembelajaran;
use App\Exports\CapaianPembelajaranExport;
use App\Exports\CapaianPembelajaranTemplateExport;
use App\Imports\CapaianPembelajaranImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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

    public function export()
    {
        return Excel::download(new CapaianPembelajaranExport, 'capaian_pembelajaran_' . date('Y-m-d') . '.xlsx');
    }

    public function template()
    {
        return Excel::download(new CapaianPembelajaranTemplateExport, 'template_capaian_pembelajaran.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new CapaianPembelajaranImport, $request->file('file'));
            
            $errors = session()->get('import_errors', []);
            if (!empty($errors)) {
                return back()->with('warning', 'Import selesai dengan beberapa error. ' . count($errors) . ' baris gagal.')->with('import_errors', $errors);
            }
            
            return back()->with('success', 'Capaian Pembelajaran berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error import: ' . $e->getMessage());
        }
    }
}