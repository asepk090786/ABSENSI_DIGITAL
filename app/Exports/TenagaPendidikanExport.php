<?php

namespace App\Exports;

use App\Models\TenagaPendidikan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TenagaPendidikanExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return TenagaPendidikan::with('user')->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return [
            'no_id',
            'id_tenaga_pendidikan',
            'nama',
            'nip',
            'jabatan',
            'email',
            'telepon',
            'tanggal_lahir',
            'jenis_kelamin',
            'alamat',
            'username',
            'password',
        ];
    }

    public function map($tenagaPendidikan): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $tenagaPendidikan->id,
            $tenagaPendidikan->nama,
            $tenagaPendidikan->nip ?? '-',
            $tenagaPendidikan->jabatan ?? '-',
            $tenagaPendidikan->email ?? '-',
            $tenagaPendidikan->telepon ?? '-',
            $tenagaPendidikan->tanggal_lahir ? \Carbon\Carbon::parse($tenagaPendidikan->tanggal_lahir)->format('Y-m-d') : '-',
            $tenagaPendidikan->jenis_kelamin == 'L' ? 'L' : ($tenagaPendidikan->jenis_kelamin == 'P' ? 'P' : '-'),
            $tenagaPendidikan->alamat ?? '-',
            $tenagaPendidikan->user->username ?? '-',
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
