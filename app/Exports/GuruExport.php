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
            'no_id',
            'id_guru',
            'nama',
            'kode_guru',
            'nip',
            'pangkat_golongan',
            'email',
            'telepon',
            'tanggal_lahir',
            'jenis_kelamin',
            'alamat',
            'username',
            'password',
        ];
    }

    public function map($guru): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $guru->id,
            $guru->nama,
            $guru->kode_guru ?? '-',
            $guru->nip ?? '-',
            $guru->pangkat_golongan ?? '-',
            $guru->email ?? '-',
            $guru->telepon ?? '-',
            $guru->tanggal_lahir ? \Carbon\Carbon::parse($guru->tanggal_lahir)->format('Y-m-d') : '-',
            $guru->jenis_kelamin == 'L' ? 'L' : ($guru->jenis_kelamin == 'P' ? 'P' : '-'),
            $guru->alamat ?? '-',
            $guru->user->username ?? '-',
            '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
