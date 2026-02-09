<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KomponenNilaiTemplateExport implements FromArray, WithHeadings, WithStyles, ShouldAutoSize
{
    public function array(): array
    {
        return [
            ['1', 'Contoh CP', 'Ulangan Harian', '20', 'Detail CP', 'Siswa dapat memahami materi dengan baik', 'Pertemuan 1-2: Pengenalan → Pertemuan 3: Penerapan', 'Dapat menjawab 80% soal dengan benar'],
            ['2', 'Contoh CP', 'Tugas Kelompok', '30', 'Detail CP', 'Siswa dapat berkolaborasi dalam kelompok', 'Pertemuan 2-4: Diskusi dan presentasi', 'Dapat menyelesaikan tugas tepat waktu'],
        ];
    }

    public function headings(): array
    {
        return ['No', 'Capaian Pembelajaran', 'Nama Komponen', 'Bobot (%)', 'Capaian Pembelajaran (Detail)', 'Tujuan Pembelajaran', 'Alur Tujuan Pembelajaran', 'Indikator Kriteria'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']]],
            2 => ['font' => ['color' => ['rgb' => '808080']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']]],
            3 => ['font' => ['color' => ['rgb' => '808080']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E7E6E6']]],
        ];
    }
}
