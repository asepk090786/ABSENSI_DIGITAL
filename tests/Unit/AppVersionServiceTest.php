<?php

namespace Tests\Unit;

use App\Services\AppVersionService;
use Tests\TestCase;

class AppVersionServiceTest extends TestCase
{
    public function test_format_version_uses_major_minor_patch(): void
    {
        $service = new AppVersionService(sys_get_temp_dir() . '/absensi_test_state.json', sys_get_temp_dir() . '/absensi_test_history.json');

        $result = $service->getVersionInfo();

        $this->assertMatchesRegularExpression('/^Ver\.\d+\.\d+\.\d+$/', $result['version']);
        $this->assertIsInt($result['major']);
        $this->assertIsInt($result['minor']);
        $this->assertIsInt($result['patch']);
    }
}
