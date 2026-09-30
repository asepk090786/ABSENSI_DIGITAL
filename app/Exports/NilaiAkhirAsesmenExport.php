<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NilaiAkhirAsesmenExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private Collection $students)
    {
    }

    public function collection(): Collection
    {
        return $this->students->values()->map(function ($student, $index) {
            return [
                'no' => $index + 1,
                'nis' => $student->nis ?: '-',
                'nama' => $student->nama,
                'rata_formatif' => $student->rata_formatif !== null ? number_format($student->rata_formatif, 2, '.', '') : '-',
                'rata_sumatif' => $student->rata_sumatif !== null ? number_format($student->rata_sumatif, 2, '.', '') : '-',
                'nilai_akhir' => $student->nilai_akhir !== null ? number_format($student->nilai_akhir, 2, '.', '') : '-',
                'keterangan' => $student->nilai_akhir !== null ? 'Lengkap' : 'Nilai formatif atau sumatif belum tersedia',
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'NIS', 'Nama Siswa', 'Rata-rata Formatif', 'Rata-rata Sumatif', 'Nilai Akhir', 'Keterangan'];
    }
}