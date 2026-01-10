<?php

namespace App\Exports;

use App\Models\Siswa;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SiswaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Siswa::with(['user', 'kelas'])->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'NIS',
            'NISN',
            'Nama',
            'Jenis Kelamin',
            'Kelas',
            'Email',
            'Username',
            'Status Akun',
        ];
    }

    public function map($siswa): array
    {
        return [
            $siswa->id,
            $siswa->nis ?? '-',
            $siswa->nisn ?? '-',
            $siswa->nama,
            $siswa->jenis_kelamin === 'L'
                ? 'Laki-laki'
                : ($siswa->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
            $siswa->kelas->nama_kelas ?? '-',
            $siswa->user->email ?? $siswa->email ?? '-',
            $siswa->user->username ?? '-',
            $siswa->user && $siswa->user->is_active
                ? 'Aktif'
                : ($siswa->user ? 'Tidak Aktif' : 'Belum Ada Akun'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
