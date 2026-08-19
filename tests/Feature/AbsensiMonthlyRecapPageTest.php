<?php

namespace Tests\Feature;

use Tests\TestCase;

class AbsensiMonthlyRecapPageTest extends TestCase
{
    public function test_monthly_absence_recap_route_exists(): void
    {
        $this->assertTrue(app('router')->has('absensi.rekap-bulanan'));
    }

    public function test_monthly_pdf_report_accepts_legacy_alfa_count_rows(): void
    {
        $html = view('absensi.reports.guru_kelas_pdf', [
            'monthlyRows' => collect([(object) [
                'nama_siswa' => 'Siswa Uji',
                'nis' => '123',
                'hadir_count' => 1,
                'terlambat_count' => 0,
                'sakit_count' => 0,
                'izin_count' => 0,
                'alfa_count' => 2,
                'total_days' => 1,
            ]]),
            'monthlyKelas' => (object) ['nama_kelas' => 'Kelas Uji'],
            'monthlyBulanLabel' => 'Agustus',
            'tahun' => (object) ['nama_tahun' => '2026/2027'],
            'semester' => (object) ['nama_semester' => 'Ganjil'],
            'sekolah' => (object) ['nama_sekolah' => 'Sekolah Uji'],
        ])->render();

        $this->assertStringContainsString('Siswa Uji', $html);
        $this->assertStringContainsString('>2<', $html);
    }
}
