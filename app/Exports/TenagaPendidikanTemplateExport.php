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

class TenagaPendidikanTemplateExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
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
                'Andi Pratama',
                '198601151990031001',
                'Tenaga Administrasi',
                'andi.pratama@gmail.com',
                '081234567890',
                '1986-01-15',
                'L',
                'Jl. Ahmad Yani No. 15',
                'andi.pratama',
                'password123'
            ],
            [
                2,
                '',
                'Nurdin Malik',
                '198703202010031002',
                'Tenaga Perpustakaan',
                'nurdin.malik@gmail.com',
                '081234567891',
                '1987-03-20',
                'L',
                'Jl. Gatot Subroto No. 25',
                'nurdin.malik',
                'password123'
            ],
            [
                3,
                '',
                'Sry Handayani',
                '198905102015031003',
                'Tenaga Laboratorium',
                'sry.handayani@gmail.com',
                '081234567892',
                '1989-05-10',
                'P',
                'Jl. Imam Bonjol No. 30',
                'sry.handayani',
                'password123'
            ],
        ]);
    }

    public function headings(): array
    {
        return [
            'no_id',
            'id_tenaga_pendidikan',
            'nama',
            'nip',
            'jabatan',
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
            'B' => 12,
            'C' => 25,
            'D' => 18,
            'E' => 22,
            'F' => 25,
            'G' => 15,
            'H' => 14,
            'I' => 14,
            'J' => 25,
            'K' => 18,
            'L' => 15,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $worksheet = $event->sheet->getDelegate();

                // Insert instruction row at the top
                $worksheet->insertNewRowBefore(1, 1);
                $worksheet->mergeCells('A1:L1');
                $worksheet->setCellValue('A1', 'Petunjuk: Isi data di bawah header. ID dan Password boleh kosong (akan di-generate otomatis). Jenis Kelamin: L/P');
                $worksheet->getStyle('A1')->getFont()->setItalic(true)->setColor(new Color('FF666666'));
                $worksheet->getRowDimension(1)->setRowHeight(25);

                // Freeze header row (now at row 2)
                $worksheet->freezePane('A3');
            }
        ];
    }
}
