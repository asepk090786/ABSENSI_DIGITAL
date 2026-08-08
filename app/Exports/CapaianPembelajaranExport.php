<?php

namespace App\Exports;

use App\Models\CapaianPembelajaran;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CapaianPembelajaranExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    private int $no = 1;
    private $user;

    public function __construct($user = null)
    {
        $this->user = $user;
    }

    public function collection()
    {
        $query = CapaianPembelajaran::query();
        $user = auth()->user();

        if ($user && ! $user->hasAnyRole(['Admin', 'Kepala Sekolah']) && ! empty($user->guru_id)) {
            $query->where('user_id', $user->id);
        }

        return $query->orderBy('nama_capaian_pembelajaran')->get();
    }

    public function headings(): array
    {
        return ['No', 'Nama Capaian Pembelajaran', 'Fase', 'Deskripsi', 'Tujuan Pembelajaran', 'Alur Tujuan Pembelajaran', 'Indikator Kriteria'];
    }

    public function map($item): array
    {
        return [
            $this->no++,
            $item->nama_capaian_pembelajaran,
            $item->fase ?? '',
            $item->deskripsi ?? '',
            $item->tujuan_pembelajaran ?? '',
            $item->alur_tujuan_pembelajaran ?? '',
            $item->indikator_kriteria ?? '',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4472C4']]]
        ];
    }
}
