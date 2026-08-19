<?php

namespace Tests\Feature;

use Tests\TestCase;

class AbsensiMonthlyRecapPageTest extends TestCase
{
    public function test_monthly_absence_recap_route_exists(): void
    {
        $this->assertTrue(app('router')->has('absensi.rekap-bulanan'));
    }
}
