<?php

namespace App\Exports;

use App\Models\Kelas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KelasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return Kelas::with(['waliKelas'])->withCount('siswa')->orderBy('nama_kelas')->get();
    }

    public function headings(): array
    {
        return ['ID', 'Nama Kelas', 'Wali Kelas', 'Jumlah Siswa'];
    }

    public function map($kelas): array
    {
        return [
            $kelas->id,
            $kelas->nama_kelas,
            $kelas->waliKelas->nama ?? '-',
            $kelas->siswa_count,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
