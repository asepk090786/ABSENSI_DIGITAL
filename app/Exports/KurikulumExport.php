<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KurikulumExport implements FromCollection, WithHeadings, WithStyles
{
    private ?string $tingkat;
    private ?string $jurusan;

    public function __construct(?string $tingkat = null, ?string $jurusan = null)
    {
        $this->tingkat = $tingkat;
        $this->jurusan = $jurusan;
    }

    public function collection()
    {
        $query = DB::table('kurikulum_mapel as k')
            ->join('mata_pelajaran as m', 'm.id', '=', 'k.mata_pelajaran_id')
            ->select(
                'k.tingkat',
                'k.jurusan',
                'm.nama_mapel',
                'm.kode_mapel',
                'k.jp'
            )
            ->orderBy('k.tingkat')
            ->orderBy('k.jurusan')
            ->orderBy('m.nama_mapel');

        if ($this->tingkat) {
            $query->where('k.tingkat', $this->tingkat);
        }
        if ($this->jurusan) {
            $query->where('k.jurusan', $this->jurusan);
        }

        $data = $query->get();

        return $data->map(function ($row, $index) {
            return [
                'no' => $index + 1,
                'tingkat' => $row->tingkat,
                'jurusan' => $row->jurusan,
                'nama_mapel' => $row->nama_mapel,
                'kode_mapel' => $row->kode_mapel,
                'jp' => $row->jp,
            ];
        });
    }

    public function headings(): array
    {
        return ['No', 'Tingkat', 'Jurusan', 'Nama Mapel', 'Kode Mapel', 'JP'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
            ],
        ];
    }
}
