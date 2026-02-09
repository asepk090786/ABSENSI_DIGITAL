<?php

namespace App\Http\Controllers;

use App\Models\KomponenNilai;
use App\Models\CapaianPembelajaran;
use App\Exports\KomponenNilaiExport;
use App\Exports\KomponenNilaiTemplateExport;
use App\Imports\KomponenNilaiImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class KomponenNilaiController extends Controller
{
    public function index()
    {
        $items = KomponenNilai::orderBy('nama_komponen')->get();
        $capaianList = CapaianPembelajaran::orderBy('nama_capaian_pembelajaran')->get();
        return view('komponen_nilai.index', compact('items', 'capaianList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'capaian_pembelajaran_id' => 'nullable|exists:capaian_pembelajarans,id',
            'nama_komponen' => 'required|string|max:255|unique:komponen_nilai,nama_komponen',
            'bobot' => 'nullable|numeric|min:0|max:100',
            'capaian_pembelajaran' => 'nullable|string',
            'tujuan_pembelajaran' => 'nullable|string',
            'alur_tujuan_pembelajaran' => 'nullable|string',
            'indikator_kriteria' => 'nullable|string',
        ]);

        KomponenNilai::create($validated);

        return redirect()->route('komponen_nilai.index')->with('success', 'Komponen penilaian berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = KomponenNilai::findOrFail($id);
        $capaianList = CapaianPembelajaran::orderBy('nama_capaian_pembelajaran')->get();
        return view('komponen_nilai.edit', compact('item', 'capaianList'));
    }

    public function update(Request $request, $id)
    {
        $item = KomponenNilai::findOrFail($id);

        $validated = $request->validate([
            'capaian_pembelajaran_id' => 'nullable|exists:capaian_pembelajarans,id',
            'nama_komponen' => 'required|string|max:255|unique:komponen_nilai,nama_komponen,' . $item->id,
            'bobot' => 'nullable|numeric|min:0|max:100',
            'capaian_pembelajaran' => 'nullable|string',
            'tujuan_pembelajaran' => 'nullable|string',
            'alur_tujuan_pembelajaran' => 'nullable|string',
            'indikator_kriteria' => 'nullable|string',
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

    public function export()
    {
        return Excel::download(new KomponenNilaiExport, 'komponen_nilai_' . date('Y-m-d') . '.xlsx');
    }

    public function template()
    {
        return Excel::download(new KomponenNilaiTemplateExport, 'template_komponen_nilai.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new KomponenNilaiImport, $request->file('file'));
            
            $errors = session()->get('import_errors', []);
            if (!empty($errors)) {
                return back()->with('warning', 'Import selesai dengan beberapa error. ' . count($errors) . ' baris gagal.')->with('import_errors', $errors);
            }
            
            return back()->with('success', 'Komponen Penilaian berhasil diimport.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error import: ' . $e->getMessage());
        }
    }
}
