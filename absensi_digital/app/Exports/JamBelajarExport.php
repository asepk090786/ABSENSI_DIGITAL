<?php

namespace App\Exports;

use App\Models\JamBelajar;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class JamBelajarExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    public function collection()
    {
        return JamBelajar::orderByDay()
            ->get()
            ->map(function ($item, $index) {
                return [
                    'No' => $index + 1,
                    'hari' => $item->hari,
                    'urutan' => $item->urutan,
                    'jam_mulai' => $item->jam_mulai,
                    'jam_selesai' => $item->jam_selesai,
                    'jenis' => $item->jenis,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'No',
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
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(12);
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(12);
                $sheet->getColumnDimension('E')->setWidth(12);
                $sheet->getColumnDimension('F')->setWidth(12);

                $sheet->getStyle('A1:F1')->getAlignment()->setWrapText(true);
            },
        ];
    }
}
