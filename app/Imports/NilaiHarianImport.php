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
        int $semesterId
    ) {
        $this->kelasId = $kelasId;
        $this->mapelId = $mapelId;
        $this->rencanaId = $rencanaId;
        $this->tanggal = $tanggal;
        $this->guruId = $guruId;
        $this->tahunAjaranId = $tahunAjaranId;
        $this->semesterId = $semesterId;
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $payload = [
                'nis' => trim((string) ($row['nis'] ?? '')),
                'nisn' => trim((string) ($row['nisn'] ?? '')),
                'nilai' => $row['nilai'] ?? null,
            ];

            $validator = Validator::make($payload, [
                'nis' => 'nullable|string',
                'nisn' => 'nullable|string',
                'nilai' => 'nullable|numeric|min:0|max:100',
            ]);

            if ($validator->fails()) {
                $this->pushError($rowNumber, 'Validasi gagal: ' . implode(', ', $validator->errors()->all()));
                continue;
            }

            if ($payload['nis'] === '' && $payload['nisn'] === '') {
                $this->pushError($rowNumber, 'NIS atau NISN wajib diisi.');
                continue;
            }

            $siswa = Siswa::where('kelas_id', $this->kelasId)
                ->when($payload['nis'] !== '', function ($q) use ($payload) {
                    return $q->where('nis', $payload['nis']);
                })
                ->when($payload['nis'] === '' && $payload['nisn'] !== '', function ($q) use ($payload) {
                    return $q->where('nisn', $payload['nisn']);
                })
                ->first();

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
                ->first();

            if ($existing) {
                DB::table('nilai_harian')
                    ->where('id', $existing->id)
                    ->update([
                        'nilai' => $payload['nilai'],
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('nilai_harian')->insert([
                    'siswa_id' => $siswa->id,
                    'guru_id' => $this->guruId,
                    'kelas_id' => $this->kelasId,
                    'mapel_id' => $this->mapelId,
                    'komponen_id' => null,
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
}