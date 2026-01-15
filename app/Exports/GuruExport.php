<?php

namespace App\Exports;

use App\Models\Guru;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuruExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return Guru::with('user')->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama',
            'Kode Guru',
            'NIP',
            'Email',
            'Telepon',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Alamat',
            'Username',
            'Status Akun',
        ];
    }

    public function map($guru): array
    {
        static $no = 0;
        $no++;
        
        return [
            $no,
            $guru->nama,
            $guru->kode_guru ?? '-',
            $guru->nip ?? '-',
            $guru->email ?? '-',
            $guru->telepon ?? '-',
            $guru->tanggal_lahir ? \Carbon\Carbon::parse($guru->tanggal_lahir)->format('d/m/Y') : '-',
            $guru->jenis_kelamin == 'L' ? 'Laki-laki' : ($guru->jenis_kelamin == 'P' ? 'Perempuan' : '-'),
            $guru->alamat ?? '-',
            $guru->user->username ?? '-',
            $guru->user && $guru->user->is_active ? 'Aktif' : 'Tidak Aktif',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
