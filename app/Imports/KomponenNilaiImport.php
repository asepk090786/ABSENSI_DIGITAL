<?php

namespace App\Imports;

use App\Models\KomponenNilai;
use App\Models\CapaianPembelajaran;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KomponenNilaiImport implements ToCollection, WithHeadingRow
{
    protected array $errors = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            $cpName = trim((string) ($row['capaian_pembelajaran'] ?? ''));
            $capaianId = null;

            // Try to find CP by nama
            if (!empty($cpName)) {
                $cp = CapaianPembelajaran::where('nama_capaian_pembelajaran', $cpName)->first();
                if ($cp) {
                    if (auth()->check() && ! auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah', 'Pengawas Pembina']) && ! empty(auth()->user()->guru_id)) {
                        $allowed = $cp->user_id === null || $cp->user_id === auth()->id();
                        if (! $allowed) {
                            $this->pushError($rowNumber, 'CP tidak ditemukan atau bukan milik Anda: ' . $cpName);
                            continue;
                        }
                    }
                    $capaianId = $cp->id;
                } else {
                    $this->pushError($rowNumber, 'CP tidak ditemukan: ' . $cpName);
                    continue;
                }
            }

            $payload = [
                'guru_id' => auth()->check() && ! auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah', 'Pengawas Pembina'])
                    ? auth()->user()->guru_id
                    : null,
                'capaian_pembelajaran_id' => $capaianId,
                'no' => $row['no'] ?? null,
                'nama_komponen' => trim((string) ($row['nama_komponen'] ?? '')),
                'bobot' => !empty($row['bobot']) ? floatval($row['bobot']) : null,
                'capaian_pembelajaran' => trim((string) ($row['capaian_pembelajaran_detail'] ?? '')),
                'tujuan_pembelajaran' => trim((string) ($row['tujuan_pembelajaran'] ?? '')),
                'alur_tujuan_pembelajaran' => trim((string) ($row['alur_tujuan_pembelajaran'] ?? '')),
                'indikator_kriteria' => trim((string) ($row['indikator_kriteria'] ?? '')),
            ];

            $validator = Validator::make($payload, [
                'nama_komponen' => 'required|string|max:255',
                'bobot' => 'nullable|numeric|min:0|max:100',
                'capaian_pembelajaran' => 'nullable|string',
                'tujuan_pembelajaran' => 'nullable|string',
                'alur_tujuan_pembelajaran' => 'nullable|string',
                'indikator_kriteria' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                $this->pushError($rowNumber, 'Validasi gagal: ' . implode(', ', $validator->errors()->all()));
                continue;
            }

            try {
                // Check if exists by nama_komponen
                $existingQuery = KomponenNilai::where('nama_komponen', $payload['nama_komponen']);
                if ($payload['guru_id']) {
                    $existingQuery->where('guru_id', $payload['guru_id']);
                }
                $existing = $existingQuery->first();
                
                if ($existing) {
                    if (auth()->check() && ! auth()->user()->hasAnyRole(['Admin', 'Kepala Sekolah', 'Pengawas Pembina']) && ! empty(auth()->user()->guru_id)) {
                        $existingCp = $existing->capaianPembelajaran;
                        $allowed = $existingCp === null || $existingCp->user_id === null || $existingCp->user_id === auth()->id();
                        if (!$existingCp || ! $allowed) {
                            $this->pushError($rowNumber, 'Komponen penilaian ini bukan milik Anda: ' . $payload['nama_komponen']);
                            continue;
                        }
                    }
                    $existing->update($payload);
                } else {
                    KomponenNilai::create($payload);
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
