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

class GuruTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    public function collection()
    {
        // Return contoh data
        return collect([
            [
                1,
                'Budi Santoso',
                '198501012010011001',
                'budi@gmail.com',
                '081234567890',
                '1985-01-01',
                'L',
                'Jl. Merdeka No. 10',
                'budi.santoso',
                'password123'
            ],
            [
                2,
                'Siti Aminah',
                '199003152015022002',
                'siti@gmail.com',
                '081234567891',
                '1990-03-15',
                'P',
                'Jl. Sudirman No. 20',
                'siti.aminah',
                'password123'
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'no_id',
            'nama',
            'nip',
            'email',
            'telepon',
            'tanggal_lahir',
            'jenis_kelamin',
            'alamat',
            'username',
            'password'
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
            'B' => 25,
            'C' => 20,
            'D' => 25,
            'E' => 15,
            'F' => 15,
            'G' => 15,
            'H' => 30,
            'I' => 20,
            'J' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Tambah instruksi di bawah data
                $startRow = 5;
                $instructions = [
                    'PETUNJUK PENGISIAN:',
                    '1. no_id: Nomor urut (bisa dikosongkan, hanya untuk referensi)',
                    '2. nama: WAJIB diisi',
                    '3. nip: Opsional',
                    '4. email: WAJIB diisi, harus unik',
                    '5. telepon: Opsional',
                    '6. tanggal_lahir: Format YYYY-MM-DD atau DD/MM/YYYY',
                    '7. jenis_kelamin: WAJIB diisi, isi dengan L atau P',
                    '8. alamat: Opsional',
                    '9. username: WAJIB diisi, harus unik',
                    '10. password: WAJIB diisi, minimal 6 karakter',
                ];
                
                foreach ($instructions as $index => $instruction) {
                    $row = $startRow + $index;
                    $sheet->setCellValue('A' . $row, $instruction);
                    $sheet->mergeCells('A' . $row . ':J' . $row);
                    $sheet->getStyle('A' . $row)->getFont()->setSize(9)->setItalic(true);
                }
            },
        ];
    }
}
