<?php

namespace App\Exports;

use App\Models\MataPelajaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MataPelajaranExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private int $no = 1;

    public function collection()
    {
        return MataPelajaran::orderBy('nama_mapel')->get();
    }

    public function headings(): array
    {
        return ['No', 'Mata Pelajaran', 'Kode Pelajaran'];
    }

    public function map($item): array
    {
        return [
            $this->no++,
            $item->nama_mapel,
            $item->kode_mapel ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}
