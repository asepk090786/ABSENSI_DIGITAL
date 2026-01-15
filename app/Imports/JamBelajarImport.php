<?php

namespace App\Imports;

use App\Models\JamBelajar;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class JamBelajarImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    private $errors = [];
    private $successCount = 0;
    private $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    private $updateMode = false;

    public function __construct($updateMode = false)
    {
        $this->updateMode = $updateMode;
    }

    public function model(array $row)
    {
        $rowNum = count($this->errors) + $this->successCount + 2;

        // Validate required fields
        if (empty($row['hari']) || empty($row['jam_ke']) || empty($row['jam_mulai']) || empty($row['jam_selesai']) || empty($row['jenis'])) {
            $this->errors[] = "Baris $rowNum: Semua kolom harus diisi";
            return null;
        }

        // Validate day
        if (!in_array($row['hari'], $this->days)) {
            $this->errors[] = "Baris $rowNum: Hari '{$row['hari']}' tidak valid. Gunakan: " . implode(', ', $this->days);
            return null;
        }

        // Validate urutan
        if (!is_numeric($row['jam_ke']) || $row['jam_ke'] < 1) {
            $this->errors[] = "Baris $rowNum: Jam Ke harus berupa angka positif";
            return null;
        }

        $urutan = (int)$row['jam_ke'];

        // Validate times
        if (!preg_match('/^\d{2}:\d{2}$/', $row['jam_mulai'])) {
            $this->errors[] = "Baris $rowNum: Jam Mulai harus format HH:MM";
            return null;
        }
        if (!preg_match('/^\d{2}:\d{2}$/', $row['jam_selesai'])) {
            $this->errors[] = "Baris $rowNum: Jam Selesai harus format HH:MM";
            return null;
        }

        // Validate time range
        if ($row['jam_mulai'] >= $row['jam_selesai']) {
            $this->errors[] = "Baris $rowNum: Jam Mulai harus lebih kecil dari Jam Selesai";
            return null;
        }

        // Validasi kode kegiatan (untuk jenis selain KBM)
        $kodeKegiatan = $row['jenis'];
        if (strtoupper($kodeKegiatan) !== 'KBM') {
            $kegiatan = \App\Models\Kegiatan::where('kode_kegiatan', $kodeKegiatan)->first();
            if (!$kegiatan) {
                $this->errors[] = "Baris $rowNum: Kode Kegiatan '{$kodeKegiatan}' tidak ditemukan di master kegiatan.";
                return null;
            }
            $jenis = $kegiatan->nama_kegiatan;
        } else {
            $jenis = 'KBM';
        }

        // Check if already exists
        $existing = JamBelajar::where('hari', $row['hari'])
            ->where('urutan', $urutan)
            ->first();

        if ($existing) {
            if ($this->updateMode) {
                // Update data lama dengan yang baru
                $existing->jam_mulai = $row['jam_mulai'];
                $existing->jam_selesai = $row['jam_selesai'];
                $existing->jenis = $jenis;
                $existing->save();
                $this->successCount++;
                return null; // Tidak insert baru
            } else {
                $this->errors[] = "Baris $rowNum: Sesi Jam Ke-{$urutan} untuk hari {$row['hari']} sudah ada";
                return null;
            }
        }

        $this->successCount++;

        return new JamBelajar([
            'hari' => $row['hari'],
            'urutan' => $urutan,
            'jam_mulai' => $row['jam_mulai'],
            'jam_selesai' => $row['jam_selesai'],
            'jenis' => $jenis, // Simpan nama kegiatan, bukan kode
        ]);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }
}
