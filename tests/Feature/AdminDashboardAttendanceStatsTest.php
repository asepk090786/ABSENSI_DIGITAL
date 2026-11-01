<?php

namespace Tests\Feature;

use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardAttendanceStatsTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_daily_teacher_attendance_link_opens_admin_teacher_recap_for_the_selected_date(): void
    {
        $role = Role::firstOrCreate(['role_name' => 'Admin']);
        $admin = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $guru = Guru::create(['nama' => 'Guru Dashboard']);

        foreach (['2026-10-29', '2026-10-30'] as $tanggal) {
            AbsensiGuru::create([
                'guru_id' => $guru->id,
                'tanggal' => $tanggal,
                'status' => 'hadir',
            ]);
        }

        $response = $this->actingAs($admin)->get(route('home'));

        $response->assertOk()
            ->assertSee(route('admin.absensi_guru.index', ['tanggal' => '2026-10-29']), false)
            ->assertSee(route('admin.absensi_guru.index', ['tanggal' => '2026-10-30']), false)
            ->assertDontSee(route('absensi.index', ['tanggal' => '2026-10-29']), false);
    }
}
