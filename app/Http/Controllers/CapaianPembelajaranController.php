<?php

namespace App\Http\Controllers;

use App\Models\CapaianPembelajaran;
use App\Exports\CapaianPembelajaranExport;
use App\Exports\CapaianPembelajaranTemplateExport;
use App\Imports\CapaianPembelajaranImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;

class CapaianPembelajaranController extends Controller
{
    private function isGuruMapel(): bool
    {
        return auth()->check() && auth()->user()->hasRole('Guru Mapel');
    }

    private function scopedCapaianQuery()
    {
        $query = CapaianPembelajaran::query();

        if ($this->isGuruMapel() && Schema::hasColumn('capaian_pembelajarans', 'user_id')) {
            $query->where('user_id', auth()->id());
        }

        return $query;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_capaian_pembelajaran' => 'required|string|max:191|unique:capaian_pembelajarans,nama_capaian_pembelajaran',
            'deskripsi' => 'nullable|string',
            'fase' => 'nullable|string|max:1',
            'tujuan_pembelajaran' => 'nullable|string',
            'alur_tujuan_pembelajaran' => 'nullable|string',
            'indikator_kriteria' => 'nullable|string',
        ]);

        if (Schema::hasColumn('capaian_pembelajarans', 'user_id')) {
            $validated['user_id'] = auth()->id();
        }

        CapaianPembelajaran::create($validated);

        return back()->with('success', 'Capaian Pembelajaran berhasil ditambahkan');
    }

    public function update(Request $request, CapaianPembelajaran $capaianPembelajaran)
    {
        if ($this->isGuruMapel() && Schema::hasColumn('capaian_pembelajarans', 'user_id') && $capaianPembelajaran->user_id !== auth()->id()) {
            abort(403, 'Anda tidak diizinkan mengubah CP milik guru lain.');
        }

        $validated = $request->validate([
            'nama_capaian_pembelajaran' => 'required|string|max:191|unique:capaian_pembelajarans,nama_capaian_pembelajaran,' . $capaianPembelajaran->id,
            'deskripsi' => 'nullable|string',
            'fase' => 'nullable|string|max:1',
            'tujuan_pembelajaran' => 'nullable|string',
            'alur_tujuan_pembelajaran' => 'nullable|string',
            'indikator_kriteria' => 'nullable|string',
        ]);

        $capaianPembelajaran->update($validated);

        return back()->with('success', 'Capaian Pembelajaran berhasil diperbarui');
    }

    public function destroy(CapaianPembelajaran $capaianPembelajaran)
    {
        if ($this->isGuruMapel() && Schema::hasColumn('capaian_pembelajarans', 'user_id') && $capaianPembelajaran->user_id !== auth()->id()) {
            abort(403, 'Anda tidak diizinkan menghapus CP milik guru lain.');
        }

        $capaianPembelajaran->delete();

        return back()->with('success', 'Capaian Pembelajaran berhasil dihapus');
    }

    public function list()
    {
        $capaian = $this->scopedCapaianQuery()->orderBy('nama_capaian_pembelajaran')->get();
        return response()->json($capaian);
    }

    public function export()
    {
        return Excel::download(new CapaianPembelajaranExport(auth()->user()), 'capaian_pembelajaran_' . date('Y-m-d') . '.xlsx');
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