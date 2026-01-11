<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KurikulumExport;
use App\Imports\KurikulumImport;

class KurikulumController extends Controller
{
    private array $jurusanList = ['IPA','IPS','MIA','IIS','UMUMJ'];

    public function index(Request $request)
    {
        $tingkatList = Kelas::select('tingkat_kelas')->distinct()->pluck('tingkat_kelas')->filter()->values()->all();
        sort($tingkatList);

        $selectedTingkat = $request->get('tingkat', $tingkatList[0] ?? null);
        $selectedJurusan = $request->get('jurusan', $this->jurusanList[0]);

        $mapel = MataPelajaran::orderBy('nama_mapel')->get();

        // Data tampilan tabel per tingkat
        $kurikulumByTingkat = DB::table('kurikulum_mapel as k')
            ->join('mata_pelajaran as m', 'm.id', '=', 'k.mata_pelajaran_id')
            ->select('k.tingkat', 'k.jurusan', 'm.nama_mapel', 'm.kode_mapel', 'k.jp')
            ->orderBy('k.tingkat')->orderBy('k.jurusan')->orderBy('m.nama_mapel')
            ->get()
            ->groupBy('tingkat');

        $totalJpPerTingkat = [];
        foreach ($kurikulumByTingkat as $tingkat => $items) {
            $totalJpPerTingkat[$tingkat] = $items->sum('jp');
        }

        return view('kurikulum.index', [
            'jurusanList' => $this->jurusanList,
            'tingkatList' => $tingkatList,
            'selectedTingkat' => $selectedTingkat,
            'selectedJurusan' => $selectedJurusan,
            'mapel' => $mapel,
            'kurikulumByTingkat' => $kurikulumByTingkat,
            'totalJpPerTingkat' => $totalJpPerTingkat,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tingkat' => 'required|string|max:10',
            'jurusan' => 'nullable|string|max:20|in:IPA,IPS,MIA,IIS,UMUMJ',
            'mata_pelajaran_id' => 'array',
            'mata_pelajaran_id.*' => 'exists:mata_pelajaran,id',
            'jp' => 'array',
            'jp.*' => 'nullable|integer|min:0|max:50',
        ]);

        DB::transaction(function() use ($validated) {
            DB::table('kurikulum_mapel')
                ->where('tingkat', $validated['tingkat'])
                ->where('jurusan', $validated['jurusan'])
                ->delete();

            $mapelIds = $validated['mata_pelajaran_id'] ?? [];
            $jpInputs = request()->input('jp', []);
            foreach ($mapelIds as $mapelId) {
                $jp = isset($jpInputs[$mapelId]) ? (int) $jpInputs[$mapelId] : 0;
                DB::table('kurikulum_mapel')->insert([
                    'tingkat' => $validated['tingkat'],
                    'jurusan' => $validated['jurusan'],
                    'mata_pelajaran_id' => $mapelId,
                    'jp' => $jp,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return back()->with('success', 'Struktur kurikulum berhasil disimpan untuk tingkat dan jurusan yang dipilih.');
    }

    public function addItem(Request $request)
    {
        $validated = $request->validate([
            'tingkat' => 'required|string|max:10',
            'jurusan' => 'nullable|string|max:20|in:IPA,IPS,MIA,IIS,UMUMJ',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'jp' => 'required|integer|min:0|max:50',
        ]);

        DB::table('kurikulum_mapel')->updateOrInsert(
            [
                'tingkat' => $validated['tingkat'],
                'jurusan' => $validated['jurusan'],
                'mata_pelajaran_id' => $validated['mata_pelajaran_id'],
            ],
            [
                'jp' => $validated['jp'],
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('success', 'Mapel berhasil ditambahkan/diupdate.');
    }

    public function updateItem(Request $request, $id)
    {
        $validated = $request->validate([
            'jp' => 'required|integer|min:0|max:50',
        ]);

        DB::table('kurikulum_mapel')->where('id', $id)->update([
            'jp' => $validated['jp'],
            'updated_at' => now(),
        ]);

        return back()->with('success', 'JP berhasil diperbarui.');
    }

    public function deleteItem($id)
    {
        DB::table('kurikulum_mapel')->where('id', $id)->delete();
        return back()->with('success', 'Mapel dihapus dari struktur.');
    }

    public function export(Request $request)
    {
        $tingkat = $request->get('tingkat');
        $jurusan = $request->get('jurusan');
        $file = 'kurikulum_' . ($tingkat ?? 'all') . '_' . ($jurusan ?? 'all') . '_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new KurikulumExport($tingkat, $jurusan), $file);
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'tingkat' => 'required|string|max:10',
            'jurusan' => 'nullable|string|max:20|in:IPA,IPS,MIA,IIS,UMUMJ',
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        Excel::import(new KurikulumImport($validated['tingkat'], $validated['jurusan']), $validated['file']);

        return back()->with('success', 'Import struktur kurikulum berhasil.');
    }
}
