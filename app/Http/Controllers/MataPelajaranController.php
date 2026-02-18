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
    private function ensureCanManageMapel(): void
    {
        $user = auth()->user();

        if ($user && $user->hasAnyRole(['Guru', 'Guru Mapel', 'Guru Kelas', 'Wali Kelas'])) {
            abort(403, 'Akses tambah/edit mata pelajaran tidak diizinkan untuk role ini.');
        }
    }

    public function index()
    {
        $items = MataPelajaran::orderBy('nama_mapel')->get();
        return view('mata_pelajaran.index', compact('items'));
    }

    /**
     * Daftar mata pelajaran khusus guru (berdasarkan jadwal KBM yang diajar).
     */
    public function guruIndex()
    {
        $user = auth()->user();
        $guru = $user->guru;

        if (!$guru) {
            return redirect()->route('mata_pelajaran.index')->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        // Fetch jadwal KBM dengan relasi kelas dan mata pelajaran
        $jadwalKbm = \App\Models\JadwalKbm::where('guru_id', $guru->id)
            ->with(['mataPelajaran', 'kelas'])
            ->whereNotNull('mata_pelajaran_id')
            ->get();

        // Group by mata pelajaran ID AND tingkat kelas
        $groupedByMapelAndTingkat = [];
        foreach ($jadwalKbm as $jadwal) {
            $key = $jadwal->mata_pelajaran_id . '_' . $jadwal->kelas->tingkat_kelas;
            if (!isset($groupedByMapelAndTingkat[$key])) {
                $groupedByMapelAndTingkat[$key] = [];
            }
            $groupedByMapelAndTingkat[$key][] = $jadwal;
        }

        // Create items array with separate rows per tingkat
        $items = [];
        foreach ($groupedByMapelAndTingkat as $group) {
            $mataPelajaran = $group[0]->mataPelajaran;
            $kelas = collect($group)->pluck('kelas')->unique('id')->values();
            
            // Create a new object for this tingkat combination
            $item = clone $mataPelajaran;
            $item->kelas_list = $kelas;
            $item->kelas_names = $kelas->pluck('nama_kelas')->sort()->join(', ');
            $item->tingkat = $kelas->first()->tingkat_kelas ?? '-';
            
            $items[] = $item;
        }

        // Sort by mata pelajaran name, then by tingkat
        usort($items, function($a, $b) {
            $cmp = strcasecmp($a->nama_mapel, $b->nama_mapel);
            if ($cmp !== 0) return $cmp;
            return strcmp($b->tingkat, $a->tingkat); // Descending order (XII before XI)
        });

        return view('mata_pelajaran.index', [
            'items' => $items,
            'isGuruView' => true,
        ]);
    }

    public function create()
    {
        $this->ensureCanManageMapel();

        $jenisKegiatanList = \App\Models\Kegiatan::orderBy('nama_kegiatan')->get();
        return view('mata_pelajaran.create', compact('jenisKegiatanList'));
    }

    public function store(Request $request)
    {
        $this->ensureCanManageMapel();

        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:255|unique:mata_pelajaran,nama_mapel',
            'kode_mapel' => 'nullable|string|max:50|unique:mata_pelajaran,kode_mapel',
            'kategori' => 'required|string|in:Umum,Jurusan,Pilihan,Tingkat lanjut,Mulok',
            'jenis_kegiatan_id' => 'nullable|exists:jenis_kegiatan,id',
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
        $this->ensureCanManageMapel();

        $jenisKegiatanList = \App\Models\Kegiatan::orderBy('nama_kegiatan')->get();
        return view('mata_pelajaran.edit', compact('mata_pelajaran', 'jenisKegiatanList'));
    }

    public function update(Request $request, MataPelajaran $mata_pelajaran)
    {
        $this->ensureCanManageMapel();

        $validated = $request->validate([
            'nama_mapel' => 'required|string|max:255|unique:mata_pelajaran,nama_mapel,' . $mata_pelajaran->id,
            'kode_mapel' => 'nullable|string|max:50|unique:mata_pelajaran,kode_mapel,' . $mata_pelajaran->id,
            'kategori' => 'required|string|in:Umum,Jurusan,Pilihan,Tingkat lanjut,Mulok',
            'jenis_kegiatan_id' => 'nullable|exists:jenis_kegiatan,id',
        ]);

        $mata_pelajaran->update($validated);

        return redirect()->route('mata_pelajaran.index')->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(MataPelajaran $mata_pelajaran)
    {
        $this->ensureCanManageMapel();

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
        $this->ensureCanManageMapel();

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
