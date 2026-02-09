<?php

namespace App\Exports;

use App\Models\CapaianPembelajaran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CapaianPembelajaranExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private int $no = 1;

    public function collection()
    {
        return CapaianPembelajaran::orderBy('nama_capaian_pembelajaran')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Capaian Pembelajaran', 'Fase', 'Deskripsi'];
    }

    public function map($item): array
    {
        return [
            $this->no++,
            $item->nama_capaian_pembelajaran,
            $item->fase ?? '',
            $item->deskripsi ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']]]
        ];
    }
}
