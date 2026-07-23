<?php

namespace Tests\Feature;

use App\Http\Controllers\AbsensiController;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class AbsensiAttendanceSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        $path = __DIR__ . '/../../storage/app/settings.json';
        if (is_file($path)) {
            @unlink($path);
        }
    }

    public function test_regular_teacher_cannot_fill_future_date(): void
    {
        $controller = new class extends AbsensiController {
            public function testCanEditAttendanceDate($user, $date): bool
            {
                return $this->canEditAttendanceDate($user, $date);
            }
        };

        $user = new class {
            public function hasAnyRole(array $roles): bool
            {
                return in_array('Guru', $roles, true);
            }
            public function hasRole(string $role): bool
            {
                return $role === 'Guru';
            }
        };

        $this->assertFalse($controller->testCanEditAttendanceDate($user, Carbon::tomorrow()->toDateString()));
    }

    public function test_student_officer_cannot_fill_future_date(): void
    {
        $controller = new class extends AbsensiController {
            public function testCanEditAttendanceDate($user, $date): bool
            {
                return $this->canEditAttendanceDate($user, $date);
            }
        };

        $user = new class {
            public function hasAnyRole(array $roles): bool
            {
                return false;
            }
            public function hasRole(string $role): bool
            {
                return $role === 'Siswa';
            }
            public function hasClassPosition(): bool
            {
                return true;
            }
        };

        $this->assertFalse($controller->testCanEditAttendanceDate($user, Carbon::tomorrow()->toDateString()));
    }

    public function test_guru_can_change_past_status_when_setting_enabled(): void
    {
        $path = __DIR__ . '/../../storage/app/settings.json';
        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, json_encode([
            'attendance' => [
                'allow_edit_past_for_guru' => true,
                'allow_edit_past_for_siswa_officer' => false,
            ],
        ], JSON_PRETTY_PRINT));

        $controller = new class extends AbsensiController {
            public function testCanModifyStudentAttendanceStatus($user, $date, $isGuruPiketToday = false): bool
            {
                return $this->canModifyStudentAttendanceStatus($user, $date, $isGuruPiketToday);
            }
        };

        $user = new class {
            public function hasAnyRole(array $roles): bool
            {
                return in_array('Guru', $roles, true);
            }
            public function hasRole(string $role): bool
            {
                return $role === 'Guru';
            }
        };

        $this->assertTrue($controller->testCanModifyStudentAttendanceStatus($user, Carbon::yesterday()->toDateString(), false));
    }
}
