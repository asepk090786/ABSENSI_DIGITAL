<?php

namespace App\Imports;

use App\Models\MataPelajaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MataPelajaranImport implements ToCollection, WithHeadingRow
{
    protected array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $payload = [
                'no' => $row['no'] ?? null,
                'nama_mapel' => trim((string) ($row['mata_pelajaran'] ?? '')),
                'kode_mapel' => trim((string) ($row['kode_pelajaran'] ?? '')),
            ];

            $validator = Validator::make($payload, [
                'nama_mapel' => 'required|string|max:255',
                'kode_mapel' => 'nullable|string|max:50',
            ]);

            if ($validator->fails()) {
                $this->pushError($rowNumber, 'Validasi gagal: ' . implode(', ', $validator->errors()->all()));
                continue;
            }

            try {
                if ($payload['kode_mapel'] !== '') {
                    // Update by kode_mapel if exists, else create
                    $existing = MataPelajaran::where('kode_mapel', $payload['kode_mapel'])->first();
                    if ($existing) {
                        $existing->update(['nama_mapel' => $payload['nama_mapel']]);
                        continue;
                    }
                }

                // Also prevent duplicate nama_mapel
                $dupName = MataPelajaran::where('nama_mapel', $payload['nama_mapel'])->exists();
                if ($dupName) {
                    $this->pushError($rowNumber, 'Nama mata pelajaran sudah ada: ' . $payload['nama_mapel']);
                    continue;
                }

                MataPelajaran::create([
                    'nama_mapel' => $payload['nama_mapel'],
                    'kode_mapel' => $payload['kode_mapel'] !== '' ? $payload['kode_mapel'] : null,
                ]);
            } catch (\Exception $e) {
                $this->pushError($rowNumber, 'Error: ' . $e->getMessage());
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
