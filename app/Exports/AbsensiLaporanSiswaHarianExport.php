<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AbsensiLaporanSiswaHarianExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    private int $rowNumber = 1;

    public function __construct(private readonly Collection $rows)
    {
    }

    public function collection(): Collection
    {
        // Produce a class-level summary for each class present in the rows
        $grouped = $this->rows->groupBy('nama_kelas');
        $out = collect();

        foreach ($grouped as $kelasName => $rows) {
            $hadir = $rows->where('status', 'Hadir')->count();
            $sakit = $rows->where('status', 'Sakit')->count();
            $izin = $rows->where('status', 'Izin')->count();
            $alpa = $rows->where('status', 'Absen')->count();
            $total = $rows->count();
            $percent = $total ? round(($hadir / $total) * 100, 1) : 0;

            // attempt to resolve kelas id and wali kelas name
            $kelasId = $rows->first()->kelas_id ?? null;
            $waliName = '-';
            if ($kelasId) {
                $waliName = DB::table('kelas')
                    ->leftJoin('guru', 'kelas.wali_kelas_id', '=', 'guru.id')
                    ->where('kelas.id', $kelasId)
                    ->value('guru.nama') ?? '-';
            }

            $keterangan = $rows->pluck('keterangan')->filter()->unique()->values()->join(' | ');

            $out->push((object) [
                'is_summary' => true,
                'nama_kelas' => $kelasName,
                'summary_hadir' => $hadir,
                'summary_sakit' => $sakit,
                'summary_izin' => $izin,
                'summary_alpa' => $alpa,
                'summary_total' => $total,
                'summary_percent' => $percent,
                'nama_wali_kelas' => $waliName,
                'keterangan' => $keterangan ?: '-',
            ]);
        }

        return $out;
    }

    public function headings(): array
    {
        return [
            'NO',
            'KELAS',
            'KEHADIRAN (Hadir | Sakit | Izin | Alpa)',
            'JUMLAH HADIR',
            'PERSENTASE KEHADIRAN',
            'NAMA WALI KELAS',
            'KETERANGAN',
        ];
    }

    public function map($row): array
    {
        // Only summary rows are produced by collection()
        return [
            $this->rowNumber++,
            $row->nama_kelas ?? '-',
            'Hadir: ' . ($row->summary_hadir ?? 0) . ' | Sakit: ' . ($row->summary_sakit ?? 0) . ' | Izin: ' . ($row->summary_izin ?? 0) . ' | Alpa: ' . ($row->summary_alpa ?? 0),
            $row->summary_hadir ?? 0,
            ($row->summary_percent ?? 0) . '%',
            $row->nama_wali_kelas ?? '-',
            $row->keterangan ?? '-',
        ];
    }
}
