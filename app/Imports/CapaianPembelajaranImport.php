<?php

namespace App\Imports;

use App\Models\CapaianPembelajaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CapaianPembelajaranImport implements ToCollection, WithHeadingRow
{
    protected array $errors = [];
    protected ?int $userId = null;
    protected bool $isGuruMapel = false;
    protected bool $hasUserIdColumn = false;

    public function __construct()
    {
        $this->userId = auth()->id();
        $this->hasUserIdColumn = Schema::hasColumn('capaian_pembelajarans', 'user_id');
    }

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $payload = [
                'no' => $row['no'] ?? null,
                'nama_capaian_pembelajaran' => trim((string) ($row['nama_capaian_pembelajaran'] ?? '')),
                'fase' => trim((string) ($row['fase'] ?? '')),
                'deskripsi' => trim((string) ($row['deskripsi'] ?? '')),
                'tujuan_pembelajaran' => trim((string) ($row['tujuan_pembelajaran'] ?? '')),
                'alur_tujuan_pembelajaran' => trim((string) ($row['alur_tujuan_pembelajaran'] ?? '')),
                'indikator_kriteria' => trim((string) ($row['indikator_kriteria'] ?? '')),
            ];

            $validator = Validator::make($payload, [
                'nama_capaian_pembelajaran' => 'required|string|max:191',
                'fase' => 'nullable|string|in:A,B,C,D,E,F',
                'deskripsi' => 'nullable|string',
                'tujuan_pembelajaran' => 'nullable|string',
                'alur_tujuan_pembelajaran' => 'nullable|string',
                'indikator_kriteria' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $this->pushError($rowNumber, 'Validasi gagal: ' . implode(', ', $validator->errors()->all()));
                continue;
            }

            try {
                // Check if already exists by nama (scoped by user for teachers)
                $existingQuery = CapaianPembelajaran::where('nama_capaian_pembelajaran', $payload['nama_capaian_pembelajaran']);
                if ($this->hasUserIdColumn && auth()->check() && ! auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah']) && ! empty(auth()->user()->guru_id)) {
                    $existingQuery->where('user_id', $this->userId);
                }
                $existing = $existingQuery->first();
                
                if ($existing) {
                    // Update existing
                    $existing->update([
                        'fase' => $payload['fase'] ?? $existing->fase,
                        'deskripsi' => $payload['deskripsi'] ?? $existing->deskripsi,
                        'tujuan_pembelajaran' => $payload['tujuan_pembelajaran'] ?? $existing->tujuan_pembelajaran,
                        'alur_tujuan_pembelajaran' => $payload['alur_tujuan_pembelajaran'] ?? $existing->alur_tujuan_pembelajaran,
                        'indikator_kriteria' => $payload['indikator_kriteria'] ?? $existing->indikator_kriteria,
                    ]);
                } else {
                    // Create new
                    $createPayload = $payload;
                    if ($this->hasUserIdColumn && auth()->check() && ! auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah']) && ! empty(auth()->user()->guru_id)) {
                        $createPayload['user_id'] = $this->userId;
                    }

                    CapaianPembelajaran::create($createPayload);
                }
            } catch (\Exception $e) {
                $this->pushError($rowNumber, 'Error: ' . $e->getMessage());
            }
        }

        if (!empty($this->errors)) {
            session()->flash('import_errors', $this->errors);
        }
    }

    protected function pushError($rowNumber, $message)
    {
        $this->errors[] = "Baris $rowNumber: $message";
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
