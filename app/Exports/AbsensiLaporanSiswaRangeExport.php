<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AbsensiLaporanSiswaRangeExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private int $rowNumber = 1;
    private Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'NO',
            'NAMA SISWA',
            'KELAS',
            'NIS',
            'NISN',
            'HADIR',
            'TERLAMBAT',
            'SAKIT',
            'IZIN',
            'ALPA',
            'TOTAL HARI',
        ];
    }

    public function map($row): array
    {
        return [
            $this->rowNumber++,
            $row->nama_siswa ?? '-',
            $row->nama_kelas ?? '-',
            $row->nis ?? '-',
            $row->nisn ?? '-',
            $row->hadir_count ?? 0,
            $row->terlambat_count ?? 0,
            $row->sakit_count ?? 0,
            $row->izin_count ?? 0,
            $row->alpa_count ?? 0,
            $row->total_days ?? 0,
        ];
    }
}
