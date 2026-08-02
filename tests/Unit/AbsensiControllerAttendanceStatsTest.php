<?php

namespace Tests\Unit;

use App\Http\Controllers\AbsensiController;
use PHPUnit\Framework\TestCase;

class AbsensiControllerAttendanceStatsTest extends TestCase
{
    public function test_build_bk_attendance_stats_groups_unique_students_per_status()
    {
        $controller = new AbsensiController();
        $method = new \ReflectionMethod($controller, 'buildBkAttendanceStats');
        $method->setAccessible(true);

        $rows = collect([
            (object) ['siswa_id' => 1, 'status' => 'Hadir'],
            (object) ['siswa_id' => 1, 'status' => 'Sakit'],
            (object) ['siswa_id' => 2, 'status' => 'izin'],
            (object) ['siswa_id' => 3, 'status' => 'telat'],
            (object) ['siswa_id' => 4, 'status' => 'alpha'],
            (object) ['siswa_id' => 5, 'status' => 'hadir'],
        ]);

        $stats = $method->invoke($controller, $rows);

        $this->assertSame(2, $stats['hadir']);
        $this->assertSame(1, $stats['sakit']);
        $this->assertSame(1, $stats['izin']);
        $this->assertSame(1, $stats['terlambat']);
        $this->assertSame(1, $stats['tidak_hadir']);
    }
}
