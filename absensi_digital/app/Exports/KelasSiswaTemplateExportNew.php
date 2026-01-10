<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;

class KelasSiswaTemplateExportNew implements FromArray, WithColumnWidths, WithEvents
{
    public function __construct(private int $kelasId)
    {
    }

    /**
     * Return data array dengan struktur:
     * Baris 1-5: Petunjuk
     * Baris 6: Kosong (pemisah)
     * Baris 7: Header
     * Baris 8+: Data contoh
     */
    public function array(): array
    {
        $data = [
            // Baris 1: Header data di kolom A-H, Petunjuk judul di kolom K
            ['no', 'nis', 'nisn', 'nama', 'jenis_kelamin', 'email', 'username', 'password', '', '', 'PETUNJUK PENGISIAN:'],
        ];
        
        // Baris 2-53: Data (2 contoh + 43 baris kosong) dengan petunjuk di kolom K
        $petunjuk = [
            '1. NO: nomor urut siswa (otomatis dari template).',
            '2. NIS dan NISN: wajib diisi dan unik.',
            '3. Nama: wajib diisi (nama lengkap siswa).',
            '4. Jenis Kelamin: wajib diisi dengan L (Laki-laki) atau P (Perempuan).',
            '5. Email, Username, Password: wajib diisi untuk login sistem.',
        ];
        
        for ($i = 1; $i <= 45; $i++) {
            $row = [];
            
            if ($i <= 2) {
                // Contoh data untuk 2 baris pertama
                $row = [
                    $i,
                    $i == 1 ? '2026001' : '2026002',
                    $i == 1 ? '0065432101' : '0065432102',
                    $i == 1 ? 'Adi Pratama' : 'Sari Lestari',
                    $i == 1 ? 'L' : 'P',
                    $i == 1 ? 'adi@example.com' : 'sari@example.com',
                    $i == 1 ? 'adi.pratama' : 'sari.lestari',
                    'password123',
                ];
            } else {
                // Baris kosong untuk diisi user
                $row = [$i, '', '', '', '', '', '', ''];
            }
            
            // Tambah kolom I, J (kosong) dan K (petunjuk)
            $row[] = ''; // Kolom I
            $row[] = ''; // Kolom J
            $row[] = isset($petunjuk[$i - 1]) ? $petunjuk[$i - 1] : ''; // Kolom K
            
            $data[] = $row;
        }
        
        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // no
            'B' => 12,  // nis
            'C' => 14,  // nisn
            'D' => 25,  // nama
            'E' => 14,  // jenis_kelamin
            'F' => 28,  // email
            'G' => 20,  // username
            'H' => 18,  // password
            'I' => 3,   // spacer
            'J' => 3,   // spacer
            'K' => 55,  // petunjuk
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Style header baris 1: biru dengan teks putih untuk kolom A-H
                for ($col = 'A'; $col <= 'H'; $col++) {
                    $style = $sheet->getStyle($col . '1');
                    $style->getFont()->setBold(true);
                    $style->getFont()->setColor(new Color('FFFFFF'));
                    $style->getFill()->setFillType(Fill::FILL_SOLID);
                    $style->getFill()->getStartColor()->setRGB('4472C4');
                    $style->getFont()->setSize(11);
                }
                
                // Style petunjuk di kolom K: bold untuk baris pertama (K1)
                $sheet->getStyle('K1')->getFont()->setBold(true);
                $sheet->getStyle('K1')->getFont()->setSize(11);
                
                // Style petunjuk detail K2-K6
                for ($row = 2; $row <= 6; $row++) {
                    $sheet->getStyle('K' . $row)->getFont()->setSize(10);
                }
            },
        ];
    }
}
