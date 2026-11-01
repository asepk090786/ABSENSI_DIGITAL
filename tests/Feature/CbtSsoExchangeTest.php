<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\CbtSsoTicket;
use App\Models\Kelas;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class CbtSsoExchangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_ticket_exchange_requires_api_key(): void
    {
        $this->postJson('/api/sso/cbt/exchange', ['ticket' => Str::random(64)])
            ->assertUnauthorized();
    }

    public function test_expired_ticket_is_rejected(): void
    {
        Log::spy();
        $apiKey = $this->createApiKey();
        $user = User::factory()->create(['is_active' => true]);
        $plainTicket = Str::random(64);
        CbtSsoTicket::create([
            'user_id' => $user->id,
            'ticket_hash' => hash('sha256', $plainTicket),
            'state' => str_repeat('c', 64),
            'expires_at' => now()->subSecond(),
        ]);

        $this->postJson('/api/sso/cbt/exchange', ['ticket' => $plainTicket], [
            'X-API-Key' => $apiKey,
        ])->assertUnprocessable();

        Log::shouldHaveReceived('warning')
            ->once()
            ->with('Garuda CBT SSO ticket exchange rejected.');
    }

    public function test_inactive_user_cannot_exchange_ticket(): void
    {
        $apiKey = $this->createApiKey();
        $user = User::factory()->create(['is_active' => false]);
        $plainTicket = Str::random(64);
        $ticket = CbtSsoTicket::create([
            'user_id' => $user->id,
            'ticket_hash' => hash('sha256', $plainTicket),
            'state' => str_repeat('d', 64),
            'expires_at' => now()->addMinute(),
        ]);

        $this->postJson('/api/sso/cbt/exchange', ['ticket' => $plainTicket], [
            'X-API-Key' => $apiKey,
        ])->assertForbidden();

        $this->assertNotNull($ticket->fresh()->consumed_at);
    }

    public function test_ticket_cleanup_only_deletes_tickets_expired_for_more_than_24_hours(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $oldExpired = CbtSsoTicket::create([
            'user_id' => $user->id,
            'ticket_hash' => hash('sha256', Str::random(64)),
            'state' => str_repeat('e', 64),
            'expires_at' => now()->subDays(2),
        ]);
        $recentExpired = CbtSsoTicket::create([
            'user_id' => $user->id,
            'ticket_hash' => hash('sha256', Str::random(64)),
            'state' => str_repeat('f', 64),
            'expires_at' => now()->subHours(12),
        ]);
        $validTicket = CbtSsoTicket::create([
            'user_id' => $user->id,
            'ticket_hash' => hash('sha256', Str::random(64)),
            'state' => str_repeat('a', 64),
            'expires_at' => now()->addMinute(),
        ]);

        $this->artisan('sso:prune-expired-tickets')
            ->expectsOutput('Deleted 1 expired CBT SSO ticket(s).')
            ->assertSuccessful();

        $this->assertDatabaseMissing('cbt_sso_tickets', ['id' => $oldExpired->id]);
        $this->assertDatabaseHas('cbt_sso_tickets', ['id' => $recentExpired->id]);
        $this->assertDatabaseHas('cbt_sso_tickets', ['id' => $validTicket->id]);
    }

    public function test_successful_database_restore_invalidates_all_existing_sso_tickets(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $ticket = CbtSsoTicket::create([
            'user_id' => $user->id,
            'ticket_hash' => hash('sha256', Str::random(64)),
            'state' => str_repeat('c', 64),
            'expires_at' => now()->addMinute(),
        ]);
        $backupPath = tempnam(sys_get_temp_dir(), 'simadis-restore-test-');
        $this->assertNotFalse($backupPath);

        $backup = \Mockery::mock(BackupService::class);
        $backup->shouldReceive('import')->once()->with($backupPath)->andReturn(true);
        $this->app->instance(BackupService::class, $backup);

        try {
            $this->artisan('db:restore', ['--file' => $backupPath])
                ->expectsOutput('Restore database selesai.')
                ->assertExitCode(0);
        } finally {
            unlink($backupPath);
        }

        $this->assertDatabaseMissing('cbt_sso_tickets', ['id' => $ticket->id]);
    }

    public function test_operational_status_reports_aggregate_metrics_without_secrets_or_user_ids(): void
    {
        Log::spy();
        config([
            'services.cbt_sso.start_url' => 'https://cbt.example.test/sso/start',
            'services.cbt_sso.callback_url' => 'https://cbt.example.test/sso/consume',
        ]);
        $apiKey = $this->createApiKey();
        $user = User::factory()->create(['is_active' => true]);
        CbtSsoTicket::create([
            'user_id' => $user->id,
            'ticket_hash' => hash('sha256', Str::random(64)),
            'state' => str_repeat('b', 64),
            'expires_at' => now()->subDays(2),
        ]);

        $this->artisan('sso:integration-status')
            ->expectsOutputToContain('attention_required')
            ->assertExitCode(1);

        Log::shouldHaveReceived('error')
            ->once()
            ->with('CBT SSO operational status requires attention.', \Mockery::on(
                fn (array $context): bool => $context['active_api_keys'] === 1
                    && $context['tickets_past_retention'] === 1
                    && $context['status'] === 'attention_required'
                    && ! array_key_exists('api_key', $context)
                    && ! array_key_exists('user_id', $context)
            ));

        $this->assertDatabaseHas('api_keys', ['key_hash' => hash('sha256', $apiKey)]);
        $this->assertDatabaseCount('cbt_sso_tickets', 1);
    }

    public function test_operational_status_returns_success_when_configuration_and_retention_are_healthy(): void
    {
        Log::spy();
        config([
            'services.cbt_sso.start_url' => 'https://cbt.example.test/sso/start',
            'services.cbt_sso.callback_url' => 'https://cbt.example.test/sso/consume',
        ]);
        $this->createApiKey();

        $this->artisan('sso:integration-status')
            ->expectsOutputToContain('ready_for_review')
            ->assertExitCode(0);

        Log::shouldHaveReceived('info')
            ->once()
            ->with('CBT SSO operational status report.', \Mockery::on(
                fn (array $context): bool => $context['active_api_keys'] === 1
                    && $context['tickets_past_retention'] === 0
                    && $context['status'] === 'ready_for_review'
            ));
        Log::shouldNotHaveReceived('error');
    }

    public function test_ticket_exchange_returns_identity_without_password_and_is_single_use(): void
    {
        $role = Role::create(['role_name' => 'Admin']);
        $user = User::factory()->create([
            'username' => 'sso-admin',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $plainTicket = Str::random(64);
        $state = str_repeat('a', 64);

        CbtSsoTicket::create([
            'user_id' => $user->id,
            'ticket_hash' => hash('sha256', $plainTicket),
            'state' => $state,
            'expires_at' => now()->addMinute(),
        ]);

        $apiKey = Str::random(48);
        ApiKey::create([
            'name' => 'Garuda CBT SSO test',
            'key_prefix' => substr($apiKey, 0, 12),
            'key_hash' => hash('sha256', $apiKey),
        ]);

        $this->postJson('/api/sso/cbt/exchange', ['ticket' => $plainTicket], [
            'X-API-Key' => $apiKey,
        ])
            ->assertOk()
            ->assertJsonPath('state', $state)
            ->assertJsonPath('user.simadis_user_id', (string) $user->id)
            ->assertJsonPath('user.username', 'sso-admin')
            ->assertJsonPath('user.cbt_role', 'admin')
            ->assertJsonMissingPath('user.password');

        $this->postJson('/api/sso/cbt/exchange', ['ticket' => $plainTicket], [
            'X-API-Key' => $apiKey,
        ])->assertUnprocessable();
    }

    public function test_unmapped_simadis_role_is_not_promoted_to_a_cbt_role(): void
    {
        $role = Role::create(['role_name' => 'Tenaga Pendidikan']);
        $user = User::factory()->create([
            'username' => 'sso-staff',
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $plainTicket = Str::random(64);
        CbtSsoTicket::create([
            'user_id' => $user->id,
            'ticket_hash' => hash('sha256', $plainTicket),
            'state' => str_repeat('b', 64),
            'expires_at' => now()->addMinute(),
        ]);

        $apiKey = Str::random(48);
        ApiKey::create([
            'name' => 'Garuda CBT SSO test',
            'key_prefix' => substr($apiKey, 0, 12),
            'key_hash' => hash('sha256', $apiKey),
        ]);

        $this->postJson('/api/sso/cbt/exchange', ['ticket' => $plainTicket], [
            'X-API-Key' => $apiKey,
        ])
            ->assertOk()
            ->assertJsonPath('user.cbt_role', null)
            ->assertJsonMissingPath('user.password');
    }

    public function test_student_exchange_includes_nis_and_nisn_for_cbt_account_reconciliation(): void
    {
        $apiKey = $this->createApiKey();
        $role = Role::create(['role_name' => 'Siswa']);
        $year = TahunAjaran::create([
            'nama_tahun' => '2026/2027',
            'is_active' => true,
        ]);
        Semester::create([
            'tahun_ajaran_id' => $year->id,
            'nama_semester' => 'Ganjil',
            'is_active' => true,
        ]);
        $class = Kelas::create([
            'nama_kelas' => 'Kelas dengan nama lebih dari tiga puluh karakter',
            'kode_kelas' => 'X1',
            'tingkat_kelas' => '10',
        ]);
        $student = Siswa::create([
            'nis' => '12345',
            'nisn' => '0012345678',
            'nama' => 'Siswa SSO',
            'kelas_id' => $class->id,
            'status_aktif' => true,
        ]);
        $user = User::factory()->create([
            'username' => 'siswa-sso',
            'name' => 'Siswa SSO',
            'role_id' => $role->id,
            'siswa_id' => $student->id,
            'is_active' => true,
        ]);
        $plainTicket = Str::random(64);
        CbtSsoTicket::create([
            'user_id' => $user->id,
            'ticket_hash' => hash('sha256', $plainTicket),
            'state' => str_repeat('9', 64),
            'expires_at' => now()->addMinute(),
        ]);

        $this->postJson('/api/sso/cbt/exchange', ['ticket' => $plainTicket], [
            'X-API-Key' => $apiKey,
        ])
            ->assertOk()
            ->assertJsonPath('user.cbt_role', 'siswa')
            ->assertJsonPath('user.nis', '12345')
            ->assertJsonPath('user.nisn', '0012345678')
            ->assertJsonPath('user.current_class_name', 'Kelas dengan nama lebih dari tiga puluh karakter')
            ->assertJsonPath('user.active_year_label', '2026/2027')
            ->assertJsonPath('user.active_semester_label', 'Ganjil')
            ->assertJsonMissingPath('user.password');
    }

    public function test_student_exchange_omits_ambiguous_active_semester_from_placement_data(): void
    {
        $apiKey = $this->createApiKey();
        $role = Role::create(['role_name' => 'Siswa']);
        $year = TahunAjaran::create([
            'nama_tahun' => '2026/2027',
            'is_active' => true,
        ]);
        Semester::create([
            'tahun_ajaran_id' => $year->id,
            'nama_semester' => 'Ganjil',
            'is_active' => true,
        ]);
        Semester::create([
            'tahun_ajaran_id' => $year->id,
            'nama_semester' => 'Genap',
            'is_active' => true,
        ]);
        $student = Siswa::create([
            'nis' => '54321',
            'nisn' => '0098765432',
            'nama' => 'Siswa Periode Ambigu',
            'status_aktif' => true,
        ]);
        $user = User::factory()->create([
            'username' => 'siswa-periode',
            'name' => 'Siswa Periode Ambigu',
            'role_id' => $role->id,
            'siswa_id' => $student->id,
            'is_active' => true,
        ]);
        $plainTicket = Str::random(64);
        CbtSsoTicket::create([
            'user_id' => $user->id,
            'ticket_hash' => hash('sha256', $plainTicket),
            'state' => str_repeat('8', 64),
            'expires_at' => now()->addMinute(),
        ]);

        $this->postJson('/api/sso/cbt/exchange', ['ticket' => $plainTicket], [
            'X-API-Key' => $apiKey,
        ])
            ->assertOk()
            ->assertJsonPath('user.active_year_label', '2026/2027')
            ->assertJsonPath('user.active_semester_label', null);
    }

    public function test_teacher_and_student_roles_map_to_their_cbt_groups(): void
    {
        $apiKey = $this->createApiKey();

        foreach ([
            'Guru' => 'guru',
            'Guru Kelas' => 'guru',
            'Guru Mapel' => 'guru',
            'Guru BK' => 'guru',
            'Guru Piket' => 'guru',
            'Wali Kelas' => 'guru',
            'Siswa' => 'siswa',
        ] as $roleName => $expectedRole) {
            $role = Role::create(['role_name' => $roleName]);
            $user = User::factory()->create([
                'username' => 'sso-' . Str::slug($roleName),
                'role_id' => $role->id,
                'is_active' => true,
            ]);
            $plainTicket = Str::random(64);

            CbtSsoTicket::create([
                'user_id' => $user->id,
                'ticket_hash' => hash('sha256', $plainTicket),
                'state' => str_pad(dechex($user->id), 64, '0', STR_PAD_LEFT),
                'expires_at' => now()->addMinute(),
            ]);

            $this->postJson('/api/sso/cbt/exchange', ['ticket' => $plainTicket], [
                'X-API-Key' => $apiKey,
            ])
                ->assertOk()
                ->assertJsonPath('user.cbt_role', $expectedRole);
        }
    }

    private function createApiKey(): string
    {
        $apiKey = Str::random(48);
        ApiKey::create([
            'name' => 'Garuda CBT SSO test',
            'key_prefix' => substr($apiKey, 0, 12),
            'key_hash' => hash('sha256', $apiKey),
        ]);

        return $apiKey;
    }
}
