<?php

namespace App\Imports;

use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KelasImport implements ToCollection, WithHeadingRow
{
    protected array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $payload = [
                'id' => $row['id'] ?? null,
                'nama_kelas' => trim((string) ($row['nama_kelas'] ?? '')),
                'wali_kelas_id' => $row['wali_kelas_id'] ?? null,
            ];

            $validator = Validator::make($payload, [
                'id' => 'nullable|integer|exists:kelas,id',
                'nama_kelas' => 'required|string|max:255',
                'wali_kelas_id' => 'nullable|integer|exists:guru,id',
            ]);

            if ($validator->fails()) {
                $this->pushError($rowNumber, 'Validasi gagal: ' . implode(', ', $validator->errors()->all()));
                continue;
            }

            // Unique check manual to allow update scenario
            $namaTaken = Kelas::where('nama_kelas', $payload['nama_kelas'])
                ->when($payload['id'], fn($q) => $q->where('id', '!=', $payload['id']))
                ->exists();
            if ($namaTaken) {
                $this->pushError($rowNumber, 'Nama kelas sudah digunakan.');
                continue;
            }

            $data = [
                'nama_kelas' => $payload['nama_kelas'],
                'wali_kelas_id' => $payload['wali_kelas_id'] ?: null,
            ];

            try {
                if ($payload['id']) {
                    $kelas = Kelas::find($payload['id']);
                    $kelas?->update($data);
                } else {
                    Kelas::create($data);
                }
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
