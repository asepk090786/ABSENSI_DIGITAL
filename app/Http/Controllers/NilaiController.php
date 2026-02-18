<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JadwalKbm;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\RencanaPembelajaran;
use App\Models\Siswa;
use App\Imports\NilaiHarianImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\NilaiHarianTemplateExport;

class NilaiController extends Controller
{
    public function index()
    {
        $kelasId = request()->get('kelas_id');
        $mapelId = request()->get('mapel_id');
        $quickMenus = collect();
        $filterKelasName = null;
        $filterMapelName = null;
        $kelasOptions = collect();
        $mapelOptions = collect();
        $mapelByKelas = collect();
        $rencanaByMapel = [];
        $debugRencana = null;
        $komponenList = DB::table('komponen_nilai')->orderBy('nama_komponen')->get();

        $user = auth()->user();
        $guru = $user ? $user->guru : null;

        if ($guru) {
            $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
            $semesterAktif = Semester::where('is_active', true)->first();

            $jadwalQuery = JadwalKbm::with(['kelas', 'mataPelajaran'])
                ->where('guru_id', $guru->id)
                ->whereNotNull('mata_pelajaran_id')
                ->whereNotNull('kelas_id');

            if ($tahunAjaranAktif) {
                $jadwalQuery->where('tahun_ajaran_id', $tahunAjaranAktif->id);
            }
            if ($semesterAktif) {
                $jadwalQuery->where('semester_id', $semesterAktif->id);
            }

            $jadwalGuru = $jadwalQuery->get();

            $quickMenus = $jadwalGuru
                ->unique(function ($item) {
                    return $item->kelas_id . '-' . $item->mata_pelajaran_id;
                })
                ->values();

            $kelasOptions = $jadwalGuru->pluck('kelas')->filter()->unique('id')->values();
            $mapelOptions = $jadwalGuru->pluck('mataPelajaran')->filter()->unique('id')->values();

            $mapelByKelas = $jadwalGuru->groupBy('kelas_id')->map(function ($group) {
                return $group->pluck('mataPelajaran')
                    ->filter()
                    ->unique('id')
                    ->map(function ($mapel) {
                        return [
                            'id' => $mapel->id,
                            'nama' => $mapel->nama_mapel,
                        ];
                    })
                    ->values();
            });

            $mapelIds = $mapelOptions->pluck('id');
            if ($mapelIds->isNotEmpty()) {
                $rencana = RencanaPembelajaran::where('guru_id', $guru->id)
                    ->whereIn('mata_pelajaran_id', $mapelIds)
                    ->orderBy('created_at', 'desc')
                    ->get();

                foreach ($rencana as $item) {
                    $kelasKey = (string) $item->kelas_id;
                    $mapelKey = (string) $item->mata_pelajaran_id;

                    if (!isset($rencanaByMapel[$kelasKey])) {
                        $rencanaByMapel[$kelasKey] = [];
                    }
                    if (!isset($rencanaByMapel[$kelasKey][$mapelKey])) {
                        $rencanaByMapel[$kelasKey][$mapelKey] = [];
                    }

                    // Hindari judul duplikat pada kombinasi kelas + mapel
                    $sudahAda = collect($rencanaByMapel[$kelasKey][$mapelKey])
                        ->contains(function ($row) use ($item) {
                            return mb_strtolower(trim((string) $row['judul'])) === mb_strtolower(trim((string) $item->judul));
                        });

                    if (!$sudahAda) {
                        $rencanaByMapel[$kelasKey][$mapelKey][] = [
                            'id' => $item->id,
                            'judul' => $item->judul,
                        ];
                    }
                }

                if (request()->get('debug') === '1') {
                    $debugRencana = [
                        'total' => $rencana->count(),
                        'sample' => $rencana->take(5)->load(['kelas', 'mataPelajaran']),
                    ];
                }
            }

        }

        $itemsQuery = DB::table('nilai_harian')
            ->join('siswa', 'nilai_harian.siswa_id', '=', 'siswa.id')
            ->join('mata_pelajaran', 'nilai_harian.mapel_id', '=', 'mata_pelajaran.id')
            ->leftJoin('kelas', 'nilai_harian.kelas_id', '=', 'kelas.id')
            ->leftJoin('komponen_nilai', 'nilai_harian.komponen_id', '=', 'komponen_nilai.id')
            ->select(
                'nilai_harian.*',
                'siswa.nama as nama_siswa',
                'mata_pelajaran.nama_mapel',
                'kelas.nama_kelas',
                'komponen_nilai.nama_komponen'
            );

        // Filter by active tahun ajaran and semester
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        
        if ($tahunAjaranAktif) {
            $itemsQuery->where('nilai_harian.tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $itemsQuery->where('nilai_harian.semester_id', $semesterAktif->id);
        }

        if ($kelasId) {
            $itemsQuery->where('nilai_harian.kelas_id', $kelasId);
            $filterKelasName = Kelas::where('id', $kelasId)->value('nama_kelas');
        }
        if ($mapelId) {
            $itemsQuery->where('nilai_harian.mapel_id', $mapelId);
            $filterMapelName = MataPelajaran::where('id', $mapelId)->value('nama_mapel');
        }
        $items = $itemsQuery->orderBy('nilai_harian.created_at','desc')->get();

        $nilaiKomponenColumns = $komponenList
            ->map(function ($komponen) {
                return (object) [
                    'id' => (int) $komponen->id,
                    'nama' => $komponen->nama_komponen,
                ];
            })
            ->values();
        $nilaiTableRows = collect();

        if ($items->isNotEmpty()) {
            $hasHarian = $items->contains(function ($row) {
                return $row->komponen_id === null;
            });

            if ($hasHarian) {
                $nilaiKomponenColumns = collect([(object) ['id' => 0, 'nama' => 'Harian']])
                    ->merge($nilaiKomponenColumns)
                    ->values();
            }

            $nilaiTableRows = $items
                ->groupBy('siswa_id')
                ->map(function ($rows) use ($nilaiKomponenColumns) {
                    $first = $rows->first();
                    $nilaiByKomponen = [];
                    $nilaiIdByKomponen = [];
                    $nilaiValues = [];

                    foreach ($nilaiKomponenColumns as $komponen) {
                        $komponenId = (int) $komponen->id;
                        $match = $rows->first(function ($item) use ($komponenId) {
                            return (int) ($item->komponen_id ?? 0) === $komponenId;
                        });

                        if ($match) {
                            $nilaiByKomponen[$komponenId] = $match->nilai;
                            $nilaiIdByKomponen[$komponenId] = $match->id;
                            if ($match->nilai !== null && $match->nilai !== '') {
                                $nilaiValues[] = (float) $match->nilai;
                            }
                        } else {
                            $nilaiByKomponen[$komponenId] = null;
                            $nilaiIdByKomponen[$komponenId] = null;
                        }
                    }

                    $jumlah = count($nilaiValues) ? array_sum($nilaiValues) : null;
                    $rataRata = count($nilaiValues) ? ($jumlah / count($nilaiValues)) : null;

                    return (object) [
                        'siswa_id' => $first->siswa_id,
                        'nama_siswa' => $first->nama_siswa,
                        'nilai_by_komponen' => $nilaiByKomponen,
                        'nilai_id_by_komponen' => $nilaiIdByKomponen,
                        'jumlah' => $jumlah,
                        'rata_rata' => $rataRata,
                    ];
                })
                ->sortBy(function ($row) {
                    return mb_strtolower((string) $row->nama_siswa);
                })
                ->values();
        }

        return view('nilai.index', compact(
            'items',
            'nilaiKomponenColumns',
            'nilaiTableRows',
            'quickMenus',
            'kelasId',
            'mapelId',
            'filterKelasName',
            'filterMapelName',
            'kelasOptions',
            'mapelOptions',
            'mapelByKelas',
            'rencanaByMapel',
            'debugRencana',
            'komponenList',
            'tahunAjaranAktif',
            'semesterAktif'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'rencana_pembelajaran_id' => 'required|exists:rencana_pembelajarans,id',
            'komponen_id' => 'nullable|exists:komponen_nilai,id',
        ]);

        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        if (!$guru) {
            return redirect()->route('nilai.index')->with('error', 'Akun guru tidak ditemukan.');
        }

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();

        if (!$tahunAjaranAktif || !$semesterAktif) {
            return redirect()->route('nilai.index')->with('error', 'Tahun ajaran atau semester aktif belum diatur.');
        }

        $siswaIds = Siswa::where('kelas_id', $validated['kelas_id'])->pluck('id');
        if ($siswaIds->isEmpty()) {
            return redirect()->route('nilai.index')->with('error', 'Tidak ada siswa pada kelas ini.');
        }

        $existingSiswaIds = DB::table('nilai_harian')
            ->where('kelas_id', $validated['kelas_id'])
            ->where('mapel_id', $validated['mapel_id'])
            ->whereDate('tanggal', $validated['tanggal'])
            ->where('rencana_pembelajaran_id', $validated['rencana_pembelajaran_id'])
            ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
            ->where('semester_id', $semesterAktif->id)
            ->whereIn('siswa_id', $siswaIds)
            ->pluck('siswa_id');

        $newSiswaIds = $siswaIds->diff($existingSiswaIds);
        if ($newSiswaIds->isEmpty()) {
            return redirect()->route('nilai.index')->with('warning', 'Nilai harian untuk rencana ini sudah dibuat.');
        }

        $now = now();
        $rows = $newSiswaIds->map(function ($siswaId) use ($validated, $guru, $tahunAjaranAktif, $semesterAktif, $now) {
            return [
                'siswa_id' => $siswaId,
                'guru_id' => $guru->id,
                'kelas_id' => $validated['kelas_id'],
                'mapel_id' => $validated['mapel_id'],
                'komponen_id' => $validated['komponen_id'] ?? null,
                'rencana_pembelajaran_id' => $validated['rencana_pembelajaran_id'],
                'tanggal' => $validated['tanggal'],
                'nilai' => null,
                'tahun_ajaran_id' => $tahunAjaranAktif->id,
                'semester_id' => $semesterAktif->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        DB::table('nilai_harian')->insert($rows);

        return redirect()->route('nilai.index')->with('success', 'Nilai harian berhasil dibuat untuk ' . count($rows) . ' siswa.');
    }

    public function updateBatch(Request $request)
    {
        $validated = $request->validate([
            'nilai' => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $now = now();
        $updated = 0;
        foreach ($validated['nilai'] as $id => $value) {
            $affected = DB::table('nilai_harian')
                ->where('id', $id)
                ->update([
                    'nilai' => $value === '' ? null : $value,
                    'updated_at' => $now,
                ]);
            $updated += $affected;
        }

        return redirect()->route('nilai.index')
            ->with('success', 'Nilai berhasil diperbarui (' . $updated . ' siswa).');
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'rencana_pembelajaran_id' => 'required|exists:rencana_pembelajarans,id',
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        if (!$guru) {
            return redirect()->route('nilai.index')->with('error', 'Akun guru tidak ditemukan.');
        }

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();

        if (!$tahunAjaranAktif || !$semesterAktif) {
            return redirect()->route('nilai.index')->with('error', 'Tahun ajaran atau semester aktif belum diatur.');
        }

        $import = new NilaiHarianImport(
            (int) $validated['kelas_id'],
            (int) $validated['mapel_id'],
            (int) $validated['rencana_pembelajaran_id'],
            $validated['tanggal'],
            (int) $guru->id,
            (int) $tahunAjaranAktif->id,
            (int) $semesterAktif->id
        );

        Excel::import($import, $validated['file']);

        $errors = $import->getErrors();
        if (count($errors) > 0) {
            return redirect()->route('nilai.index')
                ->with('warning', 'Import selesai dengan beberapa error.')
                ->with('import_errors', $errors);
        }

        return redirect()->route('nilai.index')->with('success', 'Import nilai berhasil.');
    }

    public function template()
    {
        $validated = request()->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        $kelasName = Kelas::where('id', $validated['kelas_id'])->value('nama_kelas') ?? 'kelas';
        $safeName = str_replace([' ', '/'], '_', $kelasName);

        return Excel::download(
            new NilaiHarianTemplateExport((int) $validated['kelas_id']),
            'template_import_nilai_harian_' . $safeName . '.xlsx'
        );
    }
}
