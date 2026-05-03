<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AbsensiLaporanSiswaHarianExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private int $rowNumber = 1;

    public function __construct(private readonly Collection $rows)
    {
    }

    public function collection(): Collection
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'NO',
            'TANGGAL',
            'KELAS',
            'NAMA SISWA',
            'NIS',
            'NISN',
            'STATUS HARIAN',
            'GURU PENGINPUT',
            'KETERANGAN',
        ];
    }

    public function map($row): array
    {
        return [
            $this->rowNumber++,
            !empty($row->tanggal) ? Carbon::parse($row->tanggal)->format('d/m/Y') : '-',
            $row->nama_kelas ?? '-',
            $row->nama_siswa ?? '-',
            $row->nis ?? '-',
            $row->nisn ?? '-',
            $row->status ?? '-',
            $row->nama_guru ?? '-',
            $row->keterangan ?? '-',
        ];
    }
}
