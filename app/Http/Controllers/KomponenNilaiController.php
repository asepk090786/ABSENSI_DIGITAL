<?php

namespace App\Http\Controllers;

use App\Models\KomponenNilai;
use App\Models\CapaianPembelajaran;
use App\Models\Guru;
use App\Exports\KomponenNilaiExport;
use App\Exports\KomponenNilaiTemplateExport;
use App\Imports\KomponenNilaiImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class KomponenNilaiController extends Controller
{
    private function isTeacherUser(): bool
    {
        $user = auth()->user();
        return $user && ! $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Pengawas Pembina']) && ! empty($user->guru_id);
    }

    private function scopedCapaianQuery()
    {
        $query = CapaianPembelajaran::query();
        $user = auth()->user();

        if ($user && ! $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Pengawas Pembina']) && ! empty($user->guru_id)) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    private function scopedKomponenQuery()
    {
        $query = KomponenNilai::query();
        $user = auth()->user();

        if ($user && ! $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Pengawas Pembina']) && ! empty($user->guru_id)) {
            $query->where(function ($q) use ($user) {
                $q->whereNull('capaian_pembelajaran_id')
                  ->orWhereHas('capaianPembelajaran', function ($q2) use ($user) {
                      $q2->where('user_id', $user->id);
                  });
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $guruId = $request->query('guru_id');
        $guruList = Guru::orderBy('nama')->get(['id', 'nama', 'nip']);

        $query = $this->scopedKomponenQuery()
            ->with(['capaianPembelajaran', 'rencanaPembelajaran.guru', 'rencanaPembelajaran.mataPelajaran', 'rencanaPembelajaran.kelas']);

        if ($guruId) {
            $query->whereHas('rencanaPembelajaran', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            });
        }

        $items = $query->orderBy('nama_komponen')->get();

        $groupedRows = [];
        foreach ($items as $item) {
            $rencanas = $item->rencanaPembelajaran;
            if ($rencanas->isEmpty()) {
                $groupedRows[] = [
                    'komponen' => $item,
                    'guru' => null,
                    'mapel' => [],
                    'cp' => null,
                    'kelas' => [],
                    'rencana' => null,
                ];
                continue;
            }

            $byGuruCpHtml = [];
            foreach ($rencanas as $rencana) {
                $guru = $rencana->guru;
                $cp = $item->capaianPembelajaran;
                $key = ($guru ? $guru->id : 'null') . '|' . ($cp ? $cp->id : 'null');

                if (! isset($byGuruCpHtml[$key])) {
                    $byGuruCpHtml[$key] = [
                        'komponen' => $item,
                        'guru' => $guru,
                        'cp' => $cp,
                        'mapel' => [],
                        'kelas' => [],
                    ];
                }

                if ($rencana->mataPelajaran && ! in_array($rencana->mataPelajaran, $byGuruCpHtml[$key]['mapel'], true)) {
                    $byGuruCpHtml[$key]['mapel'][] = $rencana->mataPelajaran;
                }

                if ($rencana->kelas && ! in_array($rencana->kelas, $byGuruCpHtml[$key]['kelas'], true)) {
                    $byGuruCpHtml[$key]['kelas'][] = $rencana->kelas;
                }
            }

            foreach ($byGuruCpHtml as $row) {
                $groupedRows[] = $row;
            }
        }

        $capaianList = $this->scopedCapaianQuery()->orderBy('nama_capaian_pembelajaran')->get();

        return view('komponen_nilai.index', compact('items', 'capaianList', 'groupedRows', 'guruList', 'guruId'));
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

        if ($this->isTeacherUser() && $validated['capaian_pembelajaran_id']) {
            $allowed = CapaianPembelajaran::where('id', $validated['capaian_pembelajaran_id'])
                ->where('user_id', auth()->id())
                ->exists();

            if (! $allowed) {
                abort(403, 'Akses ditolak untuk capaian pembelajaran ini.');
            }
        }

        KomponenNilai::create($validated);

        return redirect()->route('komponen_nilai.index')->with('success', 'Komponen penilaian berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->scopedKomponenQuery()->findOrFail($id);
        $capaianList = $this->scopedCapaianQuery()->orderBy('nama_capaian_pembelajaran')->get();
        return view('komponen_nilai.edit', compact('item', 'capaianList'));
    }

    public function update(Request $request, $id)
    {
        $item = $this->scopedKomponenQuery()->findOrFail($id);

        $validated = $request->validate([
            'capaian_pembelajaran_id' => 'nullable|exists:capaian_pembelajarans,id',
            'nama_komponen' => 'required|string|max:255|unique:komponen_nilai,nama_komponen,' . $item->id,
            'bobot' => 'nullable|numeric|min:0|max:100',
            'capaian_pembelajaran' => 'nullable|string',
            'tujuan_pembelajaran' => 'nullable|string',
            'alur_tujuan_pembelajaran' => 'nullable|string',
            'indikator_kriteria' => 'nullable|string',
        ]);

        if ($this->isTeacherUser() && $validated['capaian_pembelajaran_id']) {
            $allowed = CapaianPembelajaran::where('id', $validated['capaian_pembelajaran_id'])
                ->where('user_id', auth()->id())
                ->exists();

            if (! $allowed) {
                abort(403, 'Akses ditolak untuk capaian pembelajaran ini.');
            }
        }

        $item->update($validated);

        return redirect()->route('komponen_nilai.index')->with('success', 'Komponen penilaian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = $this->scopedKomponenQuery()->findOrFail($id);
        $item->delete();

        return redirect()->route('komponen_nilai.index')->with('success', 'Komponen penilaian berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new KomponenNilaiExport(auth()->user()), 'komponen_nilai_' . date('Y-m-d') . '.xlsx');
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
