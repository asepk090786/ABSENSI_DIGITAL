<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class JamBelajarTemplateExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    public function array(): array
    {
        return [
            ['Senin', 1, '07:00', '07:45', 'KBM'],
            ['Senin', 2, '07:45', '08:30', 'KBM'],
            ['Senin', 3, '08:30', '09:15', 'KBM'],
            ['Selasa', 1, '07:00', '07:45', 'KBM'],
        ];
    }

    public function headings(): array
    {
        return [
            'Hari',
            'Jam Ke',
            'Jam Mulai',
            'Jam Selesai',
            'Jenis',
        ];
    }

    public function styles($sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->getColumnDimension('A')->setWidth(12);
                $sheet->getColumnDimension('B')->setWidth(10);
                $sheet->getColumnDimension('C')->setWidth(12);
                $sheet->getColumnDimension('D')->setWidth(12);
                $sheet->getColumnDimension('E')->setWidth(12);

                $sheet->getStyle('A1:E1')->getAlignment()->setWrapText(true);

                // Add instructions
                $sheet->insertNewRowBefore(1, 5);
                $sheet->mergeCells('A1:E1');
                $sheet->setCellValue('A1', 'TEMPLATE IMPORT JAM KBM');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                $sheet->mergeCells('A2:E2');
                $sheet->setCellValue('A2', 'Petunjuk: Isi data sesuai format di bawah ini');
                $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(10);

                $sheet->mergeCells('A3:E3');
                $sheet->setCellValue('A3', 'Hari: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu');

                $sheet->mergeCells('A4:E4');
                $sheet->setCellValue('A4', 'Jam Ke: Nomor urut jam (1, 2, 3, dst)');

                $sheet->mergeCells('A5:E5');
                $sheet->setCellValue('A5', 'Jam Mulai/Selesai: Format HH:MM (07:00, 07:45, dst)');
            },
        ];
    }
}
