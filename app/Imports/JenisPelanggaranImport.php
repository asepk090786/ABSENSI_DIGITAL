<?php

namespace App\Imports;

use App\Models\JenisPelanggaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class JenisPelanggaranImport implements ToCollection, WithHeadingRow
{
    protected array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $payload = [
                'kode' => trim((string) ($row['kode'] ?? '')),
                'nama' => trim((string) ($row['nama_jenis_pelanggaran'] ?? '')),
                'poin_default' => (int) ($row['point_default'] ?? 0),
                'status' => strtolower(trim((string) ($row['status'] ?? 'aktif'))),
            ];

            $validator = Validator::make($payload, [
                'kode' => 'required|string|max:30',
                'nama' => 'required|string|max:150',
                'poin_default' => 'required|integer|min:0|max:1000',
                'status' => 'required|in:aktif,nonaktif',
            ]);

            if ($validator->fails()) {
                $this->pushError($rowNumber, 'Validasi gagal: ' . implode(', ', $validator->errors()->all()));
                continue;
            }

            try {
                $item = JenisPelanggaran::where('kode', $payload['kode'])->first();

                if ($item) {
                    $item->update([
                        'nama' => $payload['nama'],
                        'poin_default' => $payload['poin_default'],
                        'is_active' => $payload['status'] === 'aktif',
                    ]);
                } else {
                    JenisPelanggaran::create([
                        'kode' => $payload['kode'],
                        'nama' => $payload['nama'],
                        'poin_default' => $payload['poin_default'],
                        'is_active' => $payload['status'] === 'aktif',
                    ]);
                }
            } catch (\Exception $e) {
                $this->pushError($rowNumber, 'Error: ' . $e->getMessage());
            }
        }

        if (!empty($this->errors)) {
            session()->flash('import_errors', $this->errors);
        }
    }

    protected function pushError($rowNumber, $message): void
    {
        $this->errors[] = "Baris {$rowNumber}: {$message}";
    }
}
