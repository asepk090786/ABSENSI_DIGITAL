<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\JadwalKbm;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\RencanaPembelajaran;
use App\Models\Siswa;
use App\Exports\NilaiInputGridTemplateExport;
use App\Exports\NilaiAkhirAsesmenExport;
use App\Imports\NilaiInputGridImport;
use App\Imports\NilaiHarianImport;
use App\Support\NilaiInputNormalizer;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\NilaiHarianTemplateExport;

class NilaiController extends Controller
{
    public function index()
    {
        $kelasId = request()->get('kelas_id');
        $mapelId = request()->get('mapel_id');
        $selectedAssessmentUuid = request()->query('assessment_uuid');
        $isEditMode = request()->boolean('edit');
        $isFormativeSource = request()->input('source') === 'asesmen-formatif';
        $isSumatifSource = request()->input('source') === 'asesmen-sumatif';
        $backUrl = $isFormativeSource
            ? route('nilai.asesmen.formatif', array_filter([
                'kelas_id' => $kelasId,
                'mapel_id' => $mapelId,
            ]))
            : ($isSumatifSource ? route('nilai.asesmen.sumatif', array_filter([
                'kelas_id' => $kelasId,
                'mapel_id' => $mapelId,
            ])) : route('nilai.index', array_filter([
                'kelas_id' => $kelasId,
                'mapel_id' => $mapelId,
            ])));
        $hasTanggalFilter = request()->filled('tanggal_nilai');
        $selectedTanggalNilai = request()->get('tanggal_nilai');
        if (empty($selectedTanggalNilai)) {
            $selectedTanggalNilai = now()->format('Y-m-d');
        }
        $selectedRencanaId = request()->get('rencana_pembelajaran_id');
        $hasKomponenFilter = request()->query->has('komponen_id');
        $selectedKomponenId = request()->query('komponen_id');

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

        $user = auth()->user();
        $komponenQuery = DB::table('komponen_nilai')
            ->leftJoin('capaian_pembelajarans as cp', 'komponen_nilai.capaian_pembelajaran_id', '=', 'cp.id')
            ->select('komponen_nilai.*')
            ->orderBy('komponen_nilai.nama_komponen');

        if ($user && ! $user->hasAnyRole(['Admin', 'Kepala Sekolah']) && ! empty($user->guru_id)) {
            $komponenQuery->where('komponen_nilai.guru_id', $user->guru_id);
        }

        $komponenList = $komponenQuery->get();
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

            if (!$isAdminOrKepala) {
                $daftarInputNilaiGuruQuery->where('nh.guru_id', $guru->id);
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

        if ($hasTanggalFilter && $selectedTanggalNilai) {
            $itemsQuery->whereDate('nilai_harian.tanggal', $selectedTanggalNilai);
        }

        if ($selectedRencanaId) {
            $itemsQuery->where('nilai_harian.rencana_pembelajaran_id', $selectedRencanaId);
        }

        if ($selectedAssessmentUuid) {
            $itemsQuery->where('nilai_harian.assessment_uuid', $selectedAssessmentUuid);
        }

        if ($hasKomponenFilter) {
            if ((string) $selectedKomponenId === '0') {
                $itemsQuery->whereNull('nilai_harian.komponen_id');
            } else {
                $itemsQuery->where('nilai_harian.komponen_id', $selectedKomponenId);
            }
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

        if ($guru && !$isAdminOrKepala && !$isGuruBk) {
            $itemsQuery->where('nilai_harian.guru_id', $guru->id);
        }
        $items = $itemsQuery->orderBy('nilai_harian.created_at','desc')->get();
        $scoreMenus = $items
            ->filter(function ($item) {
                return $item->kelas_id && $item->mapel_id;
            })
            ->unique(function ($item) {
                return $item->kelas_id . '-' . $item->mapel_id;
            })
            ->map(function ($item) {
                return (object) [
                    'kelas_id' => $item->kelas_id,
                    'mata_pelajaran_id' => $item->mapel_id,
                    'kelas' => (object) ['nama_kelas' => $item->nama_kelas],
                    'mataPelajaran' => (object) ['nama_mapel' => $item->nama_mapel],
                ];
            });
        $quickMenus = $quickMenus
            ->concat($scoreMenus)
            ->unique(function ($item) {
                return $item->kelas_id . '-' . ($item->mata_pelajaran_id ?? $item->mapel_id);
            })
            ->values();
        $assessmentPairs = $quickMenus->map(function ($item) {
            return [
                'kelas_id' => $item->kelas_id ?? null,
                'mapel_id' => $item->mata_pelajaran_id ?? $item->mapel_id ?? null,
            ];
        })->filter(function ($pair) {
            return $pair['kelas_id'] && $pair['mapel_id'];
        });
        if ($kelasId && $mapelId) {
            $assessmentPairs->push(['kelas_id' => $kelasId, 'mapel_id' => $mapelId]);
        }
        $nilaiAkhirByKelasMapel = [];
        $teacherScoreScopeId = $guru && ! $isAdminOrKepala && ! $isGuruBk ? (int) $guru->id : null;
        foreach ($assessmentPairs->unique(function ($pair) {
            return $pair['kelas_id'] . '-' . $pair['mapel_id'];
        }) as $pair) {
            $pairKey = $pair['kelas_id'] . '-' . $pair['mapel_id'];
            $nilaiAkhirByKelasMapel[$pairKey] = $this->nilaiAkhirAsesmenData(
                (int) $pair['kelas_id'],
                (int) $pair['mapel_id'],
                $tahunAjaranAktif?->id,
                $semesterAktif?->id,
                $teacherScoreScopeId
            );
        }

        $penilaianList = collect();
        if ($kelasId || $mapelId) {
            $penilaianQuery = DB::table('nilai_harian as nh')
                ->leftJoin('komponen_nilai as kn', 'nh.komponen_id', '=', 'kn.id')
                ->leftJoin('rencana_pembelajarans as rp', 'nh.rencana_pembelajaran_id', '=', 'rp.id')
                ->select(
                    'nh.tanggal',
                    'nh.rencana_pembelajaran_id',
                    'nh.komponen_id',
                    'nh.assessment_uuid',
                    'nh.materi_pelajaran',
                    DB::raw("COALESCE(NULLIF(nh.materi_pelajaran, ''), kn.nama_komponen, 'Harian') as nama_penilaian"),
                    DB::raw("CASE WHEN nh.komponen_id IS NULL THEN 'Harian' ELSE 'Komponen Penilaian' END as jenis_penilaian"),
                    'kn.bobot as persentase',
                    'rp.judul as rencana_judul',
                    DB::raw('COUNT(DISTINCT nh.siswa_id) as jumlah_siswa'),
                    DB::raw('SUM(CASE WHEN nh.nilai IS NOT NULL THEN 1 ELSE 0 END) as jumlah_terisi')
                )
                ->groupBy(
                    'nh.tanggal',
                    'nh.rencana_pembelajaran_id',
                    'nh.komponen_id',
                    'nh.assessment_uuid',
                    'nh.materi_pelajaran',
                    'kn.nama_komponen',
                    'kn.bobot',
                    'rp.judul'
                )
                ->orderByDesc('nh.tanggal')
                ->orderBy('nama_penilaian');

            if ($selectedRencanaId) {
                $penilaianQuery->where('nh.rencana_pembelajaran_id', $selectedRencanaId);
            }
            if ($selectedAssessmentUuid) {
                $penilaianQuery->where('nh.assessment_uuid', $selectedAssessmentUuid);
            }
            if ($hasKomponenFilter) {
                if ((string) $selectedKomponenId === '0') {
                    $penilaianQuery->whereNull('nh.komponen_id');
                } else {
                    $penilaianQuery->where('nh.komponen_id', $selectedKomponenId);
                }
            }

            if ($tahunAjaranAktif) {
                $penilaianQuery->where('nh.tahun_ajaran_id', $tahunAjaranAktif->id);
            }
            if ($semesterAktif) {
                $penilaianQuery->where('nh.semester_id', $semesterAktif->id);
            }
            if ($kelasId) {
                $penilaianQuery->where('nh.kelas_id', $kelasId);
            }
            if ($mapelId) {
                $penilaianQuery->where('nh.mapel_id', $mapelId);
            }
            if ($guru && !$isAdminOrKepala && !$isGuruBk) {
                $penilaianQuery->where('nh.guru_id', $guru->id);
            }

            $penilaianList = $penilaianQuery->get();
        }

        $isViewMode = request()->boolean('view');
        $showInputForm = $hasTanggalFilter && !$isViewMode && ($kelasId || $mapelId);
        $showBackNavigation = $isEditMode || $isFormativeSource || $showInputForm || $isViewMode;
        $showBackNavigation = $showBackNavigation || $isSumatifSource;

        $assessmentGroups = $items
            ->groupBy(function ($item) {
                return $item->assessment_uuid ?: 'legacy:' . (int) ($item->komponen_id ?? 0);
            })
            ->sortBy(function ($rows) {
                return (int) $rows->min('id');
            });
        $assessmentCounts = [];
        $nilaiKomponenColumns = $assessmentGroups
            ->map(function ($rows) use (&$assessmentCounts) {
                $item = $rows->first();
                $baseName = $item->komponen_id === null
                    ? 'Nilai Harian'
                    : 'Nilai ' . ($item->nama_komponen ?: 'Komponen');
                $assessmentCounts[$baseName] = ($assessmentCounts[$baseName] ?? 0) + 1;

                return (object) [
                    'key' => $item->assessment_uuid ?: 'legacy:' . (int) ($item->komponen_id ?? 0),
                    'assessment_uuid' => $item->assessment_uuid,
                    'id' => (int) ($item->komponen_id ?? 0),
                    'nama' => $baseName . ' ' . $assessmentCounts[$baseName],
                ];
            })
            ->values();
        $nilaiTableRows = collect();

        if ($items->isNotEmpty()) {
            $nilaiTableRows = $items
                ->groupBy('siswa_id')
                ->map(function ($rows) use ($nilaiKomponenColumns) {
                    $first = $rows->first();
                    $nilaiByKomponen = [];
                    $nilaiIdByKomponen = [];
                    $nilaiValues = [];

                    foreach ($nilaiKomponenColumns as $komponen) {
                        $match = $rows->first(function ($item) use ($komponen) {
                            if ($komponen->assessment_uuid) {
                                return $item->assessment_uuid === $komponen->assessment_uuid;
                            }

                            return empty($item->assessment_uuid)
                                && (int) ($item->komponen_id ?? 0) === $komponen->id;
                        });

                        if ($match) {
                            $nilaiByKomponen[$komponen->key] = $match->nilai;
                            $nilaiIdByKomponen[$komponen->key] = $match->id;
                            if ($match->nilai !== null && $match->nilai !== '') {
                                $nilaiValues[] = (float) $match->nilai;
                            }
                        } else {
                            $nilaiByKomponen[$komponen->key] = null;
                            $nilaiIdByKomponen[$komponen->key] = null;
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
            'selectedTanggalNilai',
            'nilaiAkhirByKelasMapel',
            'penilaianList',
            'showInputForm',
            'isViewMode',
            'selectedAssessmentUuid',
            'isEditMode',
            'isFormativeSource',
            'isSumatifSource',
            'showBackNavigation',
            'backUrl'
        ));
    }

        public function simpanBobotNilaiAkhirAsesmen(Request $request)
        {
            $validated = $request->validate([
                'kelas_id' => 'required|exists:kelas,id',
                'mapel_id' => 'required|exists:mata_pelajaran,id',
                'bobot_formatif' => 'required|numeric|min:0|max:100',
                'bobot_sumatif' => 'required|numeric|min:0|max:100',
            ]);

            if (abs(((float) $validated['bobot_formatif'] + (float) $validated['bobot_sumatif']) - 100) > 0.001) {
                return back()->withErrors(['bobot_formatif' => 'Total bobot formatif dan sumatif harus tepat 100%.'])->withInput();
            }

            $user = auth()->user();
            $guru = $user ? $user->guru : null;
            $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);
            $isGuruBk = $user && $user->hasRole('Guru BK');
            $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
            $semesterAktif = Semester::where('is_active', true)->first();
            abort_unless($tahunAjaranAktif && $semesterAktif, 422, 'Tahun ajaran dan semester aktif harus tersedia.');

            $this->authorizeAssessmentClass(
                (int) $validated['kelas_id'],
                $guru,
                (bool) $isAdminOrKepala,
                (bool) $isGuruBk,
                (int) $tahunAjaranAktif->id,
                (int) $semesterAktif->id
            );

            if ($guru && ! $isAdminOrKepala && ! $isGuruBk) {
                $hasTeachingSchedule = JadwalKbm::where('guru_id', $guru->id)
                    ->where('kelas_id', $validated['kelas_id'])
                    ->where('mata_pelajaran_id', $validated['mapel_id'])
                    ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                    ->where('semester_id', $semesterAktif->id)
                    ->exists();
                abort_unless($hasTeachingSchedule, 403);
            }

            $scope = [
                'kelas_id' => $validated['kelas_id'],
                'mapel_id' => $validated['mapel_id'],
                'tahun_ajaran_id' => $tahunAjaranAktif->id,
                'semester_id' => $semesterAktif->id,
            ];
            DB::table('nilai_akhir_asesmen_bobot')->updateOrInsert($scope, [
                'bobot_formatif' => $validated['bobot_formatif'],
                'bobot_sumatif' => $validated['bobot_sumatif'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return redirect()->route('nilai.index', [
                'kelas_id' => $validated['kelas_id'],
                'mapel_id' => $validated['mapel_id'],
            ])->with('success', 'Persentase nilai akhir berhasil disimpan.');
        }

        public function rekapNilaiAkhirAsesmen(Request $request)
        {
            $validated = $request->validate([
                'kelas_id' => 'required|exists:kelas,id',
                'mapel_id' => 'required|exists:mata_pelajaran,id',
            ]);

            return view('nilai.rekap_nilai_akhir_asesmen', array_merge(
                $this->nilaiAkhirAsesmenReportData($validated),
                ['previewMode' => false, 'exportFormat' => null]
            ));
        }

        public function previewNilaiAkhirAsesmenExport(Request $request)
        {
            $validated = $request->validate([
                'kelas_id' => 'required|exists:kelas,id',
                'mapel_id' => 'required|exists:mata_pelajaran,id',
                'format' => 'required|in:excel,pdf',
            ]);

            return view('nilai.rekap_nilai_akhir_asesmen', array_merge(
                $this->nilaiAkhirAsesmenReportData($validated),
                ['previewMode' => true, 'exportFormat' => $validated['format']]
            ));
        }

        public function exportNilaiAkhirAsesmen(Request $request)
        {
            $validated = $request->validate([
                'kelas_id' => 'required|exists:kelas,id',
                'mapel_id' => 'required|exists:mata_pelajaran,id',
                'format' => 'required|in:excel,pdf',
            ]);
            $report = $this->nilaiAkhirAsesmenReportData($validated);
            $filenameBase = 'nilai-akhir-' . $report['kelas']->id . '-' . $report['mapel']->id;

            if ($validated['format'] === 'excel') {
                return Excel::download(
                    new NilaiAkhirAsesmenExport($report['data']['students']),
                    $filenameBase . '.xlsx'
                );
            }

            return Pdf::loadView('nilai.rekap_nilai_akhir_asesmen_pdf', $report)
                ->setPaper('a4', 'landscape')
                ->download($filenameBase . '.pdf');
        }

        private function nilaiAkhirAsesmenReportData(array $validated): array
        {
            $user = auth()->user();
            $guru = $user ? $user->guru : null;
            $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);
            $isGuruBk = $user && $user->hasRole('Guru BK');
            $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
            $semesterAktif = Semester::where('is_active', true)->first();

            $this->authorizeAssessmentClass(
                (int) $validated['kelas_id'],
                $guru,
                (bool) $isAdminOrKepala,
                (bool) $isGuruBk,
                $tahunAjaranAktif?->id,
                $semesterAktif?->id
            );

            if ($guru && ! $isAdminOrKepala && ! $isGuruBk) {
                $hasTeachingSchedule = JadwalKbm::where('guru_id', $guru->id)
                    ->where('kelas_id', $validated['kelas_id'])
                    ->where('mata_pelajaran_id', $validated['mapel_id'])
                    ->when($tahunAjaranAktif, fn ($query) => $query->where('tahun_ajaran_id', $tahunAjaranAktif->id))
                    ->when($semesterAktif, fn ($query) => $query->where('semester_id', $semesterAktif->id))
                    ->exists();
                abort_unless($hasTeachingSchedule, 403);
            }

            return [
                'kelas' => Kelas::findOrFail($validated['kelas_id']),
                'mapel' => MataPelajaran::findOrFail($validated['mapel_id']),
                'tahunAjaranAktif' => $tahunAjaranAktif,
                'semesterAktif' => $semesterAktif,
                'data' => $this->nilaiAkhirAsesmenData(
                    (int) $validated['kelas_id'],
                    (int) $validated['mapel_id'],
                    $tahunAjaranAktif?->id,
                    $semesterAktif?->id,
                    $guru && ! $isAdminOrKepala && ! $isGuruBk ? (int) $guru->id : null
                ),
            ];
        }

        private function nilaiAkhirAsesmenData(int $kelasId, int $mapelId, ?int $tahunAjaranId, ?int $semesterId, ?int $guruId): array
        {
            $weightQuery = DB::table('nilai_akhir_asesmen_bobot')
                ->where('kelas_id', $kelasId)
                ->where('mapel_id', $mapelId)
                ->when($tahunAjaranId, fn ($query) => $query->where('tahun_ajaran_id', $tahunAjaranId))
                ->when($semesterId, fn ($query) => $query->where('semester_id', $semesterId));
            $weights = $weightQuery->first();
            $bobotFormatif = (float) ($weights->bobot_formatif ?? 50);
            $bobotSumatif = (float) ($weights->bobot_sumatif ?? 50);

            $assessmentRecords = DB::table('nilai_harian as nh')
                ->leftJoin('mata_pelajaran as mp', 'nh.mapel_id', '=', 'mp.id')
                ->where('nh.kelas_id', $kelasId)
                ->where('nh.mapel_id', $mapelId)
                ->whereIn('nh.kategori_asesmen', ['formatif', 'sumatif'])
                ->when($tahunAjaranId, fn ($query) => $query->where('nh.tahun_ajaran_id', $tahunAjaranId))
                ->when($semesterId, fn ($query) => $query->where('nh.semester_id', $semesterId))
                ->when($guruId, fn ($query) => $query->where('nh.guru_id', $guruId))
                ->select(
                    'nh.kategori_asesmen',
                    'nh.assessment_uuid',
                    'nh.tanggal',
                    'nh.komponen_id',
                    'nh.rencana_pembelajaran_id',
                    'nh.materi_pelajaran',
                    'nh.jenis_penilaian',
                    'mp.nama_mapel'
                )
                ->get();
            $assessmentCounts = ['formatif' => [], 'sumatif' => []];
            foreach ($assessmentRecords as $assessment) {
                $assessmentCounts[$assessment->kategori_asesmen][$this->assessmentGroupKey($assessment)] = true;
            }

            $scoreQuery = DB::table('nilai_harian')
                ->where('kelas_id', $kelasId)
                ->where('mapel_id', $mapelId)
                ->whereIn('kategori_asesmen', ['formatif', 'sumatif'])
                ->whereNotNull('nilai')
                ->when($tahunAjaranId, fn ($query) => $query->where('tahun_ajaran_id', $tahunAjaranId))
                ->when($semesterId, fn ($query) => $query->where('semester_id', $semesterId))
                ->when($guruId, fn ($query) => $query->where('guru_id', $guruId))
                ->select('siswa_id', 'kategori_asesmen', DB::raw('SUM(nilai) as total_nilai'))
                ->groupBy('siswa_id', 'kategori_asesmen')
                ->get();

            $scoresByStudent = [];
            foreach ($scoreQuery as $score) {
                $assessmentCount = count($assessmentCounts[$score->kategori_asesmen] ?? []);
                if ($assessmentCount > 0) {
                    $scoresByStudent[$score->siswa_id][$score->kategori_asesmen] = (float) $score->total_nilai / $assessmentCount;
                }
            }

            $students = Siswa::where('kelas_id', $kelasId)
                ->orderBy('nama')
                ->get(['id', 'nama', 'nis', 'nisn'])
                ->map(function ($student) use ($scoresByStudent, $bobotFormatif, $bobotSumatif) {
                    $studentScores = $scoresByStudent[$student->id] ?? [];
                    $formatif = $studentScores['formatif'] ?? null;
                    $sumatif = $studentScores['sumatif'] ?? null;
                    $student->rata_formatif = $formatif;
                    $student->rata_sumatif = $sumatif;
                    $hasRequiredScores = ($bobotFormatif == 0 || $formatif !== null)
                        && ($bobotSumatif == 0 || $sumatif !== null);
                    $student->nilai_akhir = $hasRequiredScores
                        ? round((($formatif ?? 0) * $bobotFormatif + ($sumatif ?? 0) * $bobotSumatif) / 100, 2)
                        : null;

                    return $student;
                });

            $formatifValues = $students->pluck('rata_formatif')->filter(fn ($value) => $value !== null);
            $sumatifValues = $students->pluck('rata_sumatif')->filter(fn ($value) => $value !== null);
            $finalValues = $students->pluck('nilai_akhir')->filter(fn ($value) => $value !== null);

            return [
                'bobot_formatif' => $bobotFormatif,
                'bobot_sumatif' => $bobotSumatif,
                'rata_formatif' => $formatifValues->isNotEmpty() ? round($formatifValues->avg(), 2) : null,
                'rata_sumatif' => $sumatifValues->isNotEmpty() ? round($sumatifValues->avg(), 2) : null,
                'nilai_akhir' => $finalValues->isNotEmpty() ? round($finalValues->avg(), 2) : null,
                'students' => $students,
            ];
        }

        public function asesmenFormatif()
    {
        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        $selectedKelasId = request()->get('kelas_id');
        $selectedMapelId = request()->get('mapel_id');

        $jadwalQuery = JadwalKbm::with(['kelas', 'mataPelajaran'])
            ->whereNotNull('kelas_id')
            ->whereNotNull('mata_pelajaran_id');

        if ($selectedKelasId) {
            $jadwalQuery->where('kelas_id', $selectedKelasId);
        }

        if ($selectedMapelId) {
            $jadwalQuery->where('mata_pelajaran_id', $selectedMapelId);
        }

        if ($guru && ! $user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            $jadwalQuery->where('guru_id', $guru->id);
        }

        if ($tahunAjaranAktif) {
            $jadwalQuery->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }

        if ($semesterAktif) {
            $jadwalQuery->where('semester_id', $semesterAktif->id);
        }

        $jadwalList = $jadwalQuery->orderBy('kelas_id')->get();

        $kelasOptions = $jadwalList->pluck('kelas')->filter()->unique('id')->values();
        $mapelOptions = $jadwalList->pluck('mataPelajaran')->filter()->unique('id')->values();

        $rencanaByMapel = [];
        $rencanaQuery = RencanaPembelajaran::query();

        if ($guru && ! $user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            $rencanaQuery->where('guru_id', $guru->id);
        }

        if ($selectedKelasId) {
            $rencanaQuery->where('kelas_id', $selectedKelasId);
        }

        if ($selectedMapelId) {
            $rencanaQuery->where('mata_pelajaran_id', $selectedMapelId);
        }

        foreach ($rencanaQuery->orderBy('created_at', 'desc')->get() as $rencana) {
            $kelasKey = (string) ($rencana->kelas_id ?? 'all');
            $mapelKey = (string) ($rencana->mata_pelajaran_id ?? 'all');

            if (! isset($rencanaByMapel[$kelasKey])) {
                $rencanaByMapel[$kelasKey] = [];
            }

            if (! isset($rencanaByMapel[$kelasKey][$mapelKey])) {
                $rencanaByMapel[$kelasKey][$mapelKey] = [];
            }

            $rencanaByMapel[$kelasKey][$mapelKey][] = [
                'id' => $rencana->id,
                'judul' => $rencana->judul,
            ];
        }

        $daftarTugasQuery = DB::table('nilai_harian as nh')
            ->leftJoin('kelas as k', 'nh.kelas_id', '=', 'k.id')
            ->leftJoin('mata_pelajaran as mp', 'nh.mapel_id', '=', 'mp.id')
            ->leftJoin('komponen_nilai as kn', 'nh.komponen_id', '=', 'kn.id')
            ->select(
                'nh.kelas_id',
                'nh.mapel_id',
                'nh.tanggal',
                'nh.komponen_id',
                'nh.rencana_pembelajaran_id',
                'nh.assessment_uuid',
                'nh.materi_pelajaran',
                'k.nama_kelas as kelas',
                'mp.nama_mapel as mapel',
                DB::raw("COALESCE(NULLIF(nh.materi_pelajaran, ''), kn.nama_komponen, 'Harian / Umum') as judul"),
                DB::raw('AVG(CASE WHEN nh.nilai IS NOT NULL THEN nh.nilai END) as nilai_rata'),
                DB::raw('COUNT(CASE WHEN nh.nilai IS NOT NULL THEN 1 END) as siswa_terisi'),
                DB::raw('COUNT(DISTINCT nh.siswa_id) as total_siswa')
            )
            ->whereNotNull('nh.kelas_id')
            ->whereNotNull('nh.mapel_id');

        if ($selectedKelasId) {
            $daftarTugasQuery->where('nh.kelas_id', $selectedKelasId);
        }

        if ($selectedMapelId) {
            $daftarTugasQuery->where('nh.mapel_id', $selectedMapelId);
        }

        if ($guru && ! $user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            $daftarTugasQuery->where('nh.guru_id', $guru->id);
        }

        if ($tahunAjaranAktif) {
            $daftarTugasQuery->where('nh.tahun_ajaran_id', $tahunAjaranAktif->id);
        }

        if ($semesterAktif) {
            $daftarTugasQuery->where('nh.semester_id', $semesterAktif->id);
        }

        $daftarTugas = $daftarTugasQuery
            ->groupBy(
                'nh.kelas_id',
                'nh.mapel_id',
                'nh.tanggal',
                'nh.komponen_id',
                'nh.rencana_pembelajaran_id',
                'nh.assessment_uuid',
                'nh.materi_pelajaran',
                'k.nama_kelas',
                'mp.nama_mapel',
                'kn.nama_komponen'
            )
            ->orderByDesc('nh.tanggal')
            ->orderBy('k.nama_kelas')
            ->orderBy('mp.nama_mapel')
            ->get()
            ->map(function ($row, $index) {
                $nilaiRata = $row->nilai_rata !== null ? (float) $row->nilai_rata : null;

                $tanggalRaw = $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') : null;
                $tanggalDisplay = $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-';

                return (object) [
                    'no' => $index + 1,
                    'kelas_id' => $row->kelas_id,
                    'mapel_id' => $row->mapel_id,
                    'kelas' => $row->kelas ?? 'Kelas',
                    'mapel' => $row->mapel ?? 'Mapel',
                    'judul' => $row->judul ?? 'Tugas / Latihan',
                    'tanggal' => $tanggalRaw,
                    'tanggal_display' => $tanggalDisplay,
                    'assessment_uuid' => $row->assessment_uuid,
                    'materi_pelajaran' => $row->materi_pelajaran,
                    'nilai' => $nilaiRata !== null ? number_format($nilaiRata, 2) : '-',
                    'keterangan' => ($row->siswa_terisi ?? 0) . '/' . ($row->total_siswa ?? 0) . ' siswa terisi',
                    'komponen_id' => $row->komponen_id,
                    'rencana_pembelajaran_id' => $row->rencana_pembelajaran_id,
                ];
            })
            ->values();

        return view('nilai.asesmen_formatif', compact(
            'daftarTugas',
            'guru',
            'tahunAjaranAktif',
            'semesterAktif',
            'selectedKelasId',
            'selectedMapelId',
            'kelasOptions',
            'mapelOptions',
            'rencanaByMapel'
        ));
    }

    public function asesmenSumatif()
    {
        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        $selectedKelasId = request()->get('kelas_id');
        $selectedMapelId = request()->get('mapel_id');

        $jadwalQuery = JadwalKbm::with(['kelas', 'mataPelajaran'])
            ->whereNotNull('kelas_id')
            ->whereNotNull('mata_pelajaran_id');

        if ($selectedKelasId) {
            $jadwalQuery->where('kelas_id', $selectedKelasId);
        }
        if ($selectedMapelId) {
            $jadwalQuery->where('mata_pelajaran_id', $selectedMapelId);
        }
        if ($guru && ! $isAdminOrKepala) {
            $jadwalQuery->where('guru_id', $guru->id);
        }
        if ($tahunAjaranAktif) {
            $jadwalQuery->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $jadwalQuery->where('semester_id', $semesterAktif->id);
        }

        $jadwalList = $jadwalQuery->orderBy('kelas_id')->get();
        $kelasOptions = $jadwalList->pluck('kelas')->filter()->unique('id')->values();
        $mapelOptions = $jadwalList->pluck('mataPelajaran')->filter()->unique('id')->values();

        $daftarSumatifQuery = DB::table('nilai_harian as nh')
            ->leftJoin('kelas as k', 'nh.kelas_id', '=', 'k.id')
            ->leftJoin('mata_pelajaran as mp', 'nh.mapel_id', '=', 'mp.id')
            ->leftJoin('komponen_nilai as kn', 'nh.komponen_id', '=', 'kn.id')
            ->where('nh.kategori_asesmen', 'sumatif')
            ->whereNotNull('nh.kelas_id')
            ->whereNotNull('nh.mapel_id')
            ->select(
                'nh.kelas_id',
                'nh.mapel_id',
                'nh.tanggal',
                'nh.komponen_id',
                'nh.rencana_pembelajaran_id',
                'nh.assessment_uuid',
                'nh.materi_pelajaran',
                'nh.jenis_penilaian',
                'k.nama_kelas as kelas',
                'mp.nama_mapel as mapel',
                DB::raw("COALESCE(NULLIF(nh.materi_pelajaran, ''), NULLIF(nh.jenis_penilaian, ''), kn.nama_komponen, 'Asesmen Sumatif') as judul"),
                DB::raw('AVG(CASE WHEN nh.nilai IS NOT NULL THEN nh.nilai END) as nilai_rata'),
                DB::raw('COUNT(CASE WHEN nh.nilai IS NOT NULL THEN 1 END) as siswa_terisi'),
                DB::raw('COUNT(DISTINCT nh.siswa_id) as total_siswa')
            )
            ->groupBy(
                'nh.kelas_id',
                'nh.mapel_id',
                'nh.tanggal',
                'nh.komponen_id',
                'nh.rencana_pembelajaran_id',
                'nh.assessment_uuid',
                'nh.materi_pelajaran',
                'nh.jenis_penilaian',
                'k.nama_kelas',
                'mp.nama_mapel',
                'kn.nama_komponen'
            )
            ->orderByDesc('nh.tanggal');

        if ($selectedKelasId) {
            $daftarSumatifQuery->where('nh.kelas_id', $selectedKelasId);
        }
        if ($selectedMapelId) {
            $daftarSumatifQuery->where('nh.mapel_id', $selectedMapelId);
        }
        if ($guru && ! $isAdminOrKepala) {
            $daftarSumatifQuery->where('nh.guru_id', $guru->id);
        }
        if ($tahunAjaranAktif) {
            $daftarSumatifQuery->where('nh.tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $daftarSumatifQuery->where('nh.semester_id', $semesterAktif->id);
        }

        $daftarSumatif = $daftarSumatifQuery->get()->map(function ($row, $index) {
            return (object) [
                'no' => $index + 1,
                'kelas_id' => $row->kelas_id,
                'mapel_id' => $row->mapel_id,
                'kelas' => $row->kelas ?? 'Kelas',
                'mapel' => $row->mapel ?? 'Mapel',
                'judul' => $row->judul ?? 'Asesmen Sumatif',
                'jenis_penilaian' => $row->jenis_penilaian,
                'tanggal' => $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('Y-m-d') : null,
                'tanggal_display' => $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') : '-',
                'assessment_uuid' => $row->assessment_uuid,
                'nilai' => $row->nilai_rata !== null ? number_format((float) $row->nilai_rata, 2) : '-',
                'keterangan' => ($row->siswa_terisi ?? 0) . '/' . ($row->total_siswa ?? 0) . ' siswa terisi',
                'komponen_id' => $row->komponen_id,
                'rencana_pembelajaran_id' => $row->rencana_pembelajaran_id,
            ];
        });

        return view('nilai.asesmen_sumatif', compact(
            'daftarSumatif',
            'guru',
            'tahunAjaranAktif',
            'semesterAktif',
            'selectedKelasId',
            'selectedMapelId',
            'kelasOptions',
            'mapelOptions'
        ));
    }

    public function rekapAsesmenFormatif(Request $request)
    {
        return $this->rekapAsesmen($request, 'formatif');
    }

    public function rekapAsesmenSumatif(Request $request)
    {
        return $this->rekapAsesmen($request, 'sumatif');
    }

    private function assessmentGroupKey(object $record): string
    {
        if ($record->assessment_uuid) {
            return $record->assessment_uuid;
        }

        return 'legacy:' . sha1(implode('|', [
            $record->tanggal,
            $record->komponen_id,
            $record->rencana_pembelajaran_id,
            $record->materi_pelajaran,
            $record->jenis_penilaian,
            $record->nama_mapel,
        ]));
    }

    private function rekapAsesmen(Request $request, string $kategori)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'nullable|exists:mata_pelajaran,id',
        ]);

        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);
        $isGuruBk = $user && $user->hasRole('Guru BK');
        $kelasId = (int) $validated['kelas_id'];
        $mapelId = $validated['mapel_id'] ?? null;
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        $selectedKelas = Kelas::findOrFail($kelasId);
        $selectedMapel = $mapelId ? MataPelajaran::find($mapelId) : null;

        if ($isGuruBk && $guru && ! Kelas::where('id', $kelasId)->where('guru_bk_id', $guru->id)->exists()) {
            abort(403);
        }

        if ($guru && ! $isAdminOrKepala && ! $isGuruBk) {
            $jadwalQuery = JadwalKbm::where('guru_id', $guru->id)->where('kelas_id', $kelasId);
            if ($tahunAjaranAktif) {
                $jadwalQuery->where('tahun_ajaran_id', $tahunAjaranAktif->id);
            }
            if ($semesterAktif) {
                $jadwalQuery->where('semester_id', $semesterAktif->id);
            }
            abort_unless($jadwalQuery->exists(), 403);
        }

        $recordsQuery = DB::table('nilai_harian as nh')
            ->join('siswa as s', 'nh.siswa_id', '=', 's.id')
            ->leftJoin('mata_pelajaran as mp', 'nh.mapel_id', '=', 'mp.id')
            ->leftJoin('komponen_nilai as kn', 'nh.komponen_id', '=', 'kn.id')
            ->where('nh.kelas_id', $kelasId)
            ->when($mapelId, function ($query) use ($mapelId) {
                $query->where('nh.mapel_id', $mapelId);
            })
            ->when($tahunAjaranAktif, function ($query) use ($tahunAjaranAktif) {
                $query->where('nh.tahun_ajaran_id', $tahunAjaranAktif->id);
            })
            ->when($semesterAktif, function ($query) use ($semesterAktif) {
                $query->where('nh.semester_id', $semesterAktif->id);
            });

        if ($guru && ! $isAdminOrKepala && ! $isGuruBk) {
            $recordsQuery->where('nh.guru_id', $guru->id);
        }

        $uncategorizedRecordCount = (clone $recordsQuery)
            ->whereNull('nh.kategori_asesmen')
            ->count();

        $assessmentRecords = (clone $recordsQuery)
            ->where('nh.kategori_asesmen', $kategori)
            ->select(
                'nh.assessment_uuid',
                'nh.siswa_id',
                'nh.tanggal',
                'nh.nilai',
                'nh.materi_pelajaran',
                'nh.jenis_penilaian',
                'nh.komponen_id',
                'nh.rencana_pembelajaran_id',
                'mp.nama_mapel',
                'kn.nama_komponen'
            )
            ->orderBy('nh.tanggal')
            ->orderBy('nh.id')
            ->get();

        $assessmentGroups = $assessmentRecords
            ->groupBy(fn ($record) => $this->assessmentGroupKey($record))
            ->sortBy(function ($rows) {
                return $rows->first()->tanggal;
            });

        $assessmentColumns = $assessmentGroups->map(function ($rows, $key) {
            $record = $rows->first();

            return (object) [
                'key' => $key,
                'judul' => trim((string) ($record->materi_pelajaran ?: $record->jenis_penilaian ?: $record->nama_komponen ?: 'Penilaian')),
                'mapel' => $record->nama_mapel ?: 'Mata pelajaran',
                'tanggal' => $record->tanggal,
            ];
        })->values();

        $students = Siswa::where('kelas_id', $kelasId)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nis', 'nisn'])
            ->map(function ($student) use ($assessmentGroups, $assessmentColumns) {
                $scores = [];
                $values = [];

                foreach ($assessmentColumns as $column) {
                    $record = $assessmentGroups->get($column->key)?->firstWhere('siswa_id', $student->id);
                    $score = $record && $record->nilai !== null ? (float) $record->nilai : null;
                    $scores[$column->key] = $score;
                    if ($score !== null) {
                        $values[] = $score;
                    }
                }

                $student->nilai_per_asesmen = $scores;
                $student->jumlah_nilai = count($values) ? array_sum($values) : null;
                $student->rata_rata = count($values) && $assessmentColumns->isNotEmpty()
                    ? array_sum($values) / $assessmentColumns->count()
                    : null;

                return $student;
            });

        $filledScores = $assessmentRecords->whereNotNull('nilai')->pluck('nilai')->map(fn ($value) => (float) $value);
        $studentAverages = $students->pluck('rata_rata')->filter(fn ($value) => $value !== null);
        $summary = [
            'total_siswa' => $students->count(),
            'total_asesmen' => $assessmentColumns->count(),
            'total_nilai' => $filledScores->count(),
            'rata_rata' => $studentAverages->isNotEmpty() ? round($studentAverages->avg(), 2) : null,
        ];
        $title = $kategori === 'formatif' ? 'Rekap Asesmen Formatif' : 'Rekap Asesmen Sumatif';

        return view('nilai.rekap_asesmen', compact(
            'assessmentColumns',
            'kelasId',
            'mapelId',
            'selectedKelas',
            'selectedMapel',
            'students',
            'summary',
            'tahunAjaranAktif',
            'semesterAktif',
            'title',
            'uncategorizedRecordCount'
        ));
    }

    public function kategorisasiAsesmen(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'nullable|exists:mata_pelajaran,id',
        ]);

        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);
        $isGuruBk = $user && $user->hasRole('Guru BK');
        $kelasId = (int) $validated['kelas_id'];
        $mapelId = $validated['mapel_id'] ?? null;
        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();

        $this->authorizeAssessmentClass(
            $kelasId,
            $guru,
            $isAdminOrKepala,
            $isGuruBk,
            $tahunAjaranAktif?->id,
            $semesterAktif?->id
        );

        $rowsQuery = DB::table('nilai_harian as nh')
            ->leftJoin('mata_pelajaran as mp', 'nh.mapel_id', '=', 'mp.id')
            ->leftJoin('komponen_nilai as kn', 'nh.komponen_id', '=', 'kn.id')
            ->where('nh.kelas_id', $kelasId)
            ->when($mapelId, function ($query) use ($mapelId) {
                $query->where('nh.mapel_id', $mapelId);
            })
            ->when($tahunAjaranAktif, function ($query) use ($tahunAjaranAktif) {
                $query->where('nh.tahun_ajaran_id', $tahunAjaranAktif->id);
            })
            ->when($semesterAktif, function ($query) use ($semesterAktif) {
                $query->where('nh.semester_id', $semesterAktif->id);
            });

        if ($guru && ! $isAdminOrKepala && ! $isGuruBk) {
            $rowsQuery->where('nh.guru_id', $guru->id);
        }

        $rows = $rowsQuery
            ->select(
                'nh.id',
                'nh.assessment_uuid',
                'nh.siswa_id',
                'nh.kelas_id',
                'nh.mapel_id',
                'nh.tanggal',
                'nh.komponen_id',
                'nh.rencana_pembelajaran_id',
                'nh.materi_pelajaran',
                'nh.jenis_penilaian',
                'nh.kategori_asesmen',
                'nh.guru_id',
                'nh.tahun_ajaran_id',
                'nh.semester_id',
                'nh.nilai',
                'mp.nama_mapel',
                'kn.nama_komponen'
            )
            ->orderByDesc('nh.tanggal')
            ->orderBy('nh.id')
            ->get();

        $assessmentGroups = $rows->groupBy(function ($row) {
            if ($row->assessment_uuid) {
                return $row->assessment_uuid;
            }

            return 'legacy:' . sha1(implode('|', [
                $row->kelas_id,
                $row->mapel_id,
                $row->tanggal,
                $row->komponen_id,
                $row->rencana_pembelajaran_id,
                $row->materi_pelajaran,
                $row->jenis_penilaian,
                $row->guru_id,
                $row->tahun_ajaran_id,
                $row->semester_id,
            ]));
        });

        $assessmentRows = $assessmentGroups->map(function ($sessionRows) {
            $first = $sessionRows->first();
            $categories = $sessionRows->pluck('kategori_asesmen')->filter()->unique();

            return (object) [
                'record_id' => $first->id,
                'judul' => trim((string) ($first->materi_pelajaran ?: $first->jenis_penilaian ?: $first->nama_komponen ?: 'Penilaian Harian')),
                'kelas_id' => $first->kelas_id,
                'mapel_id' => $first->mapel_id,
                'mapel' => $first->nama_mapel ?: 'Mata pelajaran',
                'tanggal' => $first->tanggal,
                'jumlah_siswa' => $sessionRows->pluck('siswa_id')->unique()->count(),
                'jumlah_nilai' => $sessionRows->whereNotNull('nilai')->count(),
                'kategori' => $categories->count() > 1 ? 'campuran' : $categories->first(),
            ];
        })->sortByDesc('tanggal')->values();

        $selectedKelas = Kelas::findOrFail($kelasId);
        $selectedMapel = $mapelId ? MataPelajaran::find($mapelId) : null;

        return view('nilai.kategorisasi_asesmen', compact(
            'assessmentRows',
            'kelasId',
            'mapelId',
            'selectedKelas',
            'selectedMapel',
            'tahunAjaranAktif',
            'semesterAktif'
        ));
    }

