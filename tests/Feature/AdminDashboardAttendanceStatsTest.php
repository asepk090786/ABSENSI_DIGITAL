<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminDashboardAttendanceStatsTest extends TestCase
{
    public function test_admin_dashboard_contains_updated_labels(): void
    {
        $contents = file_get_contents(base_path('resources/views/dashboard/admin.blade.php'));

        // Verify card labels
        $this->assertStringContainsString('Alpa/Tidak Hadir', $contents);
        $this->assertStringContainsString('Sakit', $contents);
        $this->assertStringContainsString('Bolos', $contents);
        $this->assertStringContainsString('attendance-item-orange', $contents);

        // Verify table column headers
        $this->assertStringContainsString('<th class="text-center">Alpa/Tidak Hadir</th>', $contents);
        $this->assertStringContainsString('<th class="text-center">Bolos</th>', $contents);
    }
}
