<?php

namespace App\Exports;

use App\Models\Siswa;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NilaiHarianTemplateExport implements FromCollection, WithHeadings
{
    protected int $kelasId;

    public function __construct(int $kelasId)
    {
        $this->kelasId = $kelasId;
    }

    public function headings(): array
    {
        return ['no', 'nis', 'nisn', 'nama', 'nilai'];
    }

    public function collection(): Collection
    {
        $rows = Siswa::where('kelas_id', $this->kelasId)
            ->orderBy('nama')
            ->get()
            ->values();

        return $rows->map(function ($siswa, $index) {
            $nis = $siswa->nis ? "'" . $siswa->nis : '';
            $nisn = $siswa->nisn ? "'" . $siswa->nisn : '';

            return [
                $index + 1,
                $nis,
                $nisn,
                $siswa->nama,
                null,
            ];
        });
    }
}