    public function updateKategoriAsesmen(Request $request)
    {
        $validated = $request->validate([
            'record_id' => 'required|exists:nilai_harian,id',
            'kategori_asesmen' => 'required|in:formatif,sumatif',
        ]);

        $record = DB::table('nilai_harian')->where('id', $validated['record_id'])->first();
        abort_unless($record, 404);

        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);
        $isGuruBk = $user && $user->hasRole('Guru BK');
        $this->authorizeAssessmentClass(
            (int) $record->kelas_id,
            $guru,
            $isAdminOrKepala,
            $isGuruBk,
            (int) $record->tahun_ajaran_id,
            (int) $record->semester_id
        );

        $query = DB::table('nilai_harian')
            ->where('kelas_id', $record->kelas_id)
            ->where('mapel_id', $record->mapel_id)
            ->whereDate('tanggal', $record->tanggal)
            ->where('tahun_ajaran_id', $record->tahun_ajaran_id)
            ->where('semester_id', $record->semester_id);

        if ($record->assessment_uuid) {
            $query->where('assessment_uuid', $record->assessment_uuid);
        } else {
            $query->whereNull('assessment_uuid');
            foreach (['komponen_id', 'rencana_pembelajaran_id', 'materi_pelajaran', 'jenis_penilaian', 'guru_id'] as $column) {
                $record->$column === null
                    ? $query->whereNull($column)
                    : $query->where($column, $record->$column);
            }
        }

