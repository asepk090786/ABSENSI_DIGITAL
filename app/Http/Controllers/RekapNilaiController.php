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
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $guru = auth()->user()->guru;
        $isAdminOrKepala = auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);
        
        // Get active tahun ajaran and semester
        $tahunAjaranActive = TahunAjaran::where('is_active', true)->first();
        $semesterActive = Semester::where('is_active', true)->first();
        
        // Get filter parameters
        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');
        $komponenId = $request->get('komponen_id');
        $waliKelasOnly = $request->boolean('wali_kelas');
        
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
        $komponenOptions = KomponenNilai::orderBy('nama_komponen')->get();
        
        // Get rekap data if filters are applied
        $rekapData = null;
        $rekapKomponenColumns = collect();
        $selectedKelas = null;
        $selectedMapel = null;
        $selectedKomponen = null;
        
        if ($kelasId && $mapelId) {
            $selectedKelas = Kelas::find($kelasId);
            $selectedMapel = MataPelajaran::find($mapelId);
            $selectedKomponen = $komponenId ? KomponenNilai::find($komponenId) : null;

            $students = DB::table('siswa')
                ->where('kelas_id', $kelasId)
                ->select('id', 'nis', 'nisn', 'nama')
                ->orderBy('nama')
                ->get();

            $nilaiPerKomponen = DB::table('nilai_harian')
                ->leftJoin('komponen_nilai', 'nilai_harian.komponen_id', '=', 'komponen_nilai.id')
                ->where('nilai_harian.kelas_id', $kelasId)
                ->where('nilai_harian.mapel_id', $mapelId)
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
                })
                ->select(
                    'nilai_harian.siswa_id',
                    DB::raw('COALESCE(nilai_harian.komponen_id, 0) as komponen_id'),
                    DB::raw("COALESCE(komponen_nilai.nama_komponen, 'Harian') as nama_komponen"),
                    DB::raw('AVG(nilai_harian.nilai) as nilai_komponen')
                )
                ->groupBy('nilai_harian.siswa_id', DB::raw('COALESCE(nilai_harian.komponen_id, 0)'), DB::raw("COALESCE(komponen_nilai.nama_komponen, 'Harian')"))
                ->get();

            $rekapKomponenColumns = $nilaiPerKomponen
                ->map(function ($row) {
                    return (object) [
                        'id' => (int) $row->komponen_id,
                        'nama' => $row->nama_komponen,
                    ];
                })
                ->unique('id')
                ->sortBy(function ($item) {
                    return mb_strtolower((string) $item->nama);
                })
                ->values();

            $nilaiIndex = [];
            foreach ($nilaiPerKomponen as $item) {
                $studentId = (int) $item->siswa_id;
                $komponenKey = (int) $item->komponen_id;
                if (!isset($nilaiIndex[$studentId])) {
                    $nilaiIndex[$studentId] = [];
                }
                $nilaiIndex[$studentId][$komponenKey] = $item->nilai_komponen !== null ? (float) $item->nilai_komponen : null;
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
                $rataRata = count($validValues) ? ($jumlah / count($validValues)) : null;

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
        
        $guru = auth()->user()->guru;
        $tahunAjaranActive = TahunAjaran::where('is_active', true)->first();
        $semesterActive = Semester::where('is_active', true)->first();
        
        $selectedKelas = Kelas::find($kelasId);
        $selectedMapel = MataPelajaran::find($mapelId);
        $selectedKomponen = $komponenId ? KomponenNilai::find($komponenId) : null;
        
        // Query to get students with their grades
        $query = DB::table('siswa')
            ->leftJoin('nilai_harian', function($join) use ($mapelId, $komponenId, $tahunAjaranActive, $semesterActive) {
                $join->on('siswa.id', '=', 'nilai_harian.siswa_id')
                    ->where('nilai_harian.mapel_id', $mapelId);

                if ($tahunAjaranActive) {
                    $join->where(function ($q) use ($tahunAjaranActive) {
                        $q->where('nilai_harian.tahun_ajaran_id', $tahunAjaranActive->id)
                            ->orWhereNull('nilai_harian.tahun_ajaran_id');
                    });
                }

                if ($semesterActive) {
                    $join->where(function ($q) use ($semesterActive) {
                        $q->where('nilai_harian.semester_id', $semesterActive->id)
                            ->orWhereNull('nilai_harian.semester_id');
                    });
                }
                
                if ($komponenId) {
                    $join->where('nilai_harian.komponen_id', $komponenId);
                }
            })
            ->where('siswa.kelas_id', $kelasId)
            ->select(
                'siswa.id',
                'siswa.nis',
                'siswa.nisn',
                'siswa.nama',
                DB::raw('AVG(nilai_harian.nilai) as rata_rata'),
                DB::raw('COUNT(nilai_harian.id) as jumlah_nilai'),
                DB::raw('MAX(nilai_harian.nilai) as nilai_tertinggi'),
                DB::raw('MIN(nilai_harian.nilai) as nilai_terendah')
            )
            ->groupBy('siswa.id', 'siswa.nis', 'siswa.nisn', 'siswa.nama')
            ->orderBy('siswa.nama')
            ->get();
        
        return \Excel::download(
            new \App\Exports\RekapNilaiExport($query, $selectedKelas, $selectedMapel, $selectedKomponen, $tahunAjaranActive, $semesterActive),
            'Rekap_Nilai_' . $selectedKelas->nama_kelas . '_' . $selectedMapel->nama_mapel . '_' . date('Y-m-d') . '.xlsx'
        );
    }
}
