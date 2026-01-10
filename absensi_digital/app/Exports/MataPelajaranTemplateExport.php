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

class MataPelajaranTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function collection()
    {
        return collect([
            [1, 'Matematika', 'MTK'],
            [2, 'Bahasa Indonesia', 'BINDO'],
        ]);
    }

    public function headings(): array
    {
        return ['No', 'Mata Pelajaran', 'Kode Pelajaran'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 8, 'B' => 24, 'C' => 20];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $instructions = [
                    'PETUNJUK:',
                    '1. Kolom No hanya untuk referensi baris (opsional).',
                    '2. Isi Mata Pelajaran (wajib) dan Kode Pelajaran (disarankan unik).',
                    '3. Jika Kode Pelajaran sudah ada, import akan memperbarui nama mapel.',
                ];
                $startRow = 6;
                foreach ($instructions as $i => $text) {
                    $row = $startRow + $i;
                    $sheet->setCellValue('A'.$row, $text);
                    $sheet->mergeCells('A'.$row.':C'.$row);
                    $sheet->getStyle('A'.$row)->getFont()->setSize(9)->setItalic(true);
                }
            },
        ];
    }
}
