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

class KelasSiswaTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function __construct(private int $kelasId)
    {
    }

    public function collection()
    {
        return collect([
            [1, '2026001', '0065432101', 'Adi Pratama', 'L', 'adi@example.com', 'adi.pratama', 'password123'],
            [2, '2026002', '0065432102', 'Sari Lestari', 'P', 'sari@example.com', 'sari.lestari', 'password123'],
        ]);
    }

    public function headings(): array
    {
        return ['id', 'nis', 'nisn', 'nama', 'jenis_kelamin', 'email', 'username', 'password'];
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
            'B' => 12,
            'C' => 14,
            'D' => 25,
            'E' => 14,
            'F' => 28,
            'G' => 20,
            'H' => 18,
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
                    '1. id: isi untuk update data; kosongkan untuk siswa baru.',
                    '2. nis & nisn: wajib diisi dan unik.',
                    '3. jenis_kelamin: wajib, gunakan L atau P.',
                    '4. email, username, password: wajib diisi.',
                    '5. kelas_id dikunci ke kelas ini (ID ' . $this->kelasId . ').',
                ];

                foreach ($instructions as $index => $instruction) {
                    $row = $startRow + $index;
                    $sheet->setCellValue('A' . $row, $instruction);
                    $sheet->mergeCells('A' . $row . ':H' . $row);
                    $sheet->getStyle('A' . $row)->getFont()->setSize(9)->setItalic(true);
                }
            },
        ];
    }
}
