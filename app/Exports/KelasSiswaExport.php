<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KelasSiswaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function __construct(private int $kelasId)
    {
    }

    public function collection()
    {
        return Siswa::with(['user', 'kelas'])
            ->where('kelas_id', $this->kelasId)
            ->orderBy('nama')
            ->get();
    }

    public function headings(): array
    {
        return ['ID', 'NIS', 'NISN', 'Nama', 'Jenis Kelamin', 'Kelas', 'Email', 'Username'];
    }

    public function map($siswa): array
    {
        $genderText = $siswa->jenis_kelamin === 'L'
            ? 'Laki-laki'
            : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '-');

        return [
            $siswa->id,
            $siswa->nis,
            $siswa->nisn,
            $siswa->nama,
            $genderText,
            $siswa->kelas->nama_kelas ?? '-',
            $siswa->user->email ?? $siswa->email ?? '-',
            $siswa->user->username ?? '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
