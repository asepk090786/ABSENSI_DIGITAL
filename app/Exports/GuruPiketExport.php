<?php

namespace App\Exports;

use App\Models\Guru;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuruPiketExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $workDays;
    protected $sekolahNama;
    protected $tahunAjaran;
    protected $semester;

    public function __construct(array $workDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'])
    {
        $this->workDays = $workDays;

        $sekolah = DB::table('sekolah')->first();
        $this->sekolahNama = $sekolah->nama_sekolah ?? '';

        $ta = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $this->tahunAjaran = $ta->nama_tahun ?? $ta->nama_tahun_ajaran ?? '';

        $semester = null;
        if ($ta) {
            $semester = DB::table('semester')->where('tahun_ajaran_id', $ta->id)->where('is_active', 1)->first();
        }
        $this->semester = $semester->nama_semester ?? '';
    }

    public function array(): array
    {
        $guru = Guru::with('user')
            ->whereNotNull('hari_piket')
            ->orderBy('nama')
            ->get();

        $lists = [];
        foreach ($this->workDays as $hari) {
            $lists[$hari] = [];
        }

        foreach ($guru as $item) {
            $hariPiket = array_unique((array) ($item->hari_piket ?? []));
            foreach ($hariPiket as $hari) {
                $hariLower = strtolower($hari);
                foreach ($this->workDays as $day) {
                    if (strtolower($day) === $hariLower) {
                        $lists[$day][] = $item->nama;
                        break;
                    }
                }
            }
        }

        $maxCount = max(array_map('count', $lists));

        $rows = [];
        for ($i = 0; $i < $maxCount; $i++) {
            $row = [$i + 1];
            foreach ($this->workDays as $hari) {
                $row[] = $lists[$hari][$i] ?? '';
            }
            $rows[] = $row;
        }

        return $rows;
    }

    public function headings(): array
    {
        return ['No', ...$this->workDays];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            4 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 6,
        ];

        foreach ($this->workDays as $index => $hari) {
            $col = chr(ord('B') + $index);
            $widths[$col] = 32;
        }

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $highestColumn = $event->sheet->getDelegate()->getHighestColumn();
                $highestRow = $event->sheet->getDelegate()->getHighestRow();

                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', 'Jadwal Piket');
                if ($this->sekolahNama) {
                    $sheet->setCellValue('B1', $this->sekolahNama);
                }
                $sheet->setCellValue('A2', 'Tahun Pelajaran: ' . $this->tahunAjaran);
                if ($this->semester) {
                    $sheet->setCellValue('B2', $this->semester);
                }

                $sheet->getStyle('A1:B1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2:B2')->getFont()->setBold(true)->setSize(11);
                $sheet->mergeCells('A1:' . $highestColumn . '1');
                $sheet->mergeCells('A2:' . $highestColumn . '2');
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $headerRow = 4;
                $sheet->getStyle('A' . $headerRow . ':' . $highestColumn . $headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                foreach (range('A', $highestColumn) as $col) {
                    $sheet->getStyle($col . '5:' . $col . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                }
            },
        ];
    }
}
