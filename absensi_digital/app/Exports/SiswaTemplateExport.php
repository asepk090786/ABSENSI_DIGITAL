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

class SiswaTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function collection()
    {
        return collect([
            [1, '2026001', '0065432101', 'Adi Pratama', 'L', 1, 'adi@example.com', 'adi.pratama', 'password123'],
            [2, '2026002', '0065432102', 'Sari Lestari', 'P', 1, 'sari@example.com', 'sari.lestari', 'password123'],
        ]);
    }

    public function headings(): array
    {
        return [
            'id',
            'nis',
            'nisn',
            'nama',
            'jenis_kelamin',
            'kelas_id',
            'email',
            'username',
            'password',
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
            'B' => 12,
            'C' => 14,
            'D' => 25,
            'E' => 14,
            'F' => 10,
            'G' => 28,
            'H' => 20,
            'I' => 18,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $startRow = 6;
                $instructions = [
                    'PETUNJUK PENGISIAN:',
                    '1. id: isi dengan ID siswa di database untuk memperbarui; kosongkan untuk menambah siswa baru.',
                    '2. nis & nisn: WAJIB diisi dan harus unik.',
                    '3. nama: WAJIB diisi.',
                    '4. jenis_kelamin: WAJIB, gunakan L atau P.',
                    '5. kelas_id: WAJIB, isi dengan ID kelas yang valid.',
                    '6. email: WAJIB diisi dan unik (juga menjadi email akun).',
                    '7. username: WAJIB diisi dan unik.',
                    '8. password: WAJIB, minimal 6 karakter.',
                ];

                foreach ($instructions as $index => $instruction) {
                    $row = $startRow + $index;
                    $sheet->setCellValue('A' . $row, $instruction);
                    $sheet->mergeCells('A' . $row . ':I' . $row);
                    $sheet->getStyle('A' . $row)->getFont()->setSize(9)->setItalic(true);
                }
            },
        ];
    }
}
