<?php

namespace App\Exports;

use App\Models\KomponenNilai;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KomponenNilaiExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private int $no = 1;

    public function collection()
    {
        return KomponenNilai::with('capaianPembelajaran')->orderBy('nama_komponen')->get();
    }

    public function headings(): array
    {
        return ['No', 'Capaian Pembelajaran', 'Nama Komponen', 'Bobot (%)', 'Capaian Pembelajaran (Detail)', 'Tujuan Pembelajaran', 'Alur Tujuan Pembelajaran', 'Indikator Kriteria'];
    }

    public function map($item): array
    {
        return [
            $this->no++,
            $item->capaianPembelajaran?->nama_capaian_pembelajaran ?? '-',
            $item->nama_komponen,
            $item->bobot ?? '',
            $item->capaian_pembelajaran ?? '',
            $item->tujuan_pembelajaran ?? '',
            $item->alur_tujuan_pembelajaran ?? '',
            $item->indikator_kriteria ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']]]
        ];
    }
}
