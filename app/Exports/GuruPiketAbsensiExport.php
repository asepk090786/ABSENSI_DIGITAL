<?php

namespace App\Exports;

use App\Models\Guru;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GuruPiketAbsensiExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $selectedTanggal;
    protected $sekolahNama;
    protected $tahunAjaran;
    protected $semester;
    protected $summary;
    protected $jamColumns = [];
    protected $jamColumnColors = [];

    public function __construct(string $selectedTanggal)
    {
        $this->selectedTanggal = $selectedTanggal;

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

    protected function getHariIndonesiaFromDate($date): ?string
    {
        $map = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];

        return $map[Carbon::parse($date)->format('l')] ?? null;
    }

    public function array(): array
    {
        $hariAgenda = $this->getHariIndonesiaFromDate($this->selectedTanggal);

        $daftarGuru = Guru::query()
            ->where('is_active', 1)
            ->orderBy('nama')
            ->get(['id', 'nama', 'nip']);

        $absensiHariIni = DB::table('absensi_guru')
            ->whereDate('tanggal', $this->selectedTanggal)
            ->get()
            ->keyBy('guru_id');

        $agendaHariIni = DB::table('agenda_guru')
            ->whereDate('tanggal', $this->selectedTanggal)
            ->get()
            ->pluck('id', 'guru_id')
            ->toArray();

        $jadwalQuery = DB::table('jadwal_kbm')
            ->join('kelas', 'jadwal_kbm.kelas_id', '=', 'kelas.id')
            ->join('jam_belajar', 'jadwal_kbm.jam_belajar_id', '=', 'jam_belajar.id')
            ->where('jadwal_kbm.guru_id', '!=', 0)
            ->where('jadwal_kbm.hari', $hariAgenda)
            ->select(
                'jadwal_kbm.guru_id',
                'kelas.nama_kelas',
                'jam_belajar.urutan',
                'jam_belajar.jam_mulai',
                'jam_belajar.jam_selesai'
            )
            ->orderBy('jam_belajar.urutan')
            ->get();

        $jadwalPerGuru = [];
        $semuaJamUrutan = [];
        foreach ($jadwalQuery as $jadwal) {
            $jadwalPerGuru[$jadwal->guru_id][] = $jadwal;
            $semuaJamUrutan[$jadwal->urutan] = (int) $jadwal->urutan;
        }
        $this->jamColumns = array_values(array_unique($semuaJamUrutan));
        sort($this->jamColumns);

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
            $record = $absensiHariIni->get($item->id);
            $status = $record->status ?? '';
            $isHadir = $status === 'hadir';

            $hasAgenda = isset($agendaHariIni[$item->id]);
            $hasAbsensi = !empty($status);

            if ($hasAbsensi && $hasAgenda) {
                $rowColor = 'green';
            } elseif ($hasAbsensi || $hasAgenda) {
                $rowColor = 'yellow';
            } else {
                $rowColor = 'red';
            }

            $jadwalList = $jadwalPerGuru[$item->id] ?? [];
            $jamStatus = [];
            $jamColors = [];
            foreach ($this->jamColumns as $urutan) {
                $matched = collect($jadwalList)->firstWhere('urutan', $urutan);
                if ($matched) {
                    $jamStatus[$urutan] = $isHadir ? $matched->nama_kelas : 'X';
                    if ($hasAbsensi && $hasAgenda) {
                        $jamColors[$urutan] = '#d4edda';
                    } elseif ($hasAbsensi || $hasAgenda) {
                        $jamColors[$urutan] = '#fff3cd';
                    } else {
                        $jamColors[$urutan] = '#f8d7da';
                    }
                } else {
                    $jamStatus[$urutan] = '';
                    $jamColors[$urutan] = '';
                }
            }

            $keteranganParts = [];
            foreach ($jadwalList as $jadwal) {
                if ($isHadir) {
                    $keteranganParts[] = 'Hadir: ' . $jadwal->nama_kelas;
                }
            }
            $keterangan = $keteranganParts ? implode(', ', $keteranganParts) : '-';

            $row = [
                $no++,
                $item->nama,
                $item->nip ?: '-',
            ];
            foreach ($this->jamColumns as $urutan) {
                $row[] = $jamStatus[$urutan] ?? '';
            }
            $row[] = $keterangan;
            $row['jam_colors'] = $jamColors;

            $rows[] = $row;

            if ($status && isset($summary[$status])) {
                $summary[$status]++;
            }
            $summary['total']++;
        }

        $this->summary = $summary;
        $this->jamColumnColors = [];
        foreach ($rows as $index => $row) {
            $this->jamColumnColors[$index] = $row['jam_colors'] ?? [];
        }

        return $rows;
    }

    public function headings(): array
    {
        $headings = ['No', 'Nama Guru', 'NIP'];
        foreach ($this->jamColumns as $jam) {
            $headings[] = 'Jam ' . $jam;
        }
        $headings[] = 'Keterangan';

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $dataStartRow = $this->getHeaderRows() + 1;
        $dataEndRow = $highestRow;

        $styles = [
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

        if ($dataEndRow >= $dataStartRow) {
            foreach (range($dataStartRow, $dataEndRow) as $rowIndex) {
                $dataIndex = $rowIndex - $dataStartRow;
                if (!isset($this->jamColumnColors[$dataIndex])) {
                    continue;
                }

                $jamStartCol = 4;
                foreach ($this->jamColumnColors[$dataIndex] as $colOffset => $color) {
                    if ($color && $color !== '#ffffff') {
                        $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($jamStartCol + $colOffset);
                        $cell = $colLetter . $rowIndex;
                        $styles[$cell] = [
                            'fill' => [
                                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                                'startColor' => ['rgb' => ltrim($color, '#')],
                            ],
                        ];
                    }
                }
            }
        }

        return $styles;
    }

    protected function getHeaderRows(): int
    {
        return 5;
    }

    public function columnWidths(): array
    {
        $widths = [
            'A' => 6,
            'B' => 30,
            'C' => 24,
        ];

        $colIndex = 4;
        foreach ($this->jamColumns as $jam) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $widths[$col] = 14;
            $colIndex++;
        }

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
        $widths[$lastCol] = 34;

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
                $sheet->setCellValue('A2', 'Tanggal: ' . \Carbon\Carbon::parse($this->selectedTanggal)->format('d/m/Y'));
                $sheet->setCellValue('A3', 'Tahun Pelajaran: ' . $this->tahunAjaran . ' | Semester: ' . $this->semester);

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
                $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(11);

                $headerRow = 5;
                $sheet->mergeCells('D' . $headerRow . ':' . $highestColumnLetter . $headerRow);
                $sheet->setCellValue('D' . $headerRow, 'Kehadiran di jam ke');
                $sheet->getStyle('D' . $headerRow . ':' . $highestColumnLetter . $headerRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $headerStyle = $sheet->getStyle('A' . $headerRow . ':' . $highestColumnLetter . ($headerRow + 1));
                $headerStyle->getFont()->setBold(true);
                $headerStyle->getFont()->getColor()->setRGB('FFFFFF');
                $headerStyle->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
                $headerStyle->getFill()->getStartColor()->setRGB('4472C4');
                $headerStyle->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                foreach (range('A', $highestColumnLetter) as $col) {
                    $sheet->getStyle($col . '5:' . $col . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                }
                $sheet->getStyle('A6:A' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('C6:C' . $highestRow)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                if ($this->summary) {
                    $summaryStartRow = $highestRow + 2;
                    $sheet->setCellValue('A' . $summaryStartRow, 'REKAP KEHADIRAN');
                    $sheet->setCellValue('B' . ($summaryStartRow + 1), 'Hadir: ' . ($this->summary['hadir'] ?? 0));
                    $sheet->setCellValue('B' . ($summaryStartRow + 2), 'Izin: ' . ($this->summary['izin'] ?? 0));
                    $sheet->setCellValue('B' . ($summaryStartRow + 3), 'Sakit: ' . ($this->summary['sakit'] ?? 0));
                    $sheet->setCellValue('B' . ($summaryStartRow + 4), 'Tidak Hadir: ' . ($this->summary['tidak_hadir'] ?? 0));
                    $sheet->setCellValue('B' . ($summaryStartRow + 5), 'Total Guru: ' . ($this->summary['total'] ?? 0));

                    $sheet->getStyle('A' . $summaryStartRow)->getFont()->setBold(true);
                    $sheet->getStyle('B' . ($summaryStartRow + 1) . ':B' . ($summaryStartRow + 5))->getFont()->setBold(true);
                }
            },
        ];
    }
}
