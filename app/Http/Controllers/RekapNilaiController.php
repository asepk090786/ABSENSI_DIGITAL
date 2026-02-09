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
                $kelasOptions = DB::table('jadwal_kbm')
                    ->join('kelas', 'jadwal_kbm.kelas_id', '=', 'kelas.id')
                    ->where('jadwal_kbm.guru_id', $guru->id)
                    ->where('jadwal_kbm.tahun_ajaran_id', $tahunAjaranActive->id)
                    ->where('jadwal_kbm.semester_id', $semesterActive->id)
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
                ->where('jadwal_kbm.guru_id', $guru->id)
                ->where('jadwal_kbm.tahun_ajaran_id', $tahunAjaranActive->id)
                ->where('jadwal_kbm.semester_id', $semesterActive->id);
            
            if ($kelasId) {
                $query->where('jadwal_kbm.kelas_id', $kelasId);
            }
            
            $mapelOptions = $query->select('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
                ->distinct()
                ->orderBy('mata_pelajaran.nama_mapel')
                ->get();
        }

        if ($waliKelasOnly && !$mapelId && $mapelOptions->isNotEmpty()) {
            $mapelId = $mapelOptions->first()->id;
        }
        
        // Get komponen nilai options
        $komponenOptions = KomponenNilai::orderBy('nama_komponen')->get();
        
        // Get rekap data if filters are applied
        $rekapData = null;
        $selectedKelas = null;
        $selectedMapel = null;
        $selectedKomponen = null;
        
        if ($kelasId && $mapelId) {
            $selectedKelas = Kelas::find($kelasId);
            $selectedMapel = MataPelajaran::find($mapelId);
            $selectedKomponen = $komponenId ? KomponenNilai::find($komponenId) : null;
            
            // Query to get students with their grades
            $query = DB::table('siswa')
                ->leftJoin('nilai_harian', function($join) use ($mapelId, $komponenId, $tahunAjaranActive, $semesterActive, $kelasId) {
                    $join->on('siswa.id', '=', 'nilai_harian.siswa_id')
                        ->where('nilai_harian.mapel_id', $mapelId)
                        ->where('nilai_harian.kelas_id', $kelasId)
                        ->where('nilai_harian.tahun_ajaran_id', $tahunAjaranActive->id)
                        ->where('nilai_harian.semester_id', $semesterActive->id);
                    
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
            
            $rekapData = $query;
        }
        
        return view('rekap_nilai.index', compact(
            'kelasOptions',
            'mapelOptions',
            'komponenOptions',
            'kelasId',
            'mapelId',
            'komponenId',
            'rekapData',
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
                    ->where('nilai_harian.mapel_id', $mapelId)
                    ->where('nilai_harian.tahun_ajaran_id', $tahunAjaranActive->id)
                    ->where('nilai_harian.semester_id', $semesterActive->id);
                
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
