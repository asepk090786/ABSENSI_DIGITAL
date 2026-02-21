<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JenisPelanggaranTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function headings(): array
    {
        return ['No', 'Kode', 'Nama Jenis Pelanggaran', 'Point Default', 'Status'];
    }

    public function array(): array
    {
        return [
            ['1', 'Terlambat', 'Terlambat Masuk Sekolah', '5', 'aktif'],
            ['2', 'Seragam', 'Seragam Tidak Lengkap', '10', 'aktif'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
            ],
            2 => [
                'font' => ['color' => ['rgb' => '808080']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']],
            ],
            3 => [
                'font' => ['color' => ['rgb' => '808080']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']],
            ],
        ];
    }
}
