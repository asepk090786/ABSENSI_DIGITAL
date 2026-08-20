<?php

namespace App\Imports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NilaiHarianImport implements ToCollection, WithHeadingRow
{
    protected array $errors = [];
    protected int $kelasId;
    protected int $mapelId;
    protected int $rencanaId;
    protected ?int $komponenId;
    protected string $tanggal;
    protected int $guruId;
    protected int $tahunAjaranId;
    protected int $semesterId;

    public function __construct(
        int $kelasId,
        int $mapelId,
        int $rencanaId,
        string $tanggal,
        int $guruId,
        int $tahunAjaranId,
        int $semesterId,
        ?int $komponenId = null
    ) {
        $this->kelasId = $kelasId;
        $this->mapelId = $mapelId;
        $this->rencanaId = $rencanaId;
        $this->komponenId = $komponenId;
        $this->tanggal = $tanggal;
        $this->guruId = $guruId;
        $this->tahunAjaranId = $tahunAjaranId;
        $this->semesterId = $semesterId;
    }

    public function collection(Collection $rows): void
    {
        $students = Siswa::where('kelas_id', $this->kelasId)
            ->get(['id', 'nis', 'nisn', 'nama']);

        $studentsByNis = [];
        $studentsByNisn = [];
        $studentsByName = [];
        $nameCounts = [];

        foreach ($students as $student) {
            $normalizedNis = $this->normalizeIdentity((string) ($student->nis ?? ''));
            $normalizedNisn = $this->normalizeIdentity((string) ($student->nisn ?? ''));
            $normalizedName = $this->normalizeName((string) ($student->nama ?? ''));

            if ($normalizedNis !== '' && !isset($studentsByNis[$normalizedNis])) {
                $studentsByNis[$normalizedNis] = $student;
            }

            if ($normalizedNisn !== '' && !isset($studentsByNisn[$normalizedNisn])) {
                $studentsByNisn[$normalizedNisn] = $student;
            }

            if ($normalizedName !== '') {
                if (!isset($nameCounts[$normalizedName])) {
                    $nameCounts[$normalizedName] = 0;
                }
                $nameCounts[$normalizedName]++;

                if (!isset($studentsByName[$normalizedName])) {
                    $studentsByName[$normalizedName] = $student;
                }
            }
        }

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $normalizedNis = $this->normalizeIdentity((string) ($row['nis'] ?? ''));
            $normalizedNisn = $this->normalizeIdentity((string) ($row['nisn'] ?? ''));
            $normalizedName = $this->normalizeName((string) ($row['nama'] ?? ''));

            $payload = [
                'nis' => $normalizedNis,
                'nisn' => $normalizedNisn,
                'nama' => $normalizedName,
                'nilai' => $row['nilai'] ?? null,
            ];

            $validator = Validator::make($payload, [
                'nis' => 'nullable|string',
                'nisn' => 'nullable|string',
                'nama' => 'nullable|string',
                'nilai' => 'nullable|numeric|min:0|max:100',
            ]);

            if ($validator->fails()) {
                $this->pushError($rowNumber, 'Validasi gagal: ' . implode(', ', $validator->errors()->all()));
                continue;
            }

            if ($payload['nis'] === '' && $payload['nisn'] === '' && $payload['nama'] === '') {
                continue;
            }

            $siswa = null;

            if ($payload['nis'] !== '' && isset($studentsByNis[$payload['nis']])) {
                $siswa = $studentsByNis[$payload['nis']];
            }

            if (!$siswa && $payload['nisn'] !== '' && isset($studentsByNisn[$payload['nisn']])) {
                $siswa = $studentsByNisn[$payload['nisn']];
            }

            if (!$siswa && $payload['nama'] !== '' && isset($studentsByName[$payload['nama']]) && ($nameCounts[$payload['nama']] ?? 0) === 1) {
                $siswa = $studentsByName[$payload['nama']];
            }

            if (!$siswa) {
                $this->pushError($rowNumber, 'Siswa tidak ditemukan pada kelas ini.');
                continue;
            }

            $now = now();
            $existing = DB::table('nilai_harian')
                ->where('siswa_id', $siswa->id)
                ->where('kelas_id', $this->kelasId)
                ->where('mapel_id', $this->mapelId)
                ->where('rencana_pembelajaran_id', $this->rencanaId)
                ->whereDate('tanggal', $this->tanggal)
                ->where('tahun_ajaran_id', $this->tahunAjaranId)
                ->where('semester_id', $this->semesterId)
                ->when($this->komponenId !== null, function ($query) {
                    $query->where('komponen_id', $this->komponenId);
                }, function ($query) {
                    $query->whereNull('komponen_id');
                })
                ->first();

            if ($existing) {
                DB::table('nilai_harian')
                    ->where('id', $existing->id)
                    ->update([
                        'nilai' => $payload['nilai'],
                        'guru_id' => $this->guruId,
                        'tahun_ajaran_id' => $this->tahunAjaranId,
                        'semester_id' => $this->semesterId,
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('nilai_harian')->insert([
                    'siswa_id' => $siswa->id,
                    'guru_id' => $this->guruId,
                    'kelas_id' => $this->kelasId,
                    'mapel_id' => $this->mapelId,
                    'komponen_id' => $this->komponenId,
                    'rencana_pembelajaran_id' => $this->rencanaId,
                    'tanggal' => $this->tanggal,
                    'nilai' => $payload['nilai'],
                    'tahun_ajaran_id' => $this->tahunAjaranId,
                    'semester_id' => $this->semesterId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function pushError(int $row, string $message): void
    {
        $this->errors[] = 'Baris ' . $row . ': ' . $message;
    }

    protected function normalizeIdentity(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $value = ltrim($value, "'’`");
        $value = str_replace("\u{00A0}", ' ', $value);
        $value = preg_replace('/\s+/u', '', (string) $value);

        if (is_numeric($value)) {
            if (stripos((string) $value, 'e') !== false) {
                $value = sprintf('%.0f', (float) $value);
            } elseif (strpos((string) $value, '.') !== false) {
                $value = rtrim(rtrim((string) $value, '0'), '.');
            }
        }

        return trim((string) $value);
    }

    protected function normalizeName(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        $value = str_replace("\u{00A0}", ' ', $value);
        $value = preg_replace('/\s+/u', ' ', (string) $value);

        return mb_strtolower(trim((string) $value));
    }
}