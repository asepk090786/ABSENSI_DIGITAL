<?php

namespace App\Imports;

use App\Support\NilaiInputNormalizer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NilaiInputGridImport implements ToCollection, WithHeadingRow
{
    protected array $errors = [];
    protected array $columnCodes = [];
    protected array $preparedRows = [];
    protected array $previewRows = [];

    public function __construct(
        private array $students,
        private array $sessions,
        private array $context
    ) {
    }

    public function collection(Collection $rows): void
    {
        if ($rows->isEmpty()) {
            $this->errors[] = 'File tidak berisi baris nilai.';
            return;
        }

        $firstRow = $rows->first();
        $headerKeys = array_keys(is_array($firstRow) ? $firstRow : $firstRow->toArray());
        foreach ($headerKeys as $header) {
            if (preg_match('/^a(\d+)$/i', (string) $header, $matches)) {
                $this->columnCodes[(int) $matches[1]] = 'A' . (int) $matches[1];
            }
        }
        ksort($this->columnCodes);
        $this->columnCodes = array_values($this->columnCodes);

        if ($this->columnCodes === []) {
            $this->errors[] = 'Template harus memiliki minimal satu kolom penilaian A1.';
            return;
        }

        foreach ($this->columnCodes as $index => $columnCode) {
            if ($columnCode !== 'A' . ($index + 1)) {
                $this->errors[] = 'Kolom penilaian harus berurutan mulai dari A1 tanpa jeda.';
                return;
            }
        }

        $activeTypeCodes = array_values(array_unique(array_column($this->sessions, 'type_code')));
        if (count($this->getNewColumnCodes()) > 0 && count($activeTypeCodes) !== 1) {
            $this->errors[] = 'Kolom baru hanya dapat ditambahkan jika grid aktif memiliki satu jenis penilaian.';
            return;
        }

        $studentsByNumber = array_values($this->students);
        $studentsByName = [];
        foreach ($studentsByNumber as $student) {
            $name = $this->normalizeName((string) $student['nama']);
            $studentsByName[$name][] = $student;
        }

        $expectedTypeCodes = implode(';', $activeTypeCodes);
        $seenStudents = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $rowName = $this->normalizeName((string) ($row['nama'] ?? ''));
            $rawNumber = trim((string) ($row['no'] ?? ''));
            $rowNumberValue = $rawNumber !== '' ? filter_var($rawNumber, FILTER_VALIDATE_INT) : false;

            if ($rowName === '' && $rowNumberValue === false) {
                continue;
            }

            $student = $rowNumberValue !== false && $rowNumberValue > 0
                ? ($studentsByNumber[$rowNumberValue - 1] ?? null)
                : null;

            if (! $student || $this->normalizeName((string) $student['nama']) !== $rowName) {
                $matches = $studentsByName[$rowName] ?? [];
                $student = count($matches) === 1 ? $matches[0] : null;
            }

            if (! $student) {
                $this->pushError($rowNumber, 'No dan Nama tidak cocok dengan daftar siswa pada template.');
                continue;
            }

            if (isset($seenStudents[$student['id']])) {
                $this->pushError($rowNumber, 'Siswa tercantum lebih dari satu kali.');
                continue;
            }
            $seenStudents[$student['id']] = true;

            $providedTypeCodes = preg_replace('/\s+/', '', (string) ($row['jenis_penilaian'] ?? ''));
            if ($providedTypeCodes !== $expectedTypeCodes) {
                $this->pushError($rowNumber, 'Kode Jenis Penilaian tidak sesuai dengan lembar Keterangan.');
                continue;
            }

            $providedClass = $this->normalizeName((string) ($row['kelas'] ?? ''));
            if ($providedClass !== $this->normalizeName((string) $this->context['class_name'])) {
                $this->pushError($rowNumber, 'Nama kelas tidak sesuai dengan template.');
                continue;
            }

            $scores = [];
            $rowHasError = false;
            foreach ($this->columnCodes as $columnCode) {
                $columnKey = strtolower($columnCode);
                $rawScore = $row[$columnKey] ?? null;
                if ($rawScore === null || trim((string) $rawScore) === '') {
                    $scores[$columnCode] = null;
                    continue;
                }

                $score = NilaiInputNormalizer::normalize($rawScore);
                if ($score === null || $score < 0 || $score > 100) {
                    $this->pushError($rowNumber, 'Nilai pada kolom ' . $columnCode . ' harus berupa angka 0 sampai 100.');
                    $rowHasError = true;
                    continue;
                }

                $scores[$columnCode] = $score;
            }

            if ($rowHasError) {
                continue;
            }

            $preparedRow = [
                'student' => $student,
                'scores' => $scores,
            ];
            $this->preparedRows[] = $preparedRow;
            $this->previewRows[] = [
                'no' => $rowNumberValue ?: array_search($student, $studentsByNumber, true) + 1,
                'nama' => $student['nama'],
                'jenis_penilaian' => $providedTypeCodes,
                'kelas' => $this->context['class_name'],
                'scores' => $scores,
            ];
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getColumnCodes(): array
    {
        return $this->columnCodes;
    }

    public function getNewColumnCodes(): array
    {
        $activeColumnCodes = array_column($this->sessions, 'column_code');

        return array_values(array_diff($this->columnCodes, $activeColumnCodes));
    }

    public function getPreviewRows(int $limit = 10): array
    {
        return array_slice($this->previewRows, 0, $limit);
    }

    public function getPreparedRowCount(): int
    {
        return count($this->preparedRows);
    }

    public function getOverwriteCount(): int
    {
        $sessionsByColumn = [];
        foreach ($this->sessions as $session) {
            $sessionsByColumn[$session['column_code']] = $session;
        }

        $overwriteCount = 0;
        foreach ($this->preparedRows as $row) {
            foreach ($row['scores'] as $columnCode => $score) {
                if ($score === null || ! isset($sessionsByColumn[$columnCode])) {
                    continue;
                }

                $existing = $sessionsByColumn[$columnCode]['scores'][$row['student']['id']] ?? null;
                if ($existing !== null && (float) $existing !== (float) $score) {
                    $overwriteCount++;
                }
            }
        }

        return $overwriteCount;
    }

    public function save(array $sessions): void
    {
        $sessionsByColumn = [];
        foreach ($sessions as $session) {
            $sessionsByColumn[$session['column_code']] = $session;
        }

        foreach ($this->preparedRows as $row) {
            foreach ($row['scores'] as $columnCode => $score) {
                if ($score === null) {
                    continue;
                }

                if (! isset($sessionsByColumn[$columnCode])) {
                    $this->errors[] = 'Kolom ' . $columnCode . ' tidak memiliki sesi penilaian.';
                    continue;
                }

                $this->saveScore($row['student'], $sessionsByColumn[$columnCode], (float) $score);
            }
        }
    }

    protected function saveScore(array $student, array $session, float $score): void
    {
        $context = $this->context;
        $query = DB::table('nilai_harian')
            ->where('siswa_id', $student['id'])
            ->where('kelas_id', $context['kelas_id'])
            ->where('mapel_id', $context['mapel_id'])
            ->whereDate('tanggal', $context['tanggal'])
            ->where('tahun_ajaran_id', $context['tahun_ajaran_id'])
            ->where('semester_id', $context['semester_id']);

        if ($session['rencana_pembelajaran_id'] !== null) {
            $query->where('rencana_pembelajaran_id', $session['rencana_pembelajaran_id']);
        } else {
            $query->whereNull('rencana_pembelajaran_id');
        }

        if ($session['komponen_id'] !== null) {
            $query->where('komponen_id', $session['komponen_id']);
        } else {
            $query->whereNull('komponen_id');
        }

        if ($session['assessment_uuid'] !== null) {
            $query->where('assessment_uuid', $session['assessment_uuid']);
        } else {
            $query->whereNull('assessment_uuid');
        }

        $now = now();
        $existing = $query->first();
        if ($existing) {
            DB::table('nilai_harian')->where('id', $existing->id)->update([
                'nilai' => $score,
                'updated_at' => $now,
            ]);

            return;
        }

        DB::table('nilai_harian')->insert([
            'assessment_uuid' => $session['assessment_uuid'],
            'siswa_id' => $student['id'],
            'guru_id' => $context['guru_id'],
            'kelas_id' => $context['kelas_id'],
            'mapel_id' => $context['mapel_id'],
            'komponen_id' => $session['komponen_id'],
            'rencana_pembelajaran_id' => $session['rencana_pembelajaran_id'],
            'materi_pelajaran' => $session['materi_pelajaran'],
            'jenis_penilaian' => $session['jenis_penilaian'],
            'tanggal' => $context['tanggal'],
            'nilai' => $score,
            'tahun_ajaran_id' => $context['tahun_ajaran_id'],
            'semester_id' => $context['semester_id'],
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    protected function normalizeName(string $value): string
    {
        $value = str_replace("\u{00A0}", ' ', $value);
        $value = preg_replace('/\s+/u', ' ', trim($value));

        return mb_strtolower((string) $value);
    }

    protected function pushError(int $row, string $message): void
    {
        $this->errors[] = 'Baris ' . $row . ': ' . $message;
    }
}