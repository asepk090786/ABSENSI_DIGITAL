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
                'GURU001',
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
                'GURU002',
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
            'kode_guru',
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
            'C' => 15,
            'D' => 20,
            'E' => 25,
            'F' => 15,
            'G' => 15,
            'H' => 15,
            'I' => 30,
            'J' => 20,
            'K' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Tambah instruksi di column O (baris 1)
                $instructions = [
                    'PETUNJUK PENGISIAN:',
                    '1. no_id: Nomor urut (bisa dikosongkan)',
                    '2. nama: WAJIB diisi',
                    '3. kode_guru: Opsional, unik. GURU001',
                    '4. nip: Opsional',
                    '5. email: WAJIB diisi, unik',
                    '6. telepon: Opsional',
                    '7. tanggal_lahir: YYYY-MM-DD atau DD/MM/YYYY',
                    '8. jenis_kelamin: WAJIB diisi, L atau P',
                    '9. alamat: Opsional',
                    '10. username: WAJIB diisi, unik',
                    '11. password: WAJIB diisi, min 6 karakter',
                ];
                
                foreach ($instructions as $index => $instruction) {
                    $row = 1 + $index;
                    $sheet->setCellValue('O' . $row, $instruction);
                    $sheet->getStyle('O' . $row)->getFont()->setSize(9)->setItalic(true);
                    $sheet->getColumnDimension('O')->setWidth(40);
                }
            },
        ];
    }
}
