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

class GuruTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $mode;

    public function __construct(?string $mode = 'create')
    {
        $this->mode = in_array($mode, ['create', 'update'], true) ? $mode : 'create';
    }

    public function collection()
    {
        return collect([
            [
                1,
                '',
                'Budi Santoso',
                'GURU001',
                '198501012010011001',
                'Pembina',
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
                '',
                'Siti Aminah',
                'GURU002',
                '199003152015022002',
                'Penata',
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
            'id_guru',
            'nama',
            'kode_guru',
            'nip',
            'pangkat_golongan',
            'email',
            'telepon',
            'tanggal_lahir',
            'jenis_kelamin',
            'alamat',
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
            'B' => 10,
            'C' => 25,
            'D' => 15,
            'E' => 20,
            'F' => 18,
            'G' => 25,
            'H' => 15,
            'I' => 15,
            'J' => 15,
            'K' => 30,
            'L' => 20,
            'M' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $instructions = [
                    'PETUNJUK PENGISIAN:',
                    '1. no_id: Nomor urut (auto), boleh dikosongkan.',
                    '2. id_guru: UNTUK UPDATE SAJA. Isi ID guru yang akan diubah. Kosongkan untuk tambah baru.',
                    '3. nama: WAJIB diisi (25 karakter max).',
                    '4. kode_guru: Opsional, unik. Contoh: GURU001',
                    '5. nip: Opsional, unik.',
                    '6. pangkat_golongan: Opsional. Contoh: Pembina, Penata, dll.',
                    '7. email: WAJIB diisi dan unik, valid format email.',
                    '8. telepon: Opsional, maks 30 karakter.',
                    '9. tanggal_lahir: Format YYYY-MM-DD. Contoh: 1985-01-01',
                    '10. jenis_kelamin: WAJIB diisi. Nilai: L atau P',
                    '11. alamat: Opsional, text panjang boleh.',
                    '12. username: WAJIB untuk tambah baru, opsional saat update. Unik di sistem.',
                    '13. password: WAJIB untuk tambah baru (min 6 karakter), opsional saat update.',
                    '',
                    'MODE UPDATE:',
                    '- Gunakan kolom id_guru atau identitas lain (kode_guru/nip/email) untuk match.',
                    '- Nama, email, dan jenis_kelamin wajib saat update.',
                    '- Kosongkan kolom yang tidak ingin diubah atau isi ulang data saat ini.',
                ];

                foreach ($instructions as $index => $instruction) {
                    $row = 1 + $index;
                    $sheet->setCellValue('O' . $row, $instruction);
                    $sheet->getStyle('O' . $row)->getFont()->setSize(9)->setItalic(true);
                    $sheet->getColumnDimension('O')->setWidth(50);
                }

                if ($this->mode === 'update') {
                    $sheet->setCellValue('Q1', 'TEMPLATE UPDATE');
                    $sheet->getStyle('Q1')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF0000'));
                }
            },
        ];
    }
}
