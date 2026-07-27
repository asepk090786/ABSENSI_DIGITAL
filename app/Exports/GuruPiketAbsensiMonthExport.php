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

class GuruPiketAbsensiMonthExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $month;
    protected $year;
    protected $sekolahNama;
    protected $tahunAjaran;
    protected $semester;
    protected $summary;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;

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
        $startDate = \Carbon\Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::create($this->year, $this->month, 1)->endOfMonth();

        $daftarGuru = Guru::query()
            ->where('is_active', 1)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip']);

        $absensiRange = DB::table('absensi_guru')
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy('guru_id');

        $agendaRange = DB::table('agenda_guru')
            ->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->get()
            ->groupBy('guru_id')
            ->map(fn($items) => $items->pluck('id')->toArray());

        $dates = [];
        for ($d = $startDate->copy(); $d->lte($endDate); $d->addDay()) {
            $dates[] = $d->format('Y-m-d');
        }

        $rows = [];
        $no = 1;
        $summary = [
            'hadir' => 0,
            'izin' => 0,
            'sakit' => 0,
            'tidak_hadir' => 0,
            'total' => 0,
        ];

        foreach ($daftarGuru as $item) {
            $absensiMap = $absensiRange->get($item->id, collect())->keyBy('tanggal');
            $hasAgenda = !empty($agendaRange->get($item->id, []));

            $row = [
                $no++,
                $item->nama,
                $item->nip ?: '-',
            ];

            foreach ($dates as $date) {
                $record = $absensiMap->get($date);
                $status = $record->status ?? '';
                $row[] = $status ? match ($status) {
                    'hadir' => 'H',
                    'tidak_hadir' => 'A',
                    'izin' => 'I',
                    'sakit' => 'S',
                    default => '-',
                } : '-';
            }

            $row[] = $absensiMap->where('status', 'hadir')->count() ?? 0;
            $row[] = $absensiMap->where('status', 'tidak_hadir')->count() ?? 0;
            $row[] = $absensiMap->whereIn('status', ['izin', 'sakit'])->count() ?? 0;

            $rows[] = $row;

            foreach ($absensiMap as $record) {
                $status = $record->status;
                if ($status && isset($summary[$status])) {
                    $summary[$status]++;
                }
            }
            $summary['total']++;
        }

        $this->summary = $summary;

        return $rows;
    }

    public function headings(): array
    {
        $headings = ['No', 'Nama Guru', 'NIP'];
        $daysInMonth = \Carbon\Carbon::create($this->year, $this->month)->daysInMonth;
        for ($i = 1; $i <= $daysInMonth; $i++) {
            $headings[] = $i;
        }
        $headings[] = 'H';
        $headings[] = 'A';
        $headings[] = 'I';

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();

        return [
            1 => [
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
            'B' => 30,
            'C' => 24,
        ];

        $daysInMonth = \Carbon\Carbon::create($this->year, $this->month)->daysInMonth;
        $colIndex = 4;
        for ($i = 0; $i < $daysInMonth; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $widths[$col] = 6;
            $colIndex++;
        }

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $widths[$lastCol] = 16;
        $widths[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 1)] = 16;
        $widths[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 2)] = 16;
        $widths[\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex + 3)] = 20;

        return $widths;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $highestColumn = $event->sheet->getHighestColumn();
                $highestRow = $event->sheet->getHighestRow();
                $highestColumnLetter = $event->sheet->getHighestColumn();

                $sheet = $event->sheet;

                $sheet->insertNewRowBefore(1, 4);

                $sheet->setCellValue('A1', 'Rekap Kehadiran Guru');
                if ($this->sekolahNama) {
                    $sheet->setCellValue('B1', $this->sekolahNama);
                }
                $sheet->setCellValue('A2', 'Bulan: ' . \Carbon\Carbon::create($this->year, $this->month)->format('F Y'));
                $sheet->setCellValue('A3', 'Tahun Pelajaran: ' . $this->tahunAjaran . ' | Semester: ' . $this->semester);

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(11);

                $headerRow = 5;
                $headerStyle = $sheet->getStyle('A' . $headerRow . ':' . $highestColumnLetter . $headerRow);
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getFont()->getColor()->setRGB('FFFFFF');
                $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $headerStyle->getFill()->getStartColor()->setRGB('4472C4');
                $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                foreach (range('A', $highestColumnLetter) as $col) {
                    $sheet->getStyle($col . '5:' . $col . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                }

                if ($this->summary) {
                    $summaryStartRow = $highestRow + 2;
                    $sheet->setCellValue('A' . $summaryStartRow, 'REKAP KEHADIRAN');
                    $sheet->setCellValue('B' . ($summaryStartRow + 1), 'Hadir: ' . ($this->summary['hadir'] ?? 0));
                    $sheet->setCellValue('B' . ($summaryStartRow + 2), 'Tidak Hadir: ' . ($this->summary['tidak_hadir'] ?? 0));
                    $sheet->setCellValue('B' . ($summaryStartRow + 3), 'Izin: ' . ($this->summary['izin'] ?? 0));
                    $sheet->setCellValue('B' . ($summaryStartRow + 4), 'Total Guru: ' . ($this->summary['total'] ?? 0));

                    $sheet->getStyle('A' . $summaryStartRow)->getFont()->setBold(true);
                    $sheet->getStyle('B' . ($summaryStartRow + 1) . ':B' . ($summaryStartRow + 4))->getFont()->setBold(true);
                }
            },
        ];
    }
}
