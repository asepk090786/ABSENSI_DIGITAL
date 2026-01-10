<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class KelasTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function collection()
    {
        return collect([
            [1, 'Kelas 7A', 1],
            [2, 'Kelas 7B', 2],
        ]);
    }

    public function headings(): array
    {
        return [
            'id',
            'nama_kelas',
            'wali_kelas_id',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 20,
            'C' => 14,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $startRow = 6;
                $instructions = [
                    'PETUNJUK:',
                    '1. id: isi untuk update data; kosongkan untuk tambah kelas baru.',
                    '2. nama_kelas: wajib diisi dan harus unik.',
                    '3. wali_kelas_id: opsional, isi dengan ID guru yang valid.',
                ];

                foreach ($instructions as $index => $instruction) {
                    $row = $startRow + $index;
                    $sheet->setCellValue('A' . $row, $instruction);
                    $sheet->mergeCells('A' . $row . ':C' . $row);
                    $sheet->getStyle('A' . $row)->getFont()->setSize(9)->setItalic(true);
                }
            },
        ];
    }
}
