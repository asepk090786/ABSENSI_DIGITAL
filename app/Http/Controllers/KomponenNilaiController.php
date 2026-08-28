<?php

namespace App\Http\Controllers;

use App\Models\KomponenNilai;
use App\Models\CapaianPembelajaran;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\JadwalKbm;
use App\Exports\KomponenNilaiExport;
use App\Exports\KomponenNilaiTemplateExport;
use App\Imports\KomponenNilaiImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rule;

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
            $query->where('guru_id', $user->guru_id);
        }

        return $query;
    }

    private function kelasList()
    {
        $user = auth()->user();

        if ($this->isTeacherUser()) {
            return JadwalKbm::with('kelas')
                ->where('guru_id', $user->guru_id)
                ->get()
                ->pluck('kelas')
                ->filter()
                ->unique('id')
                ->sortBy('nama_kelas')
                ->values();
        }

        return Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']);
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $canFilterGuru = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Pengawas Pembina']);
        $hasGuruFilter = $request->has('guru_id');
        $guruId = $request->query('guru_id');
        $guruList = Guru::orderBy('nama')->get(['id', 'nama', 'nip']);
        $isTeacherUser = $this->isTeacherUser();

        $query = $this->scopedKomponenQuery()
            ->with(['guru', 'mataPelajaran', 'kelas', 'kelasMany', 'capaianPembelajaran', 'rencanaPembelajaran.guru', 'rencanaPembelajaran.mataPelajaran', 'rencanaPembelajaran.kelas']);

        if ($hasGuruFilter && ! $guruId) {
            $query->whereNull('id');
        } elseif ($guruId) {
            if ($isTeacherUser && (string) $guruId !== (string) ($user->guru_id ?? '')) {
                $query->whereNull('id');
            } else {
                $query->where('guru_id', $guruId)
                    ->with([
                        'rencanaPembelajaran' => function ($q) use ($guruId) {
                            $q->where('guru_id', $guruId);
                        },
                        'rencanaPembelajaran.guru',
                        'rencanaPembelajaran.mataPelajaran',
                        'rencanaPembelajaran.kelas',
                    ]);
            }
        }

        $items = $query->orderBy('nama_komponen')->get();

        $groupedRows = [];
        foreach ($items as $item) {
            $rencanas = $item->rencanaPembelajaran;
            if ($rencanas->isEmpty()) {
                $groupedRows[] = [
                    'komponen' => $item,
                    'guru' => $item->guru,
                    'mapel' => $item->mataPelajaran ? [$item->mataPelajaran] : [],
                    'cp' => null,
                    'kelas' => collect([$item->kelas])->merge($item->kelasMany)->filter()->unique('id')->values()->all(),
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

                if ($item->kelas && ! in_array($item->kelas, $byGuruCpHtml[$key]['kelas'], true)) {
                    $byGuruCpHtml[$key]['kelas'][] = $item->kelas;
                }

                foreach ($item->kelasMany as $kelas) {
                    if (! collect($byGuruCpHtml[$key]['kelas'])->contains('id', $kelas->id)) {
                        $byGuruCpHtml[$key]['kelas'][] = $kelas;
                    }
                }
            }

            foreach ($byGuruCpHtml as $row) {
                $groupedRows[] = $row;
            }
        }

        $capaianList = $this->scopedCapaianQuery()->orderBy('nama_capaian_pembelajaran')->get();
        $mataPelajaranList = MataPelajaran::orderBy('nama_mapel')->get(['id', 'nama_mapel']);
        $kelasList = $this->kelasList();

        $penilaianRows = $groupedRows;
        if ($user && ! $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Pengawas Pembina']) && ! empty($user->guru_id)) {
            $penilaianRows = collect($groupedRows)->filter(function ($row) use ($user) {
                $komponen = $row['komponen'] ?? null;
                if ($komponen && $komponen->guru && $komponen->guru->id === (int) $user->guru_id) {
                    return true;
                }
                return false;
            })->values()->all();
        } elseif ($user && ! $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Pengawas Pembina']) && empty($user->guru_id)) {
            $penilaianRows = [];
        }

        return view('komponen_nilai.index', compact('items', 'capaianList', 'mataPelajaranList', 'kelasList', 'groupedRows', 'penilaianRows', 'guruList', 'guruId', 'canFilterGuru', 'isTeacherUser'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $guruId = $this->isTeacherUser() ? $user->guru_id : null;
        $validated = $request->validate([
            'capaian_pembelajaran_id' => 'nullable|exists:capaian_pembelajarans,id',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajaran,id',
            'kelas_ids' => 'nullable|array',
            'kelas_ids.*' => 'integer|exists:kelas,id',
            'nama_komponen' => ['required', 'string', 'max:255', Rule::unique('komponen_nilai', 'nama_komponen')->where(fn ($query) => $query->where('guru_id', $guruId))],
            'bobot' => 'nullable|numeric|min:0|max:100',
            'domain' => 'nullable|string|max:20|in:kognitif,afektif,psikomotorik',
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

        $kelasIds = collect($validated['kelas_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        if ($this->isTeacherUser() && $kelasIds->diff($this->kelasList()->pluck('id'))->isNotEmpty()) {
            abort(403, 'Akses ditolak untuk kelas ini.');
        }

        $validated['guru_id'] = $guruId;
        $validated['kelas_id'] = $kelasIds->first();
        unset($validated['kelas_ids']);
        $item = KomponenNilai::create($validated);
        $item->kelasMany()->sync($kelasIds->all());

        return redirect()->route('komponen_nilai.index')->with('success', 'Komponen penilaian berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $item = $this->scopedKomponenQuery()->with('kelasMany')->findOrFail($id);
        $capaianList = $this->scopedCapaianQuery()->orderBy('nama_capaian_pembelajaran')->get();
        $mataPelajaranList = MataPelajaran::orderBy('nama_mapel')->get(['id', 'nama_mapel']);
        $kelasList = $this->kelasList();
        return view('komponen_nilai.edit', compact('item', 'capaianList', 'mataPelajaranList', 'kelasList'));
    }

    public function update(Request $request, $id)
    {
        $item = $this->scopedKomponenQuery()->findOrFail($id);
        $guruId = $this->isTeacherUser() ? auth()->user()->guru_id : null;

        $validated = $request->validate([
            'capaian_pembelajaran_id' => 'nullable|exists:capaian_pembelajarans,id',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajaran,id',
            'kelas_ids' => 'nullable|array',
            'kelas_ids.*' => 'integer|exists:kelas,id',
            'nama_komponen' => ['required', 'string', 'max:255', Rule::unique('komponen_nilai', 'nama_komponen')->ignore($item->id)->where(fn ($query) => $query->where('guru_id', $guruId))],
            'bobot' => 'nullable|numeric|min:0|max:100',
            'domain' => 'nullable|string|max:20|in:kognitif,afektif,psikomotorik',
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

        $kelasIds = collect($validated['kelas_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        if ($this->isTeacherUser() && $kelasIds->diff($this->kelasList()->pluck('id'))->isNotEmpty()) {
            abort(403, 'Akses ditolak untuk kelas ini.');
        }

        if ($this->isTeacherUser()) {
            $validated['guru_id'] = $guruId;
        }
        $validated['kelas_id'] = $kelasIds->first();
        unset($validated['kelas_ids']);
        $item->update($validated);
        $item->kelasMany()->sync($kelasIds->all());

        return redirect()->route('komponen_nilai.index')->with('success', 'Komponen penilaian berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $item = $this->scopedKomponenQuery()->findOrFail($id);

        $usedInNilai = DB::table('nilai_harian')
            ->where('komponen_id', $item->id)
            ->exists();

        if ($usedInNilai) {
            return back()->with('warning', 'Komponen penilaian tidak dapat dihapus karena masih digunakan di data nilai harian.');
        }

        $item->delete();

        return back()->with('success', 'Komponen penilaian berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new KomponenNilaiExport(auth()->user(), $this->scopedKomponenQuery()), 'komponen_nilai_' . date('Y-m-d') . '.xlsx');
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
