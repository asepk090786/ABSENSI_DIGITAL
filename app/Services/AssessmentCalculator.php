<?php

namespace App\Services;

use App\Models\KomponenNilai;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\Semester;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class AssessmentCalculator
{
    private const DEFAULT_WEIGHTS = [
        'kognitif' => 0.40,
        'afektif' => 0.30,
        'psikomotorik' => 0.30,
    ];

    private const DEFAULT_KKM = 75.0;

    private const PREDICATES = [
        ['min' => 90, 'max' => 100, 'grade' => 'A', 'description' => 'Sangat Baik'],
        ['min' => 80, 'max' => 89, 'grade' => 'B', 'description' => 'Baik'],
        ['min' => 70, 'max' => 79, 'grade' => 'C', 'description' => 'Cukup'],
        ['min' => 0, 'max' => 69, 'grade' => 'D', 'description' => 'Perlu Bimbingan'],
    ];

    public function getWeights(): array
    {
        return config('assessment.weights', self::DEFAULT_WEIGHTS);
    }

    public function getKKM(): float
    {
        return config('assessment.kkm', self::DEFAULT_KKM);
    }

    public function getPredicates(): array
    {
        return config('assessment.predicates', self::PREDICATES);
    }

    public function calculateDomainAverage(int $siswaId, int $kelasId, int $mapelId, ?int $tahunAjaranId, ?int $semesterId, string $domain): ?float
    {
        $query = DB::table('nilai_harian')
            ->join('komponen_nilai', 'nilai_harian.komponen_id', '=', 'komponen_nilai.id')
            ->where('nilai_harian.siswa_id', $siswaId)
            ->where('nilai_harian.kelas_id', $kelasId)
            ->where('nilai_harian.mapel_id', $mapelId)
            ->where('komponen_nilai.domain', $domain)
            ->whereNotNull('nilai_harian.nilai')
            ->select(
                DB::raw('SUM(nilai_harian.nilai * komponen_nilai.bobot) as weighted_sum'),
                DB::raw('SUM(komponen_nilai.bobot) as total_bobot')
            );

        if ($tahunAjaranId) {
            $query->where('nilai_harian.tahun_ajaran_id', $tahunAjaranId);
        }

        if ($semesterId) {
            $query->where('nilai_harian.semester_id', $semesterId);
        }

        $result = $query->first();

        if ($result && $result->total_bobot > 0) {
            return round((float) $result->weighted_sum / (float) $result->total_bobot, 2);
        }

        return null;
    }

    public function calculateFinalScore(float $kognitif, float $afektif, float $psikomotorik): float
    {
        $weights = $this->getWeights();

        return round(
            ($kognitif * $weights['kognitif']) +
            ($afektif * $weights['afektif']) +
            ($psikomotorik * $weights['psikomotorik']),
            2
        );
    }

    public function calculatePredicate(float $finalScore): array
    {
        $predicates = $this->getPredicates();

        foreach ($predicates as $predicate) {
            if ($finalScore >= $predicate['min'] && $finalScore <= $predicate['max']) {
                return $predicate;
            }
        }

        return ['grade' => 'D', 'description' => 'Perlu Bimbingan'];
    }

    public function calculateStatus(float $finalScore): string
    {
        return $finalScore >= $this->getKKM() ? 'TUNTAS' : 'BELUM TUNTAS';
    }

    public function getStudentAssessment(int $siswaId, int $kelasId, int $mapelId, ?int $tahunAjaranId, ?int $semesterId): array
    {
        $kognitif = $this->calculateDomainAverage($siswaId, $kelasId, $mapelId, $tahunAjaranId, $semesterId, 'kognitif');
        $afektif = $this->calculateDomainAverage($siswaId, $kelasId, $mapelId, $tahunAjaranId, $semesterId, 'afektif');
        $psikomotorik = $this->calculateDomainAverage($siswaId, $kelasId, $mapelId, $tahunAjaranId, $semesterId, 'psikomotorik');

        $finalScore = null;
        $predicate = null;
        $status = null;
        $isComplete = false;

        if ($kognitif !== null && $afektif !== null && $psikomotorik !== null) {
            $finalScore = $this->calculateFinalScore($kognitif, $afektif, $psikomotorik);
            $predicate = $this->calculatePredicate($finalScore);
            $status = $this->calculateStatus($finalScore);
            $isComplete = true;
        }

        return [
            'kognitif' => $kognitif,
            'afektif' => $afektif,
            'psikomotorik' => $psikomotorik,
            'final_score' => $finalScore,
            'predicate' => $predicate,
            'status' => $status,
            'is_complete' => $isComplete,
        ];
    }

    public function getClassStatistics(int $kelasId, int $mapelId, ?int $tahunAjaranId, ?int $semesterId): array
    {
        $students = Siswa::where('kelas_id', $kelasId)
            ->select('id', 'nis', 'nisn', 'nama')
            ->orderBy('nama')
            ->get();

        $assessments = [];
        $validFinalScores = [];

        foreach ($students as $student) {
            $assessment = $this->getStudentAssessment($student->id, $kelasId, $mapelId, $tahunAjaranId, $semesterId);
            $assessment['student'] = $student;
            $assessments[] = $assessment;

            if ($assessment['final_score'] !== null) {
                $validFinalScores[] = $assessment['final_score'];
            }
        }

        $stats = [
            'total_students' => $students->count(),
            'tuntas' => 0,
            'belum_tuntas' => 0,
            'rata_rata_kelas' => null,
            'nilai_tertinggi' => null,
            'nilai_terendah' => null,
        ];

        if (!empty($validFinalScores)) {
            $stats['rata_rata_kelas'] = round(array_sum($validFinalScores) / count($validFinalScores), 2);
            $stats['nilai_tertinggi'] = round(max($validFinalScores), 2);
            $stats['nilai_terendah'] = round(min($validFinalScores), 2);
        }

        foreach ($assessments as $assessment) {
            if ($assessment['status'] === 'TUNTAS') {
                $stats['tuntas']++;
            } elseif ($assessment['status'] === 'BELUM TUNTAS') {
                $stats['belum_tuntas']++;
            }
        }

        return [
            'stats' => $stats,
            'assessments' => $assessments,
        ];
    }

    public function getDomainDetails(int $siswaId, int $kelasId, int $mapelId, ?int $tahunAjaranId, ?int $semesterId, string $domain): array
    {
        $components = DB::table('nilai_harian')
            ->join('komponen_nilai', 'nilai_harian.komponen_id', '=', 'komponen_nilai.id')
            ->where('nilai_harian.siswa_id', $siswaId)
            ->where('nilai_harian.kelas_id', $kelasId)
            ->where('nilai_harian.mapel_id', $mapelId)
            ->where('komponen_nilai.domain', $domain)
            ->whereNotNull('nilai_harian.nilai')
            ->select(
                'komponen_nilai.id',
                'komponen_nilai.nama_komponen',
                'komponen_nilai.bobot',
                'nilai_harian.nilai',
                'nilai_harian.tanggal'
            );

        if ($tahunAjaranId) {
            $components->where('nilai_harian.tahun_ajaran_id', $tahunAjaranId);
        }

        if ($semesterId) {
            $components->where('nilai_harian.semester_id', $semesterId);
        }

        $components = $components->orderBy('nilai_harian.tanggal')->get();

        $values = [];
        $weightedSum = 0;
        $totalBobot = 0;

        foreach ($components as $component) {
            $values[] = [
                'nama_komponen' => $component->nama_komponen,
                'bobot' => (float) $component->bobot,
                'nilai' => (float) $component->nilai,
                'tanggal' => $component->tanggal,
            ];

            $weightedSum += (float) $component->nilai * (float) $component->bobot;
            $totalBobot += (float) $component->bobot;
        }

        $rataRata = $totalBobot > 0 ? round($weightedSum / $totalBobot, 2) : null;

        return [
            'domain' => $domain,
            'components' => $values,
            'rata_rata' => $rataRata,
            'total_bobot' => $totalBobot,
        ];
    }

    public function validateWeights(array $weights): bool
    {
        $total = array_sum($weights);

        return abs($total - 1.0) < 0.001;
    }
}
