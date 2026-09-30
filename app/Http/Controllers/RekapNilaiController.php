<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\KomponenNilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RekapNilaiController extends Controller
{
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
        ]));
    }

    private function assessmentCountsByComponent($query): array
    {
        return (clone $query)
            ->select(
                'nilai_harian.assessment_uuid',
                'nilai_harian.tanggal',
                'nilai_harian.komponen_id',
                'nilai_harian.rencana_pembelajaran_id',
                'nilai_harian.materi_pelajaran',
                'nilai_harian.jenis_penilaian'
            )
            ->get()
            ->groupBy(fn ($record) => (int) ($record->komponen_id ?? 0))
            ->map(fn ($records) => $records->map(fn ($record) => $this->assessmentGroupKey($record))->unique()->count())
            ->all();
    }

    private function scopedKomponenQuery()
    {
        $query = KomponenNilai::query();
        $user = auth()->user();

        if ($user && $user->guru_id && !$user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Pengawas Pembina'])) {
            $query->where('guru_id', $user->guru_id);
        }
        return $query;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $guru = $user->guru;
        $isSiswa = $user && $user->hasRole('Siswa');
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);
        
        // Get active tahun ajaran and semester
        $tahunAjaranActive = TahunAjaran::where('is_active', true)->first();
        $semesterActive = Semester::where('is_active', true)->first();
        
        // Get filter parameters
        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');
        $komponenId = $request->get('komponen_id');
        $waliKelasOnly = $request->boolean('wali_kelas');
        
        // If this is a student account, force class to the student's own class
        $siswaKelasId = $isSiswa ? optional($user->siswa)->kelas_id : null;
        if ($isSiswa && $siswaKelasId) {
            $kelasId = $siswaKelasId;
        }

        // Get kelas options from guru's jadwal
        $kelasOptions = collect();
        $kelasBinaan = null;
        if ($guru) {
            if ($waliKelasOnly) {
                $kelasBinaan = Kelas::where('wali_kelas_id', $guru->id)->first();
                if ($kelasBinaan) {
                    $kelasOptions = collect([
                        (object) [
                            'id' => $kelasBinaan->id,
                            'nama_kelas' => $kelasBinaan->nama_kelas,
                            'tingkat_kelas' => $kelasBinaan->tingkat_kelas
                        ]
                    ]);
                    $kelasId = $kelasBinaan->id;
                }
            } else {
                $kelasQuery = DB::table('jadwal_kbm')
                    ->join('kelas', 'jadwal_kbm.kelas_id', '=', 'kelas.id')
                    ->where('jadwal_kbm.guru_id', $guru->id);

                if ($tahunAjaranActive) {
                    $kelasQuery->where('jadwal_kbm.tahun_ajaran_id', $tahunAjaranActive->id);
                }

                if ($semesterActive) {
                    $kelasQuery->where('jadwal_kbm.semester_id', $semesterActive->id);
                }

                $kelasOptions = $kelasQuery
                    ->select('kelas.id', 'kelas.nama_kelas', 'kelas.tingkat_kelas')
                    ->distinct()
                    ->orderBy('kelas.tingkat_kelas')
                    ->orderBy('kelas.nama_kelas')
                    ->get();
            }
        } elseif ($isSiswa && $siswaKelasId) {
            $kelasModel = Kelas::find($siswaKelasId);
            if ($kelasModel) {
                $kelasOptions = collect([
                    (object) [
                        'id' => $kelasModel->id,
                        'nama_kelas' => $kelasModel->nama_kelas,
                        'tingkat_kelas' => $kelasModel->tingkat_kelas,
                    ]
                ]);
            }
        } elseif ($isAdminOrKepala) {
            $kelasQuery = DB::table('jadwal_kbm')
                ->join('kelas', 'jadwal_kbm.kelas_id', '=', 'kelas.id');

            if ($tahunAjaranActive) {
                $kelasQuery->where('jadwal_kbm.tahun_ajaran_id', $tahunAjaranActive->id);
            }

            if ($semesterActive) {
                $kelasQuery->where('jadwal_kbm.semester_id', $semesterActive->id);
            }

            $kelasOptions = $kelasQuery
                ->select('kelas.id', 'kelas.nama_kelas', 'kelas.tingkat_kelas')
                ->distinct()
                ->orderBy('kelas.tingkat_kelas')
                ->orderBy('kelas.nama_kelas')
                ->get();

            if ($kelasOptions->isEmpty()) {
                $kelasDariNilaiQuery = DB::table('nilai_harian')
                    ->join('kelas', 'nilai_harian.kelas_id', '=', 'kelas.id')
                    ->whereNotNull('nilai_harian.nilai');

                if ($tahunAjaranActive) {
                    $kelasDariNilaiQuery->where('nilai_harian.tahun_ajaran_id', $tahunAjaranActive->id);
                }

                if ($semesterActive) {
                    $kelasDariNilaiQuery->where('nilai_harian.semester_id', $semesterActive->id);
                }

                $kelasOptions = $kelasDariNilaiQuery
                    ->select('kelas.id', 'kelas.nama_kelas', 'kelas.tingkat_kelas')
                    ->distinct()
                    ->orderBy('kelas.tingkat_kelas')
                    ->orderBy('kelas.nama_kelas')
                    ->get();
            }
        }
        
        // Get mapel options from guru's jadwal
        $mapelOptions = collect();
        if ($guru) {
            $query = DB::table('jadwal_kbm')
                ->join('mata_pelajaran', 'jadwal_kbm.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->where('jadwal_kbm.guru_id', $guru->id);

            if ($tahunAjaranActive) {
                $query->where('jadwal_kbm.tahun_ajaran_id', $tahunAjaranActive->id);
            }

            if ($semesterActive) {
                $query->where('jadwal_kbm.semester_id', $semesterActive->id);
            }
            
            if ($kelasId) {
                $query->where('jadwal_kbm.kelas_id', $kelasId);
            }
            
            $mapelOptions = $query->select('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
                ->distinct()
                ->orderBy('mata_pelajaran.nama_mapel')
                ->get();
        } elseif ($isSiswa && $kelasId) {
            $query = DB::table('nilai_harian')
                ->join('mata_pelajaran', 'nilai_harian.mapel_id', '=', 'mata_pelajaran.id')
                ->where('nilai_harian.kelas_id', $kelasId)
                ->whereNotNull('nilai_harian.mapel_id');

            if ($tahunAjaranActive) {
                $query->where('nilai_harian.tahun_ajaran_id', $tahunAjaranActive->id);
            }

            if ($semesterActive) {
                $query->where('nilai_harian.semester_id', $semesterActive->id);
            }

            $mapelOptions = $query->select('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
                ->distinct()
                ->orderBy('mata_pelajaran.nama_mapel')
                ->get();
        } elseif ($isAdminOrKepala) {
            $query = DB::table('jadwal_kbm')
                ->join('mata_pelajaran', 'jadwal_kbm.mata_pelajaran_id', '=', 'mata_pelajaran.id');

            if ($tahunAjaranActive) {
                $query->where('jadwal_kbm.tahun_ajaran_id', $tahunAjaranActive->id);
            }

            if ($semesterActive) {
                $query->where('jadwal_kbm.semester_id', $semesterActive->id);
            }

            if ($kelasId) {
                $query->where('jadwal_kbm.kelas_id', $kelasId);
            }

            $mapelOptions = $query->select('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
                ->distinct()
                ->orderBy('mata_pelajaran.nama_mapel')
                ->get();

            if ($mapelOptions->isEmpty()) {
                $mapelDariNilaiQuery = DB::table('nilai_harian')
                    ->join('mata_pelajaran', 'nilai_harian.mapel_id', '=', 'mata_pelajaran.id')
                    ->whereNotNull('nilai_harian.nilai');

                if ($tahunAjaranActive) {
                    $mapelDariNilaiQuery->where('nilai_harian.tahun_ajaran_id', $tahunAjaranActive->id);
                }

                if ($semesterActive) {
                    $mapelDariNilaiQuery->where('nilai_harian.semester_id', $semesterActive->id);
                }

                if ($kelasId) {
                    $mapelDariNilaiQuery->where('nilai_harian.kelas_id', $kelasId);
                }

                $mapelOptions = $mapelDariNilaiQuery
                    ->select('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
                    ->distinct()
                    ->orderBy('mata_pelajaran.nama_mapel')
                    ->get();
            }
        }

        if ($waliKelasOnly && !$mapelId && $mapelOptions->isNotEmpty()) {
            $mapelId = $mapelOptions->first()->id;
        }
        
        // Get komponen nilai options
        $komponenOptions = $this->scopedKomponenQuery()->orderBy('nama_komponen')->get();
        
        // Get rekap data if filters are applied
        $rekapData = null;
        $rekapKomponenColumns = collect();
        $selectedKelas = null;
        $selectedMapel = null;
        $selectedKomponen = null;
        
        if ($komponenId) {
            $selectedKomponen = $this->scopedKomponenQuery()->find($komponenId);
        }

        if ($kelasId && $mapelId) {
            $selectedKelas = Kelas::find($kelasId);
            $selectedMapel = MataPelajaran::find($mapelId);
            $selectedKomponen = $komponenId ? $this->scopedKomponenQuery()->find($komponenId) : null;

            $students = DB::table('siswa')
                ->where('kelas_id', $kelasId)
                ->select('id', 'nis', 'nisn', 'nama')
                ->orderBy('nama')
                ->get();

            $nilaiQuery = DB::table('nilai_harian')
                ->where('nilai_harian.kelas_id', $kelasId)
                ->where('nilai_harian.mapel_id', $mapelId)
                ->when($user && $user->guru_id && ! $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Pengawas Pembina', 'Wakil Kepala Sekolah']), function ($q) use ($user) {
                    $q->where('nilai_harian.guru_id', $user->guru_id);
                })
                ->when($tahunAjaranActive, function ($q) use ($tahunAjaranActive) {
                    $q->where(function ($w) use ($tahunAjaranActive) {
                        $w->where('nilai_harian.tahun_ajaran_id', $tahunAjaranActive->id)
                            ->orWhereNull('nilai_harian.tahun_ajaran_id');
                    });
                })
                ->when($semesterActive, function ($q) use ($semesterActive) {
                    $q->where(function ($w) use ($semesterActive) {
                        $w->where('nilai_harian.semester_id', $semesterActive->id)
                            ->orWhereNull('nilai_harian.semester_id');
                    });
                })
                ->when($komponenId, function ($q) use ($komponenId) {
                    $q->where('nilai_harian.komponen_id', $komponenId);
                });

            $assessmentCountsByComponent = $this->assessmentCountsByComponent($nilaiQuery);

            $nilaiPerKomponen = (clone $nilaiQuery)
                ->leftJoin('komponen_nilai', 'nilai_harian.komponen_id', '=', 'komponen_nilai.id')
                ->select(
                    'nilai_harian.siswa_id',
                    DB::raw('COALESCE(nilai_harian.komponen_id, 0) as komponen_id'),
                    DB::raw("COALESCE(komponen_nilai.nama_komponen, 'Harian') as nama_komponen"),
                    DB::raw('SUM(nilai_harian.nilai) as total_nilai_komponen')
                )
                ->groupBy('nilai_harian.siswa_id', DB::raw('COALESCE(nilai_harian.komponen_id, 0)'), DB::raw("COALESCE(komponen_nilai.nama_komponen, 'Harian')"))
                ->get();

            if ($komponenId) {
                $rekapKomponenColumns = $nilaiPerKomponen
                    ->map(function ($row) {
                        return (object) [
                            'id' => (int) $row->komponen_id,
                            'nama' => $row->nama_komponen,
                        ];
                    })
                    ->unique('id')
                    ->values();
            } else {
                $rekapKomponenColumns = $komponenOptions
                    ->map(function ($komponen) {
                        return (object) [
                            'id' => (int) $komponen->id,
                            'nama' => $komponen->nama_komponen,
                        ];
                    })
                    ->keyBy('id');

                foreach ($nilaiPerKomponen as $row) {
                    $componentId = (int) $row->komponen_id;
                    if (!$rekapKomponenColumns->has($componentId)) {
                        $rekapKomponenColumns->put($componentId, (object) [
                            'id' => $componentId,
                            'nama' => $row->nama_komponen,
                        ]);
                    }
                }

                $rekapKomponenColumns = $rekapKomponenColumns
                    ->sortBy(fn ($item) => mb_strtolower((string) $item->nama))
                    ->values();
            }

            $nilaiIndex = [];
            foreach ($nilaiPerKomponen as $item) {
                $studentId = (int) $item->siswa_id;
                $komponenKey = (int) $item->komponen_id;
                if (!isset($nilaiIndex[$studentId])) {
                    $nilaiIndex[$studentId] = [];
                }
                $assessmentCount = $assessmentCountsByComponent[$komponenKey] ?? 0;
                $nilaiIndex[$studentId][$komponenKey] = $item->total_nilai_komponen !== null && $assessmentCount > 0
                    ? (float) $item->total_nilai_komponen / $assessmentCount
                    : null;
            }

            $rekapData = $students->map(function ($student) use ($rekapKomponenColumns, $nilaiIndex) {
                $studentId = (int) $student->id;
                $componentValues = [];
                $validValues = [];

                foreach ($rekapKomponenColumns as $komponen) {
                    $komponenId = (int) $komponen->id;
                    $value = $nilaiIndex[$studentId][$komponenId] ?? null;
                    $componentValues[$komponenId] = $value;
                    if ($value !== null) {
                        $validValues[] = (float) $value;
                    }
                }

                $jumlah = count($validValues) ? array_sum($validValues) : null;
                $columnCount = $rekapKomponenColumns->count();
                $rataRata = count($validValues) && $columnCount > 0
                    ? ($jumlah / $columnCount)
                    : null;

                return (object) [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'nama' => $student->nama,
                    'nilai_komponen' => $componentValues,
                    'jumlah' => $jumlah,
                    'rata_rata' => $rataRata,
                ];
            });
        }
        
        return view('rekap_nilai.index', compact(
            'kelasOptions',
            'mapelOptions',
            'komponenOptions',
            'kelasId',
            'mapelId',
            'komponenId',
            'rekapData',
            'rekapKomponenColumns',
            'selectedKelas',
            'selectedMapel',
            'selectedKomponen',
            'tahunAjaranActive',
            'semesterActive'
        ));
    }
    
    /**
     * Export rekap to Excel
     */
    public function export(Request $request)
    {
        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');
        $komponenId = $request->get('komponen_id');
        
        $user = auth()->user();
        $guru = $user ? $user->guru : null;
        $tahunAjaranActive = TahunAjaran::where('is_active', true)->first();
        $semesterActive = Semester::where('is_active', true)->first();
        $canSeeAll = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah', 'Pengawas Pembina']);

        $selectedKelas = Kelas::find($kelasId);
        $selectedMapel = MataPelajaran::find($mapelId);
        $selectedKomponen = $komponenId ? $this->scopedKomponenQuery()->find($komponenId) : null;

        $nilaiQuery = DB::table('nilai_harian')
            ->where('nilai_harian.kelas_id', $kelasId)
            ->where('nilai_harian.mapel_id', $mapelId)
            ->when($user && $user->guru_id && ! $canSeeAll, function ($q) use ($user) {
                $q->where('nilai_harian.guru_id', $user->guru_id);
            })
            ->when($tahunAjaranActive, function ($q) use ($tahunAjaranActive) {
                $q->where(function ($w) use ($tahunAjaranActive) {
                    $w->where('nilai_harian.tahun_ajaran_id', $tahunAjaranActive->id)
                        ->orWhereNull('nilai_harian.tahun_ajaran_id');
                });
            })
            ->when($semesterActive, function ($q) use ($semesterActive) {
                $q->where(function ($w) use ($semesterActive) {
                    $w->where('nilai_harian.semester_id', $semesterActive->id)
                        ->orWhereNull('nilai_harian.semester_id');
                });
            })
            ->when($komponenId, function ($q) use ($komponenId) {
                $q->where('nilai_harian.komponen_id', $komponenId);
            });
        $assessmentCountsByComponent = $this->assessmentCountsByComponent($nilaiQuery);
        $displayedComponentIds = $komponenId
            ? collect(array_keys($assessmentCountsByComponent))
            : $this->scopedKomponenQuery()
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->merge(array_keys($assessmentCountsByComponent));
        $displayedComponentCount = $displayedComponentIds->unique()->count();
        $scoreRowsByStudent = (clone $nilaiQuery)
            ->select(
                'nilai_harian.siswa_id',
                DB::raw('COALESCE(nilai_harian.komponen_id, 0) as komponen_id'),
                DB::raw('COUNT(nilai_harian.id) as jumlah_nilai'),
                DB::raw('SUM(nilai_harian.nilai) as total_nilai'),
                DB::raw('MAX(nilai_harian.nilai) as nilai_tertinggi'),
                DB::raw('MIN(nilai_harian.nilai) as nilai_terendah')
            )
            ->groupBy('nilai_harian.siswa_id', DB::raw('COALESCE(nilai_harian.komponen_id, 0)'))
            ->get()
            ->groupBy('siswa_id');

        $query = DB::table('siswa')
            ->where('siswa.kelas_id', $kelasId)
            ->select('siswa.id', 'siswa.nis', 'siswa.nisn', 'siswa.nama')
            ->orderBy('siswa.nama')
            ->get()
            ->map(function ($student) use ($scoreRowsByStudent, $assessmentCountsByComponent, $displayedComponentCount) {
                $scoreRows = $scoreRowsByStudent->get($student->id, collect());
                $componentAverages = $scoreRows->map(function ($row) use ($assessmentCountsByComponent) {
                    $assessmentCount = $assessmentCountsByComponent[(int) $row->komponen_id] ?? 0;

                    return $row->total_nilai !== null && $assessmentCount > 0
                        ? (float) $row->total_nilai / $assessmentCount
                        : null;
                })->filter(fn ($value) => $value !== null);

                return (object) [
                    'id' => $student->id,
                    'nis' => $student->nis,
                    'nisn' => $student->nisn,
                    'nama' => $student->nama,
                    'jumlah_nilai' => $scoreRows->sum('jumlah_nilai'),
                    'rata_rata' => $componentAverages->isNotEmpty() && $displayedComponentCount > 0
                        ? $componentAverages->sum() / $displayedComponentCount
                        : null,
                    'nilai_tertinggi' => $scoreRows->max('nilai_tertinggi'),
                    'nilai_terendah' => $scoreRows->min('nilai_terendah'),
                ];
            });
        
        return \Excel::download(
            new \App\Exports\RekapNilaiExport($query, $selectedKelas, $selectedMapel, $selectedKomponen, $tahunAjaranActive, $semesterActive),
            'Rekap_Nilai_' . $selectedKelas->nama_kelas . '_' . $selectedMapel->nama_mapel . '_' . date('Y-m-d') . '.xlsx'
        );
    }
}
