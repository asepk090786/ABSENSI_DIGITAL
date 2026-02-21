<?php

namespace App\Exports;

use App\Models\JenisPelanggaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JenisPelanggaranExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private int $no = 1;

    public function collection()
    {
        return JenisPelanggaran::orderBy('nama')->get();
    }

    public function headings(): array
    {
        return ['No', 'Kode', 'Nama Jenis Pelanggaran', 'Point Default', 'Status'];
    }

    public function map($item): array
    {
        return [
            $this->no++,
            $item->kode,
            $item->nama,
            $item->poin_default,
            $item->is_active ? 'aktif' : 'nonaktif',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
            ],
        ];
    }
}
