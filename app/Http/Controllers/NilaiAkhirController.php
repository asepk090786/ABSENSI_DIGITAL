<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\AssessmentCalculator;

class NilaiAkhirController extends Controller
{
    protected AssessmentCalculator $calculator;

    public function __construct(AssessmentCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $guru = $user->guru;
        $isSiswa = $user && $user->hasRole('Siswa');
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);

        $tahunAjaranActive = TahunAjaran::where('is_active', true)->first();
        $semesterActive = Semester::where('is_active', true)->first();

        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');

        $siswaKelasId = $isSiswa ? optional($user->siswa)->kelas_id : null;
        if ($isSiswa && $siswaKelasId) {
            $kelasId = $siswaKelasId;
        }

        $kelasOptions = collect();
        $mapelOptions = collect();

        if ($isSiswa && $siswaKelasId) {
            $kelasModel = Kelas::find($siswaKelasId);
            if ($kelasModel) {
                $kelasOptions = collect([
                    (object) ['id' => $kelasModel->id, 'nama_kelas' => $kelasModel->nama_kelas]
                ]);
                $kelasId = $kelasModel->id;
            }

            $mapelOptions = DB::table('nilai_harian')
                ->join('mata_pelajaran', 'nilai_harian.mapel_id', '=', 'mata_pelajaran.id')
                ->where('nilai_harian.kelas_id', $kelasId)
                ->whereNotNull('nilai_harian.mapel_id')
                ->select('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
                ->distinct()
                ->orderBy('mata_pelajaran.nama_mapel')
                ->get();
        } elseif ($guru || $isAdminOrKepala) {
            $kelasQuery = DB::table('jadwal_kbm')
                ->join('kelas', 'jadwal_kbm.kelas_id', '=', 'kelas.id')
                ->when($guru, fn ($q) => $q->where('jadwal_kbm.guru_id', $guru->id))
                ->when($tahunAjaranActive, fn ($q) => $q->where('jadwal_kbm.tahun_ajaran_id', $tahunAjaranActive->id))
                ->when($semesterActive, fn ($q) => $q->where('jadwal_kbm.semester_id', $semesterActive->id))
                ->select('kelas.id', 'kelas.nama_kelas')
                ->distinct()
                ->orderBy('kelas.nama_kelas');

            $kelasOptions = $kelasQuery->get();

            if ($kelasOptions->isEmpty() && $isAdminOrKepala) {
                $kelasOptions = DB::table('nilai_harian')
                    ->join('kelas', 'nilai_harian.kelas_id', '=', 'kelas.id')
                    ->whereNotNull('nilai_harian.nilai')
                    ->select('kelas.id', 'kelas.nama_kelas')
                    ->distinct()
                    ->orderBy('kelas.nama_kelas')
                    ->get();
            }
        }

        if ($kelasId && !$mapelId && $kelasOptions->isNotEmpty()) {
            $mapelQuery = DB::table('jadwal_kbm')
                ->join('mata_pelajaran', 'jadwal_kbm.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->where('jadwal_kbm.kelas_id', $kelasId)
                ->when($guru, fn ($q) => $q->where('jadwal_kbm.guru_id', $guru->id))
                ->when($tahunAjaranActive, fn ($q) => $q->where('jadwal_kbm.tahun_ajaran_id', $tahunAjaranActive->id))
                ->when($semesterActive, fn ($q) => $q->where('jadwal_kbm.semester_id', $semesterActive->id))
                ->select('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
                ->distinct()
                ->orderBy('mata_pelajaran.nama_mapel');

            $mapelOptions = $mapelQuery->get();

            if ($mapelOptions->isEmpty() && $isAdminOrKepala) {
                $mapelOptions = DB::table('nilai_harian')
                    ->join('mata_pelajaran', 'nilai_harian.mapel_id', '=', 'mata_pelajaran.id')
                    ->where('nilai_harian.kelas_id', $kelasId)
                    ->whereNotNull('nilai_harian.nilai')
                    ->select('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
                    ->distinct()
                    ->orderBy('mata_pelajaran.nama_mapel')
                    ->get();
            }
        }

        $rekapData = null;
        $selectedKelas = null;
        $selectedMapel = null;

        if ($kelasId && $mapelId) {
            $selectedKelas = Kelas::find($kelasId);
            $selectedMapel = MataPelajaran::find($mapelId);

            $result = $this->calculator->getClassStatistics(
                $kelasId,
                $mapelId,
                $tahunAjaranActive?->id,
                $semesterActive?->id
            );

            $rekapData = $result['assessments'];
        }

        return view('nilai_akhir.index', compact(
            'kelasOptions',
            'mapelOptions',
            'kelasId',
            'mapelId',
            'rekapData',
            'selectedKelas',
            'selectedMapel',
            'tahunAjaranActive',
            'semesterActive'
        ));
    }

    public function detail(Request $request, $siswaId)
    {
        $user = auth()->user();
        $guru = $user->guru;
        $isSiswa = $user && $user->hasRole('Siswa');
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);

        $tahunAjaranActive = TahunAjaran::where('is_active', true)->first();
        $semesterActive = Semester::where('is_active', true)->first();

        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');

        $siswa = Siswa::findOrFail($siswaId);

        if ($isSiswa && optional($user->siswa)->id !== (int) $siswaId) {
            abort(403);
        }

        $assessment = $this->calculator->getStudentAssessment(
            $siswaId,
            $kelasId,
            $mapelId,
            $tahunAjaranActive?->id,
            $semesterActive?->id
        );

        $kognitifDetail = $this->calculator->getDomainDetails($siswaId, $kelasId, $mapelId, $tahunAjaranActive?->id, $semesterActive?->id, 'kognitif');
        $afektifDetail = $this->calculator->getDomainDetails($siswaId, $kelasId, $mapelId, $tahunAjaranActive?->id, $semesterActive?->id, 'afektif');
        $psikomotorikDetail = $this->calculator->getDomainDetails($siswaId, $kelasId, $mapelId, $tahunAjaranActive?->id, $semesterActive?->id, 'psikomotorik');

        return view('nilai_akhir.detail', compact(
            'siswa',
            'assessment',
            'kognitifDetail',
            'afektifDetail',
            'psikomotorikDetail',
            'kelasId',
            'mapelId'
        ));
    }

    public function rekap(Request $request)
    {
        $user = auth()->user();
        $guru = $user->guru;
        $isSiswa = $user && $user->hasRole('Siswa');
        $isAdminOrKepala = $user && $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);

        $tahunAjaranActive = TahunAjaran::where('is_active', true)->first();
        $semesterActive = Semester::where('is_active', true)->first();

        $kelasId = $request->get('kelas_id');
        $mapelId = $request->get('mapel_id');

        $siswaKelasId = $isSiswa ? optional($user->siswa)->kelas_id : null;
        if ($isSiswa && $siswaKelasId) {
            $kelasId = $siswaKelasId;
        }

        $kelasOptions = collect();
        $mapelOptions = collect();
        $rekapData = collect();
        $selectedKelas = null;
        $selectedMapel = null;

        if ($kelasId) {
            $selectedKelas = Kelas::find($kelasId);

            if (!$mapelId) {
                $mapelQuery = DB::table('jadwal_kbm')
                    ->join('mata_pelajaran', 'jadwal_kbm.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                    ->where('jadwal_kbm.kelas_id', $kelasId)
                    ->when($guru, fn ($q) => $q->where('jadwal_kbm.guru_id', $guru->id))
                    ->when($tahunAjaranActive, fn ($q) => $q->where('jadwal_kbm.tahun_ajaran_id', $tahunAjaranActive->id))
                    ->when($semesterActive, fn ($q) => $q->where('jadwal_kbm.semester_id', $semesterActive->id))
                    ->select('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
                    ->distinct()
                    ->orderBy('mata_pelajaran.nama_mapel');

                $mapelOptions = $mapelQuery->get();

                if ($mapelOptions->isEmpty() && $isAdminOrKepala) {
                    $mapelOptions = DB::table('nilai_harian')
                        ->join('mata_pelajaran', 'nilai_harian.mapel_id', '=', 'mata_pelajaran.id')
                        ->where('nilai_harian.kelas_id', $kelasId)
                        ->whereNotNull('nilai_harian.nilai')
                        ->select('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
                        ->distinct()
                        ->orderBy('mata_pelajaran.nama_mapel')
                        ->get();
                }

                if ($mapelOptions->isNotEmpty()) {
                    $mapelId = $mapelOptions->first()->id;
                }
            }

            if ($kelasId && $mapelId) {
                $selectedMapel = MataPelajaran::find($mapelId);
                $result = $this->calculator->getClassStatistics(
                    $kelasId,
                    $mapelId,
                    $tahunAjaranActive?->id,
                    $semesterActive?->id
                );
                $rekapData = $result['assessments'];
            }
        }

        return view('nilai_akhir.rekap', compact(
            'kelasOptions',
            'mapelOptions',
            'kelasId',
            'mapelId',
            'rekapData',
            'selectedKelas',
            'selectedMapel',
            'tahunAjaranActive',
            'semesterActive'
        ));
    }
}
