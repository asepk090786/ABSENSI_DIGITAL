<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class RekapNilaiExport implements FromCollection, WithHeadings, WithTitle, WithStyles, WithEvents
{
    protected $data;
    protected $kelas;
    protected $mapel;
    protected $komponen;
    protected $tahunAjaran;
    protected $semester;

    public function __construct($data, $kelas, $mapel, $komponen, $tahunAjaran, $semester)
    {
        $this->data = $data;
        $this->kelas = $kelas;
        $this->mapel = $mapel;
        $this->komponen = $komponen;
        $this->tahunAjaran = $tahunAjaran;
        $this->semester = $semester;
    }

    public function collection()
    {
        return $this->data->map(function ($siswa, $index) {
            return [
                'no' => $index + 1,
                'nis' => "'" . $siswa->nis,
                'nisn' => "'" . $siswa->nisn,
                'nama' => $siswa->nama,
                'jumlah_nilai' => $siswa->jumlah_nilai ?: '-',
                'rata_rata' => $siswa->rata_rata ? number_format($siswa->rata_rata, 2) : '-',
                'nilai_tertinggi' => $siswa->nilai_tertinggi ?: '-',
                'nilai_terendah' => $siswa->nilai_terendah ?: '-',
                'keterangan' => $siswa->rata_rata 
                    ? ($siswa->rata_rata >= 75 ? 'Baik' : ($siswa->rata_rata >= 60 ? 'Cukup' : 'Kurang'))
                    : 'Belum Ada Nilai'
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'NISN',
            'Nama Siswa',
            'Jumlah Nilai',
            'Rata-rata',
            'Nilai Tertinggi',
            'Nilai Terendah',
            'Keterangan'
        ];
    }

    public function title(): string
    {
        return 'Rekap Nilai';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            2 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Add header information
                $sheet->insertNewRowBefore(1, 5);
                
                $sheet->setCellValue('A1', 'REKAP NILAI');
                $sheet->setCellValue('A2', 'Kelas: ' . $this->kelas->nama_kelas);
                $sheet->setCellValue('A3', 'Mata Pelajaran: ' . $this->mapel->nama_mapel);
                $sheet->setCellValue('A4', 'Komponen: ' . ($this->komponen ? $this->komponen->nama_komponen : 'Semua Komponen'));
                $sheet->setCellValue('A5', 'Tahun Ajaran: ' . $this->tahunAjaran->tahun_ajaran . ' - ' . $this->semester->nama_semester);
                
                // Style header
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A2:A5')->getFont()->setBold(true);
                
                // Auto width
                foreach (range('A', 'I') as $col) {
                    $sheet->getColumnDimension($col)->setAutoSize(true);
                }
                
                // Add borders to data table
                $lastRow = $sheet->getHighestRow();
                $sheet->getStyle('A6:I' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        ],
                    ],
                ]);
            },
        ];
    }
}
