<?php

namespace Tests\Unit;

use App\Http\Controllers\JadwalKbmController;
use Tests\TestCase;

class JadwalKbmControllerAccessTest extends TestCase
{
    public function test_admin_user_is_not_blocked_by_jadwal_maintenance_toggle(): void
    {
        $controller = new class extends JadwalKbmController {
            public function publicShouldShowMaintenancePage($user = null): bool
            {
                return $this->shouldShowMaintenancePage($user);
            }
        };

        $adminUser = new class {
            public function hasAnyRole(array $roles): bool
            {
                return in_array('Admin', $roles, true);
            }
        };

        $this->assertFalse($controller->publicShouldShowMaintenancePage($adminUser));
    }

    public function test_non_admin_user_is_blocked_by_jadwal_maintenance_toggle(): void
    {
        $controller = new class extends JadwalKbmController {
            public function publicShouldShowMaintenancePage($user = null): bool
            {
                return $this->shouldShowMaintenancePage($user);
            }
        };

        $teacherUser = new class {
            public function hasAnyRole(array $roles): bool
            {
                return in_array('Guru', $roles, true);
            }
        };

        $this->assertTrue($controller->publicShouldShowMaintenancePage($teacherUser));
    }
}
