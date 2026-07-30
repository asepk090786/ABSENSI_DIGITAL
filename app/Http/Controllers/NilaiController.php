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
        $selectedTanggalNilai = request()->get('tanggal_nilai');
        if (empty($selectedTanggalNilai)) {
            $selectedTanggalNilai = now()->format('Y-m-d');
        }

        $quickMenus = collect();
        $rekapInputGuru = collect();
        $daftarInputNilaiGuru = collect();
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
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $isGuruBk = $user && $user->hasRole('Guru BK');
        $binaanKelasIds = collect();

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();

        if ($guru && !$isAdminOrKepala && !$isGuruBk) {
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

                    // Juga simpan di key '*' sebagai fallback untuk semua kelas
                    if (!isset($rencanaByMapel['*'])) {
                        $rencanaByMapel['*'] = [];
                    }
                    if (!isset($rencanaByMapel['*'][$mapelKey])) {
                        $rencanaByMapel['*'][$mapelKey] = [];
                    }
                    $sudahAdaGlobal = collect($rencanaByMapel['*'][$mapelKey])
                        ->contains(function ($row) use ($item) {
                            return mb_strtolower(trim((string) $row['judul'])) === mb_strtolower(trim((string) $item->judul));
                        });
                    if (!$sudahAdaGlobal) {
                        $rencanaByMapel['*'][$mapelKey][] = [
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

        } elseif ($isGuruBk && $guru) {
            $binaanKelasIds = Kelas::where('guru_bk_id', $guru->id)->pluck('id');

            $nilaiMenuQuery = DB::table('nilai_harian as nh')
                ->join('kelas as k', 'nh.kelas_id', '=', 'k.id')
                ->join('mata_pelajaran as mp', 'nh.mapel_id', '=', 'mp.id')
                ->whereIn('nh.kelas_id', $binaanKelasIds)
                ->when($tahunAjaranAktif, function ($query) use ($tahunAjaranAktif) {
                    $query->where('nh.tahun_ajaran_id', $tahunAjaranAktif->id);
                })
                ->when($semesterAktif, function ($query) use ($semesterAktif) {
                    $query->where('nh.semester_id', $semesterAktif->id);
                })
                ->select('nh.kelas_id', 'nh.mapel_id', 'k.nama_kelas', 'mp.nama_mapel')
                ->distinct();

            $nilaiMenuRows = $nilaiMenuQuery->get();

            $quickMenus = $nilaiMenuRows->map(function ($row) {
                return (object) [
                    'kelas_id' => $row->kelas_id,
                    'mata_pelajaran_id' => $row->mapel_id,
                    'kelas' => (object) ['id' => $row->kelas_id, 'nama_kelas' => $row->nama_kelas],
                    'mataPelajaran' => (object) ['id' => $row->mapel_id, 'nama_mapel' => $row->nama_mapel],
                ];
            })->values();

            $kelasOptions = $nilaiMenuRows->unique('kelas_id')->map(function ($row) {
                return (object) ['id' => $row->kelas_id, 'nama_kelas' => $row->nama_kelas];
            })->values();

            $mapelOptions = $nilaiMenuRows->unique('mapel_id')->map(function ($row) {
                return (object) ['id' => $row->mapel_id, 'nama_mapel' => $row->nama_mapel];
            })->values();

            $mapelByKelas = $nilaiMenuRows->groupBy('kelas_id')->map(function ($group) {
                return $group->map(function ($row) {
                    return ['id' => $row->mapel_id, 'nama' => $row->nama_mapel];
                })->unique('id')->values();
            });

        } elseif ($isAdminOrKepala) {
            $jadwalAdminQuery = JadwalKbm::with(['kelas', 'mataPelajaran'])
                ->whereNotNull('mata_pelajaran_id')
                ->whereNotNull('kelas_id');

            if ($tahunAjaranAktif) {
                $jadwalAdminQuery->where('tahun_ajaran_id', $tahunAjaranAktif->id);
            }
            if ($semesterAktif) {
                $jadwalAdminQuery->where('semester_id', $semesterAktif->id);
            }

            $jadwalAdmin = $jadwalAdminQuery->get();

            $quickMenus = $jadwalAdmin
                ->unique(function ($item) {
                    return $item->kelas_id . '-' . $item->mata_pelajaran_id;
                })
                ->values();

            $kelasOptions = $jadwalAdmin->pluck('kelas')->filter()->unique('id')->sortBy('nama_kelas')->values();
            $mapelOptions = $jadwalAdmin->pluck('mataPelajaran')->filter()->unique('id')->sortBy('nama_mapel')->values();

            $mapelByKelas = $jadwalAdmin->groupBy('kelas_id')->map(function ($group) {
                return $group->pluck('mataPelajaran')
                    ->filter()
                    ->unique('id')
                    ->map(function ($mapel) {
                        return [
                            'id' => $mapel->id,
                            'nama' => $mapel->nama_mapel,
                        ];
                    })
                    ->sortBy('nama')
                    ->values();
            });

            $rekapInputGuruQuery = DB::table('nilai_harian as nh')
                ->leftJoin('guru as g', 'nh.guru_id', '=', 'g.id')
                ->select(
                    'nh.guru_id',
                    DB::raw("COALESCE(g.nama, '-') as guru_nama"),
                    DB::raw('COUNT(nh.id) as total_record'),
                    DB::raw('SUM(CASE WHEN nh.nilai IS NOT NULL THEN 1 ELSE 0 END) as total_terisi'),
                    DB::raw('AVG(nh.nilai) as rata_nilai'),
                    DB::raw('COUNT(DISTINCT nh.kelas_id) as total_kelas'),
                    DB::raw('COUNT(DISTINCT nh.mapel_id) as total_mapel')
                )
                ->whereDate('nh.tanggal', $selectedTanggalNilai)
                ->groupBy('nh.guru_id', 'g.nama')
                ->orderBy('g.nama');

            if ($tahunAjaranAktif) {
                $rekapInputGuruQuery->where('nh.tahun_ajaran_id', $tahunAjaranAktif->id);
            }
            if ($semesterAktif) {
                $rekapInputGuruQuery->where('nh.semester_id', $semesterAktif->id);
            }

            $rekapInputGuru = $rekapInputGuruQuery->get();

            $daftarInputNilaiGuruQuery = DB::table('nilai_harian as nh')
                ->leftJoin('guru as g', 'nh.guru_id', '=', 'g.id')
                ->leftJoin('siswa as s', 'nh.siswa_id', '=', 's.id')
                ->leftJoin('kelas as k', 'nh.kelas_id', '=', 'k.id')
                ->leftJoin('mata_pelajaran as mp', 'nh.mapel_id', '=', 'mp.id')
                ->leftJoin('komponen_nilai as kn', 'nh.komponen_id', '=', 'kn.id')
                ->select(
                    'nh.id',
                    'nh.tanggal',
                    'nh.nilai',
                    'nh.created_at',
                    DB::raw("COALESCE(g.nama, '-') as guru_nama"),
                    DB::raw("COALESCE(s.nama, '-') as nama_siswa"),
                    DB::raw("COALESCE(k.nama_kelas, '-') as nama_kelas"),
                    DB::raw("COALESCE(mp.nama_mapel, '-') as nama_mapel"),
                    'kn.nama_komponen'
                )
                ->whereDate('nh.tanggal', $selectedTanggalNilai)
                ->whereNotNull('nh.nilai')
                ->orderByDesc('nh.updated_at')
                ->orderByDesc('nh.id');

            if ($tahunAjaranAktif) {
                $daftarInputNilaiGuruQuery->where('nh.tahun_ajaran_id', $tahunAjaranAktif->id);
            }
            if ($semesterAktif) {
                $daftarInputNilaiGuruQuery->where('nh.semester_id', $semesterAktif->id);
            }
            if ($kelasId) {
                $daftarInputNilaiGuruQuery->where('nh.kelas_id', $kelasId);
            }
            if ($mapelId) {
                $daftarInputNilaiGuruQuery->where('nh.mapel_id', $mapelId);
            }

            $daftarInputNilaiGuru = $daftarInputNilaiGuruQuery
                ->limit(300)
                ->get();
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
        if ($tahunAjaranAktif) {
            $itemsQuery->where('nilai_harian.tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $itemsQuery->where('nilai_harian.semester_id', $semesterAktif->id);
        }

        if ($isGuruBk && $binaanKelasIds->isNotEmpty()) {
            $itemsQuery->whereIn('nilai_harian.kelas_id', $binaanKelasIds);
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
            'semesterAktif',
            'isAdminOrKepala',
            'rekapInputGuru',
            'daftarInputNilaiGuru',
            'selectedTanggalNilai'
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
