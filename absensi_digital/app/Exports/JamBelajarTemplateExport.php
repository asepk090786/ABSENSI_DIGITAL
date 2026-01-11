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
            ['Senin', 1, '07:00', '07:45', 'KBM', 'TEMPLATE IMPORT JAM KBM'],
            ['Senin', 2, '07:45', '08:30', 'KBM', 'Petunjuk: Isi data sesuai format di bawah ini'],
            ['Senin', 3, '08:30', '09:15', 'KBM', 'Hari: Senin, Selasa, Rabu, Kamis, Jumat, Sabtu, Minggu'],
            ['Selasa', 1, '07:00', '07:45', 'KBM', 'Jam Ke: Nomor urut jam (1, 2, 3, dst)'],
            ['', '', '', '', '', 'Jam Mulai/Selesai: Format HH:MM dengan tanda petik di depan (contoh: \'07:00, \'08:30, \'09:15) PENTING: Gunakan tanda petik (apostrof) sebelum waktu. Contoh: \'07:00 bukan 07:00. Tanpa tanda petik, Excel akan mengubah format waktu!'],
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
            'Keterangan',
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
                $sheet->getColumnDimension('F')->setWidth(60);

                // Wrap text hanya untuk kolom F (Keterangan)
                $sheet->getStyle('F:F')->getAlignment()->setWrapText(true);

                // Style untuk header kolom F baris 1
                $sheet->getStyle('F1')->getFont()->setBold(true)->setSize(12);

                // Style untuk petunjuk di F2
                $sheet->getStyle('F2')->getFont()->setItalic(true)->setSize(10);

                // Style untuk F5 (peringatan penting) - teks merah tebal
                $sheet->getStyle('F5')->getFont()->setBold(true)->getColor()->setRGB('C00000');
            },
        ];
    }
}
