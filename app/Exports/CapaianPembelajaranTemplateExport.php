<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CapaianPembelajaranTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['1', 'Menganalisis dan mengevaluasi fenomena sosial', 'E', 'Capaian pembelajaran contoh untuk fase E'],
            ['2', 'Memahami konsep-konsep dasar ekonomi', 'E', 'Capaian pembelajaran contoh untuk fase E'],
        ];
    }

    public function headings(): array
    {
        return ['No', 'Nama Capaian Pembelajaran', 'Fase', 'Deskripsi'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']]],
            2 => ['font' => ['color' => ['rgb' => '808080']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']]],
            3 => ['font' => ['color' => ['rgb' => '808080']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']]],
        ];
    }
}