        if ($guru && ! $isAdminOrKepala && ! $isGuruBk) {
            $query->where('guru_id', $guru->id);
        }

        $updatedCount = $query->update([
            'kategori_asesmen' => $validated['kategori_asesmen'],
            'updated_at' => now(),
        ]);

        return redirect()->route('nilai.kategorisasi', array_filter([
            'kelas_id' => $record->kelas_id,
            'mapel_id' => $record->mapel_id,
        ]))->with('success', "Kategori berhasil diperbarui untuk {$updatedCount} data nilai.");
    }

    private function authorizeAssessmentClass(int $kelasId, $guru, bool $isAdminOrKepala, bool $isGuruBk, ?int $tahunAjaranId, ?int $semesterId): void
    {
        if ($isAdminOrKepala) {
            return;
        }

        abort_unless($guru, 403);

        if ($isGuruBk) {
            abort_unless(Kelas::where('id', $kelasId)->where('guru_bk_id', $guru->id)->exists(), 403);
            return;
        }

        $jadwalQuery = JadwalKbm::where('guru_id', $guru->id)->where('kelas_id', $kelasId);
        if ($tahunAjaranId) {
            $jadwalQuery->where('tahun_ajaran_id', $tahunAjaranId);
        }
        if ($semesterId) {
            $jadwalQuery->where('semester_id', $semesterId);
        }

        abort_unless($jadwalQuery->exists(), 403);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'rencana_pembelajaran_id' => 'nullable|exists:rencana_pembelajarans,id',
            'materi_pelajaran' => 'nullable|string|max:255',
            'jenis_penilaian' => 'nullable|string|max:255',
            'kategori_asesmen' => 'nullable|in:formatif,sumatif',
            'komponen_id' => 'nullable|numeric|exists:komponen_nilai,id',
            'return_to' => 'nullable|in:nilai,asesmen-formatif,asesmen-sumatif',
        ]);

        if ($request->input('return_to') === 'asesmen-formatif') {
            $validated['kategori_asesmen'] = 'formatif';
        } elseif ($request->input('return_to') === 'asesmen-sumatif') {
            $validated['kategori_asesmen'] = 'sumatif';
        }

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

        if (! empty($validated['jenis_penilaian'])) {
            $jenisPenilaian = trim((string) $validated['jenis_penilaian']);
            $existingKomponen = DB::table('komponen_nilai')
                ->where('guru_id', $guru->id)
                ->whereRaw('LOWER(nama_komponen) = ?', [mb_strtolower($jenisPenilaian)])
                ->first();

            if ($existingKomponen) {
                $validated['komponen_id'] = $existingKomponen->id;
            } else {
                $komponenId = DB::table('komponen_nilai')->insertGetId([
                    'guru_id' => $guru->id,
                    'mata_pelajaran_id' => $validated['mapel_id'],
                    'kelas_id' => $validated['kelas_id'],
                    'nama_komponen' => $jenisPenilaian,
                    'bobot' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $validated['komponen_id'] = $komponenId;
            }
        }

        if (!$user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            if (! empty($validated['rencana_pembelajaran_id'])) {
                $rencanaAllowed = RencanaPembelajaran::where('id', $validated['rencana_pembelajaran_id'])
                    ->where('guru_id', $guru->id)
                    ->exists();

                if (! $rencanaAllowed) {
                    return redirect()->route('nilai.index')->with('error', 'Rencana pembelajaran tidak ditemukan atau bukan milik Anda.');
                }
            }

            if (! empty($validated['komponen_id'])) {
                $komponenAllowed = DB::table('komponen_nilai')
                    ->where('id', $validated['komponen_id'])
                    ->where('guru_id', $guru->id)
                    ->exists();

                if (! $komponenAllowed) {
                    return redirect()->route('nilai.index')->with('error', 'Komponen penilaian tidak ditemukan atau bukan milik Anda.');
                }
            }
        }

        $siswaIds = Siswa::where('kelas_id', $validated['kelas_id'])->pluck('id');
        if ($siswaIds->isEmpty()) {
            return redirect()->route('nilai.index')->with('error', 'Tidak ada siswa pada kelas ini.');
        }

        $now = now();
        $assessmentUuid = (string) Str::uuid();
        $rows = $siswaIds->map(function ($siswaId) use ($validated, $guru, $tahunAjaranAktif, $semesterAktif, $now, $assessmentUuid) {
            return [
                'assessment_uuid' => $assessmentUuid,
                'siswa_id' => $siswaId,
                'guru_id' => $guru->id,
                'kelas_id' => $validated['kelas_id'],
                'mapel_id' => $validated['mapel_id'],
                'komponen_id' => $validated['komponen_id'] ?? null,
                'rencana_pembelajaran_id' => $validated['rencana_pembelajaran_id'] ?? null,
                'materi_pelajaran' => ! empty($validated['materi_pelajaran']) ? trim($validated['materi_pelajaran']) : null,
                'jenis_penilaian' => ! empty($validated['jenis_penilaian']) ? trim($validated['jenis_penilaian']) : (empty($validated['komponen_id']) ? 'Harian / Umum' : null),
                'kategori_asesmen' => $validated['kategori_asesmen'] ?? null,
                'tanggal' => $validated['tanggal'],
                'nilai' => null,
                'tahun_ajaran_id' => $tahunAjaranAktif->id,
                'semester_id' => $semesterAktif->id,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        })->toArray();

        DB::table('nilai_harian')->insert($rows);

        $successMessage = 'Nilai harian berhasil dibuat untuk ' . count($rows) . ' siswa.';
        if ($request->input('return_to') === 'asesmen-formatif') {
            return redirect()->route('nilai.asesmen.formatif', array_filter([
                'kelas_id' => $validated['kelas_id'],
                'mapel_id' => $validated['mapel_id'],
            ]))->with('success', $successMessage);
        }

        if ($request->input('return_to') === 'asesmen-sumatif') {
            return redirect()->route('nilai.asesmen.sumatif', array_filter([
                'kelas_id' => $validated['kelas_id'],
                'mapel_id' => $validated['mapel_id'],
            ]))->with('success', $successMessage);
        }

        return redirect()->route('nilai.index', array_filter([
            'kelas_id' => $validated['kelas_id'],
            'mapel_id' => $validated['mapel_id'],
            'tanggal_nilai' => $validated['tanggal'],
            'rencana_pembelajaran_id' => $validated['rencana_pembelajaran_id'] ?? null,
            'komponen_id' => $validated['komponen_id'] ?? 0,
        ], function ($value) {
            return $value !== null && $value !== '';
        }))->with('success', $successMessage);
    }

    public function updateBatch(Request $request)
    {
        $validated = $request->validate([
            'nilai' => 'required|array',
            'nilai.*' => 'nullable',
            'kelas_id' => 'nullable|exists:kelas,id',
            'mapel_id' => 'nullable|exists:mata_pelajaran,id',
            'tanggal_nilai' => 'nullable|date',
            'rencana_pembelajaran_id' => 'nullable|exists:rencana_pembelajarans,id',
            'komponen_id' => 'nullable|integer|min:0',
            'assessment_uuid' => 'nullable|uuid',
        ]);

        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $isGuruBk = $user && $user->hasRole('Guru BK');
        $binaanKelasIds = collect();

        if ($isGuruBk && $guru) {
            $binaanKelasIds = Kelas::where('guru_bk_id', $guru->id)->pluck('id');
        }

        $now = now();
        $updated = 0;
        foreach ($validated['nilai'] as $id => $value) {
            $query = DB::table('nilai_harian')
                ->where('id', $id);

            if ($guru && !$isAdminOrKepala && !$isGuruBk) {
                $query->where('guru_id', $guru->id);
            } elseif ($isGuruBk && $binaanKelasIds->isNotEmpty()) {
                $query->whereIn('nilai_harian.kelas_id', $binaanKelasIds);
            }

            $normalizedValue = NilaiInputNormalizer::normalize($value);

            $affected = $query->update([
                'nilai' => $normalizedValue,
                'updated_at' => $now,
            ]);
            $updated += $affected;
        }

        return redirect()->route('nilai.index', array_filter([
            'kelas_id' => $validated['kelas_id'] ?? null,
            'mapel_id' => $validated['mapel_id'] ?? null,
            'tanggal_nilai' => $validated['tanggal_nilai'] ?? null,
            'rencana_pembelajaran_id' => $validated['rencana_pembelajaran_id'] ?? null,
            'komponen_id' => $validated['komponen_id'] ?? null,
            'assessment_uuid' => $validated['assessment_uuid'] ?? null,
            'edit' => $request->boolean('edit') ? 1 : null,
            'source' => $request->input('source') === 'asesmen-formatif' ? 'asesmen-formatif' : null,
                    'source' => in_array($request->input('source'), ['asesmen-formatif', 'asesmen-sumatif'], true) ? $request->input('source') : null,
        ], function ($value) {
            return $value !== null && $value !== '';
        }))
            ->with('success', 'Nilai berhasil diperbarui (' . $updated . ' siswa).');
    }

    public function inputTemplate(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'tanggal_nilai' => 'required|date',
            'rencana_pembelajaran_id' => 'nullable|exists:rencana_pembelajarans,id',
            'komponen_id' => 'nullable|integer|min:0',
            'assessment_uuid' => 'nullable|uuid',
        ]);

        $grid = $this->resolveInputGridData($validated);
        if (empty($grid['sessions'])) {
            return redirect()->route('nilai.index', $this->inputGridRedirectParams($validated))
                ->with('error', 'Tidak ada kolom penilaian untuk dibuatkan template.');
        }

        $className = Kelas::where('id', $validated['kelas_id'])->value('nama_kelas') ?? 'Kelas';
        $safeClassName = preg_replace('/[^A-Za-z0-9_-]+/', '_', (string) $className);

        return Excel::download(
            new NilaiInputGridTemplateExport($grid['students'], $grid['sessions'], (string) $className, $grid['type_catalog']),
            'template_input_nilai_' . $safeClassName . '.xlsx'
        );
    }

    public function importInput(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'tanggal_nilai' => 'required|date',
            'rencana_pembelajaran_id' => 'nullable|exists:rencana_pembelajarans,id',
            'komponen_id' => 'nullable|integer|min:0',
            'assessment_uuid' => 'nullable|uuid',
            'confirm_import' => 'required|accepted',
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        $redirectToGrid = function () use ($validated) {
            return redirect()->route('nilai.index', $this->inputGridRedirectParams($validated));
        };

        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        if (! $guru) {
            return $redirectToGrid()->with('error', 'Akun guru tidak ditemukan.');
        }

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        if (! $tahunAjaranAktif || ! $semesterAktif) {
            return $redirectToGrid()->with('error', 'Tahun ajaran atau semester aktif belum diatur.');
        }

        $grid = $this->resolveInputGridData($validated);
        if (empty($grid['sessions'])) {
            return $redirectToGrid()->with('error', 'Sesi penilaian untuk import tidak ditemukan.');
        }

        $import = new NilaiInputGridImport($grid['students'], $grid['sessions'], [
            'kelas_id' => (int) $validated['kelas_id'],
            'mapel_id' => (int) $validated['mapel_id'],
            'tanggal' => $validated['tanggal_nilai'],
            'guru_id' => (int) $guru->id,
            'tahun_ajaran_id' => (int) $tahunAjaranAktif->id,
            'semester_id' => (int) $semesterAktif->id,
            'class_name' => Kelas::where('id', $validated['kelas_id'])->value('nama_kelas') ?? 'Kelas',
        ]);
        Excel::import($import, $validated['file']);
        if ($import->getErrors() !== [] || $import->getPreparedRowCount() === 0) {
            $errors = $import->getErrors() ?: ['File tidak memiliki baris siswa yang dapat diimpor.'];
            return $redirectToGrid()
                ->with('warning', 'Import ditolak. Periksa kode jenis penilaian dan data template.')
                ->with('import_errors', $errors);
        }

            $overwriteCount = $import->getOverwriteCount();
            if ($overwriteCount > 0 && ! $request->boolean('confirm_overwrite')) {
                return $redirectToGrid()->with('warning', 'Import dibatalkan karena ada nilai tersimpan yang akan ditimpa. Lakukan pemeriksaan dan konfirmasi ulang.');
            }

        $newColumnCodes = $import->getNewColumnCodes();
        $returnFilters = $validated;
        if ($newColumnCodes !== []) {
            unset($returnFilters['assessment_uuid']);
        }
        $redirectToGrid = function () use ($returnFilters) {
            return redirect()->route('nilai.index', $this->inputGridRedirectParams($returnFilters));
        };

        try {
            DB::transaction(function () use ($grid, $newColumnCodes, $import, $validated, $guru, $tahunAjaranAktif, $semesterAktif) {
                $sessions = $grid['sessions'];
                if ($newColumnCodes !== []) {
                    $typeCodes = array_values(array_unique(array_column($sessions, 'type_code')));
                    if (count($typeCodes) !== 1) {
                        throw new \RuntimeException('Kolom baru hanya dapat dibuat untuk satu jenis penilaian aktif.');
                    }

                    $baseSession = $sessions[0];
                    $sameTypeCount = count(array_filter($sessions, function ($session) use ($baseSession) {
                        return $session['type_code'] === $baseSession['type_code'];
                    }));
                    $baseLabel = preg_replace('/\s+\d+$/', '', $baseSession['label']);
                    $rencanaId = $validated['rencana_pembelajaran_id'] ?? $baseSession['rencana_pembelajaran_id'];

                    foreach ($newColumnCodes as $offset => $columnCode) {
                        $assessmentUuid = (string) Str::uuid();
                        $now = now();
                        $rows = array_map(function ($student) use ($baseSession, $assessmentUuid, $rencanaId, $guru, $validated, $tahunAjaranAktif, $semesterAktif, $now) {
                            return [
                                'assessment_uuid' => $assessmentUuid,
                                'siswa_id' => $student['id'],
                                'guru_id' => $guru->id,
                                'kelas_id' => $validated['kelas_id'],
                                'mapel_id' => $validated['mapel_id'],
                                'komponen_id' => $baseSession['komponen_id'],
                                'rencana_pembelajaran_id' => $rencanaId,
                                'materi_pelajaran' => $baseSession['materi_pelajaran'],
                                'jenis_penilaian' => $baseSession['jenis_penilaian'],
                                                                'kategori_asesmen' => $baseSession['kategori_asesmen'] ?? null,
                                'tanggal' => $validated['tanggal_nilai'],
                                'nilai' => null,
                                'tahun_ajaran_id' => $tahunAjaranAktif->id,
                                'semester_id' => $semesterAktif->id,
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }, $grid['students']);

                        DB::table('nilai_harian')->insert($rows);
                        $sessions[] = array_merge($baseSession, [
                            'assessment_uuid' => $assessmentUuid,
                            'rencana_pembelajaran_id' => $rencanaId,
                            'column_code' => $columnCode,
                            'label' => $baseLabel . ' ' . ($sameTypeCount + $offset + 1),
                            'scores' => [],
                        ]);
                    }
                }

                $import->save($sessions);
                if ($import->getErrors() !== []) {
                    throw new \RuntimeException(implode(' ', $import->getErrors()));
                }
            });
        } catch (\Throwable $exception) {
            report($exception);
            return $redirectToGrid()->with('error', 'Import gagal; tidak ada nilai yang disimpan.');
        }

        $message = 'Import berhasil: ' . count($import->getColumnCodes()) . ' kolom diproses';
        if ($newColumnCodes !== []) {
            $message .= ', ' . count($newColumnCodes) . ' kolom penilaian baru ditambahkan';
        }

        return $redirectToGrid()->with('success', $message . ' untuk ' . $import->getPreparedRowCount() . ' siswa.');
    }

    public function previewInput(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'tanggal_nilai' => 'required|date',
            'rencana_pembelajaran_id' => 'nullable|exists:rencana_pembelajarans,id',
            'komponen_id' => 'nullable|integer|min:0',
            'assessment_uuid' => 'nullable|uuid',
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        $grid = $this->resolveInputGridData($validated);
        if (empty($grid['sessions'])) {
            return response()->json(['errors' => ['Sesi penilaian aktif tidak ditemukan.']], 422);
        }

        $import = new NilaiInputGridImport($grid['students'], $grid['sessions'], [
            'kelas_id' => (int) $validated['kelas_id'],
            'mapel_id' => (int) $validated['mapel_id'],
            'tanggal' => $validated['tanggal_nilai'],
            'class_name' => Kelas::where('id', $validated['kelas_id'])->value('nama_kelas') ?? 'Kelas',
        ]);
        Excel::import($import, $validated['file']);

        if ($import->getErrors() !== [] || $import->getPreparedRowCount() === 0) {
            $errors = $import->getErrors() ?: ['File tidak memiliki baris siswa yang dapat diimpor.'];
            return response()->json(['errors' => $errors], 422);
        }

        $columnCodes = $import->getColumnCodes();
        $previewRows = array_map(function ($row) use ($columnCodes) {
            return array_merge(
                [$row['no'], $row['nama'], $row['jenis_penilaian'], $row['kelas']],
                array_map(function ($columnCode) use ($row) {
                    return $row['scores'][$columnCode] ?? null;
                }, $columnCodes)
            );
        }, $import->getPreviewRows());

        return response()->json([
            'column_count' => count($columnCodes),
            'new_column_count' => count($import->getNewColumnCodes()),
            'overwrite_count' => $import->getOverwriteCount(),
            'row_count' => $import->getPreparedRowCount(),
            'columns' => $columnCodes,
            'rows' => $previewRows,
        ]);
    }

    private function inputGridRedirectParams(array $filters): array
    {
        return array_filter([
            'kelas_id' => $filters['kelas_id'] ?? null,
            'mapel_id' => $filters['mapel_id'] ?? null,
            'tanggal_nilai' => $filters['tanggal_nilai'] ?? null,
            'rencana_pembelajaran_id' => $filters['rencana_pembelajaran_id'] ?? null,
            'komponen_id' => $filters['komponen_id'] ?? null,
            'assessment_uuid' => $filters['assessment_uuid'] ?? null,
        ], function ($value) {
            return $value !== null && $value !== '';
        });
    }

    private function resolveInputGridData(array $filters): array
    {
        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $isGuruBk = $user && $user->hasRole('Guru BK');

        $query = DB::table('nilai_harian as nh')
            ->join('siswa as s', 'nh.siswa_id', '=', 's.id')
            ->leftJoin('komponen_nilai as kn', 'nh.komponen_id', '=', 'kn.id')
            ->select(
                'nh.id',
                'nh.assessment_uuid',
                'nh.siswa_id',
                'nh.komponen_id',
                'nh.rencana_pembelajaran_id',
                'nh.materi_pelajaran',
                'nh.jenis_penilaian',
                'nh.kategori_asesmen',
                'nh.nilai',
                'kn.nama_komponen',
                's.nama as nama_siswa'
            )
            ->where('nh.kelas_id', $filters['kelas_id'])
            ->where('nh.mapel_id', $filters['mapel_id'])
            ->whereDate('nh.tanggal', $filters['tanggal_nilai']);

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $semesterAktif = Semester::where('is_active', true)->first();
        if ($tahunAjaranAktif) {
            $query->where('nh.tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $query->where('nh.semester_id', $semesterAktif->id);
        }
        if (! empty($filters['rencana_pembelajaran_id'])) {
            $query->where('nh.rencana_pembelajaran_id', $filters['rencana_pembelajaran_id']);
        }
        if (array_key_exists('komponen_id', $filters) && $filters['komponen_id'] !== null && $filters['komponen_id'] !== '') {
            if ((string) $filters['komponen_id'] === '0') {
                $query->whereNull('nh.komponen_id');
            } else {
                $query->where('nh.komponen_id', $filters['komponen_id']);
            }
        }
        if (! empty($filters['assessment_uuid'])) {
            $query->where('nh.assessment_uuid', $filters['assessment_uuid']);
        }
        if ($guru && ! $isAdminOrKepala && ! $isGuruBk) {
            $query->where('nh.guru_id', $guru->id);
        }
        if ($isGuruBk && $guru) {
            $binaanKelasIds = Kelas::where('guru_bk_id', $guru->id)->pluck('id');
            if ($binaanKelasIds->isNotEmpty()) {
                $query->whereIn('nh.kelas_id', $binaanKelasIds);
            }
        }

        $rows = $query->orderBy('nh.id')->get();
        $groups = $rows
            ->groupBy(function ($row) {
                return $row->assessment_uuid ?: 'legacy:' . (int) ($row->komponen_id ?? 0);
            })
            ->sortBy(function ($sessionRows) {
                return (int) $sessionRows->min('id');
            });

        $typeCatalog = $this->inputGridTypeCatalog(array_merge(
            $rows->pluck('jenis_penilaian')->filter()->all(),
            $rows->pluck('nama_komponen')->filter()->all()
        ));
        $typeCodes = [];
        foreach ($typeCatalog as $type) {
            $typeCodes[$this->normalizeInputTypeKey($type['type_name'])] = $type['type_code'];
        }
        $typeCounts = [];
        $columnNumber = 0;
        $sessions = $groups->map(function ($sessionRows) use (&$typeCodes, &$typeCounts, &$columnNumber) {
            $first = $sessionRows->first();
            $typeName = $first->komponen_id === null
                ? 'Harian / Umum'
                : trim((string) ($first->jenis_penilaian ?: $first->nama_komponen ?: 'Penilaian'));
            $typeKey = $this->normalizeInputTypeKey($typeName);

            $baseLabel = $first->komponen_id === null ? 'Nilai Harian' : 'Nilai ' . ($first->nama_komponen ?: 'Penilaian');
            $typeCounts[$baseLabel] = ($typeCounts[$baseLabel] ?? 0) + 1;
            $columnNumber++;

            return [
                'assessment_uuid' => $first->assessment_uuid,
                'komponen_id' => $first->komponen_id !== null ? (int) $first->komponen_id : null,
                'rencana_pembelajaran_id' => $first->rencana_pembelajaran_id !== null ? (int) $first->rencana_pembelajaran_id : null,
                'materi_pelajaran' => $first->materi_pelajaran,
                'jenis_penilaian' => $first->jenis_penilaian ?: $typeName,
                                'kategori_asesmen' => $first->kategori_asesmen,
                'type_name' => $typeName,
                'type_code' => $typeCodes[$typeKey],
                'column_code' => 'A' . $columnNumber,
                'label' => $baseLabel . ' ' . $typeCounts[$baseLabel],
                'scores' => $sessionRows
                    ->groupBy('siswa_id')
                    ->map(function ($studentRows) {
                        return $studentRows->first()->nilai;
                    })
                    ->all(),
            ];
        })->values()->all();

        $students = $rows
            ->unique('siswa_id')
            ->map(function ($row) {
                return ['id' => (int) $row->siswa_id, 'nama' => $row->nama_siswa];
            })
            ->sortBy(function ($student) {
                return mb_strtolower((string) $student['nama']);
            })
            ->values()
            ->all();

        return ['students' => $students, 'sessions' => $sessions, 'type_catalog' => $typeCatalog];
    }

    private function inputGridTypeCatalog(array $extraTypeNames = []): array
    {
        $typeNames = [
            'Harian / Umum',
            'Asesmen awal/diagnostik',
            'Observasi',
            'Tanya jawab',
            'Diskusi',
            'Kuis',
            'Latihan/tugas',
            'Praktik/unjuk kerja',
            'Penilaian diri',
            'Penilaian antarteman',
            'Refleksi',
            'Umpan balik',
            'Tindak lanjut (remedial/pengayaan)',
        ];

        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $componentQuery = DB::table('komponen_nilai')->whereNotNull('nama_komponen');
        if ($guru && ! $isAdminOrKepala) {
            $componentQuery->where('guru_id', $guru->id);
        }

        $typeNames = array_merge($typeNames, $componentQuery->orderBy('nama_komponen')->pluck('nama_komponen')->all(), $extraTypeNames);
        $uniqueNames = [];
        foreach ($typeNames as $typeName) {
            $typeName = trim((string) $typeName);
            $key = $this->normalizeInputTypeKey($typeName);
            if ($typeName !== '' && ! array_key_exists($key, $uniqueNames)) {
                $uniqueNames[$key] = $typeName;
            }
        }

        $catalog = [];
        foreach (array_values($uniqueNames) as $index => $typeName) {
            $catalog[] = [
                'type_code' => $index + 1,
                'type_name' => $typeName,
            ];
        }

        return $catalog;
    }

    private function normalizeInputTypeKey(string $typeName): string
    {
        $key = mb_strtolower(trim($typeName));

        return $key === 'harian' ? 'harian / umum' : $key;
    }

    public function import(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'rencana_pembelajaran_id' => 'nullable|exists:rencana_pembelajarans,id',
            'komponen_id' => 'nullable|exists:komponen_nilai,id',
            'assessment_uuid' => 'nullable|uuid',
            'kategori_asesmen' => 'nullable|in:formatif,sumatif',
            'return_to' => 'nullable|in:nilai',
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

        if (!$user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            if (! empty($validated['rencana_pembelajaran_id'])) {
                $rencanaAllowed = RencanaPembelajaran::where('id', $validated['rencana_pembelajaran_id'])
                    ->where('guru_id', $guru->id)
                    ->exists();

                if (! $rencanaAllowed) {
                    return redirect()->route('nilai.index')->with('error', 'Rencana pembelajaran tidak ditemukan atau bukan milik Anda.');
                }
            }

            if (! empty($validated['komponen_id'])) {
                $komponenAllowed = DB::table('komponen_nilai')
                    ->where('id', $validated['komponen_id'])
                    ->where('guru_id', $guru->id)
                    ->exists();

                if (! $komponenAllowed) {
                    return redirect()->route('nilai.index')->with('error', 'Komponen penilaian tidak ditemukan atau bukan milik Anda.');
                }
            }
        }

        if (! empty($validated['assessment_uuid'])) {
            $assessmentQuery = DB::table('nilai_harian')
                ->where('assessment_uuid', $validated['assessment_uuid'])
                ->where('kelas_id', $validated['kelas_id'])
                ->where('mapel_id', $validated['mapel_id'])
                ->whereDate('tanggal', $validated['tanggal']);

            if (! $user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
                $assessmentQuery->where('guru_id', $guru->id);
            }

            $assessment = $assessmentQuery->first();
            if (! $assessment) {
                return redirect()->route('nilai.index')->with('error', 'Sesi penilaian untuk import tidak ditemukan.');
            }

            $validated['komponen_id'] = $assessment->komponen_id;
            $validated['rencana_pembelajaran_id'] = $assessment->rencana_pembelajaran_id;
                    $validated['kategori_asesmen'] = $assessment->kategori_asesmen;
        }

        $import = new NilaiHarianImport(
            (int) $validated['kelas_id'],
            (int) $validated['mapel_id'],
            isset($validated['rencana_pembelajaran_id']) ? (int) $validated['rencana_pembelajaran_id'] : null,
            $validated['tanggal'],
            (int) $guru->id,
            (int) $tahunAjaranAktif->id,
            (int) $semesterAktif->id,
            $validated['komponen_id'] ?? null,
            $validated['assessment_uuid'] ?? null,
            $validated['kategori_asesmen'] ?? null
        );

        Excel::import($import, $validated['file']);

        $errors = $import->getErrors();
        $redirect = redirect()->route('nilai.index', [
            'kelas_id' => $validated['kelas_id'],
            'mapel_id' => $validated['mapel_id'],
            'tanggal_nilai' => $validated['tanggal'],
            'rencana_pembelajaran_id' => $validated['rencana_pembelajaran_id'] ?? null,
            'komponen_id' => $validated['komponen_id'] ?? 0,
        ]);

        if (count($errors) > 0) {
            return $redirect
                ->with('warning', 'Import selesai dengan beberapa error.')
                ->with('import_errors', $errors);
        }

        return $redirect->with('success', 'Import nilai berhasil.');
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

    public function destroyPenilaian(Request $request)
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'rencana_pembelajaran_id' => 'nullable|exists:rencana_pembelajarans,id',
            'komponen_id' => 'nullable|exists:komponen_nilai,id',
            'assessment_uuid' => 'nullable|uuid',
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mata_pelajaran,id',
            'return_to' => 'nullable|in:nilai,asesmen-formatif,asesmen-sumatif',
            'edit' => 'nullable|boolean',
                'source' => 'nullable|in:nilai,asesmen-formatif,asesmen-sumatif',
        ]);

        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $isGuruBk = $user && $user->hasRole('Guru BK');
        $binaanKelasIds = collect();

        if ($isGuruBk && $guru) {
            $binaanKelasIds = Kelas::where('guru_bk_id', $guru->id)->pluck('id');
        }

        $query = DB::table('nilai_harian')
            ->where('tanggal', $validated['tanggal'])
            ->where('kelas_id', $validated['kelas_id'])
            ->where('mapel_id', $validated['mapel_id']);

        if (! empty($validated['assessment_uuid'])) {
            $query->where('assessment_uuid', $validated['assessment_uuid']);
        } else {
            if (! empty($validated['rencana_pembelajaran_id'])) {
                $query->where('rencana_pembelajaran_id', $validated['rencana_pembelajaran_id']);
            } else {
                $query->whereNull('rencana_pembelajaran_id');
            }

            if ($validated['komponen_id']) {
                $query->where('komponen_id', $validated['komponen_id']);
            } else {
                $query->whereNull('komponen_id');
            }
        }

        if ($guru && !$isAdminOrKepala && !$isGuruBk) {
            $query->where('nilai_harian.guru_id', $guru->id);
        }

        if ($isGuruBk && $binaanKelasIds->isNotEmpty()) {
            $query->whereIn('nilai_harian.kelas_id', $binaanKelasIds);
        }

        $deleted = $query->delete();

        $message = "Penilaian berhasil dihapus ($deleted record).";
        if (($validated['return_to'] ?? null) === 'asesmen-formatif') {
            return redirect()->route('nilai.asesmen.formatif', array_filter([
                'kelas_id' => $validated['kelas_id'],
                'mapel_id' => $validated['mapel_id'],
            ]))->with('success', $message);
        }

        if (($validated['return_to'] ?? null) === 'asesmen-sumatif') {
            return redirect()->route('nilai.asesmen.sumatif', array_filter([
                'kelas_id' => $validated['kelas_id'],
                'mapel_id' => $validated['mapel_id'],
            ]))->with('success', $message);
        }

        return redirect()->route('nilai.index', array_filter([
            'kelas_id' => $validated['kelas_id'],
            'mapel_id' => $validated['mapel_id'],
            'tanggal_nilai' => $validated['tanggal'],
            'rencana_pembelajaran_id' => $validated['rencana_pembelajaran_id'] ?? null,
            'komponen_id' => $validated['komponen_id'] ?? 0,
            'edit' => ! empty($validated['edit']) ? 1 : null,
            'source' => $validated['source'] ?? null,
        ], function ($value) {
            return $value !== null && $value !== '';
        }))->with('success', $message);
    }
}
