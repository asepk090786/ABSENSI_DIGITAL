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
            [1, 'Matematika', 'MTK', 'Umum'],
            [2, 'Bahasa Indonesia', 'BINDO', 'Umum'],
            [3, 'Muatan Lokal', 'MULOK', 'Mulok'],
        ]);
    }

    public function headings(): array
    {
        return ['No', 'Mata Pelajaran', 'Kode Pelajaran', 'Kategori'];
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
        return [
            'A' => 8,
            'B' => 24,
            'C' => 20,
            'D' => 18,
            'E' => 55,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $instructions = [
                    'PETUNJUK:',
                    '1. Kolom No opsional (boleh dikosongkan).',
                    '2. Isi Mata Pelajaran (wajib), Kode Pelajaran (unik disarankan), dan Kategori.',
                    '3. Jika Kode Pelajaran sudah ada, import akan memperbarui nama/kategori mapel.',
                    '4. Pilihan kategori: Umum, Jurusan, Pilihan, Tingkat lanjut, Mulok.',
                ];

                // Taruh petunjuk di kolom E (tidak mengganggu data utama)
                foreach ($instructions as $i => $text) {
                    $row = 1 + $i;
                    $sheet->setCellValue('E'.$row, $text);
                    $sheet->getStyle('E'.$row)->getFont()->setSize(9)->setItalic(true);
                    $sheet->getStyle('E'.$row)->getAlignment()->setWrapText(true);
                }
            },
        ];
    }
}
