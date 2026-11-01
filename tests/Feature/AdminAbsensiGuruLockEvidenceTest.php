<?php

namespace Tests\Feature;

use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\Role;
use App\Models\User;
use App\Services\SettingsManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAbsensiGuruLockEvidenceTest extends TestCase
{
    use RefreshDatabase;

    protected FakeGuruPiketAttendanceSettingsManager $settings;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        $this->settings = new FakeGuruPiketAttendanceSettingsManager();
        $this->app->instance(SettingsManager::class, $this->settings);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_lock_blocks_piket_input_bulk_input_and_delete_but_admin_can_override(): void
    {
        $admin = $this->createUserWithRole('Admin');
        $piket = $this->createUserWithRole('Guru Piket');
        $guru = Guru::create(['nama' => 'Guru Terkunci']);
        $tanggal = '2026-10-24';

        $this->actingAs($admin)
            ->post(route('admin.absensi_guru.lock'), [
                'guru_id' => $guru->id,
                'tanggal' => $tanggal,
                'locked' => 1,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('absensi_guru_locks', [
            'guru_id' => $guru->id,
            'tanggal' => $tanggal,
            'locked_by_user_id' => $admin->id,
        ]);

        $this->actingAs($piket)
            ->post(route('admin.absensi_guru.lock'), [
                'guru_id' => $guru->id,
                'tanggal' => $tanggal,
                'locked' => 0,
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->get(route('admin.absensi_guru.index', ['tanggal' => $tanggal]))
            ->assertOk()
            ->assertSee('Terkunci Admin')
            ->assertSee('Buka');

        $this->actingAs($piket)
            ->get(route('piket.absensi_guru.index', ['tanggal' => $tanggal]))
            ->assertOk()
            ->assertSee('Terkunci Admin')
            ->assertSee('Tidak dapat diubah')
            ->assertDontSee('data-guru-id="' . $guru->id . '"', false);

        $absensi = AbsensiGuru::create([
            'guru_id' => $guru->id,
            'tanggal' => $tanggal,
            'status' => 'hadir',
        ]);

        $this->actingAs($piket)
            ->post(route('piket.absensi_guru.store'), [
                'guru_id' => $guru->id,
                'tanggal' => $tanggal,
                'status' => 'sakit',
            ])
            ->assertForbidden();

        $this->actingAs($piket)
            ->post(route('piket.absensi_guru.bulk_store'), [
                'tanggal' => $tanggal,
                'attendance' => [$guru->id => ['status' => 'izin']],
            ])
            ->assertForbidden();

        $this->actingAs($piket)
            ->delete(route('piket.absensi_guru.delete'), [
                'guru_id' => $guru->id,
                'tanggal' => $tanggal,
            ])
            ->assertForbidden();

        $this->assertSame('hadir', $absensi->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.absensi_guru.store'), [
                'guru_id' => $guru->id,
                'tanggal' => $tanggal,
                'status' => 'izin',
            ])
            ->assertSessionHas('success', 'Absensi guru berhasil disimpan.');

        $this->assertSame('izin', $absensi->fresh()->status);

        $this->actingAs($admin)
            ->post(route('admin.absensi_guru.lock'), [
                'guru_id' => $guru->id,
                'tanggal' => $tanggal,
                'locked' => 0,
            ])
            ->assertRedirect();

        $this->assertDatabaseMissing('absensi_guru_locks', [
            'guru_id' => $guru->id,
            'tanggal' => $tanggal,
        ]);
    }

    public function test_piket_hadir_semua_skips_locked_teachers(): void
    {
        $admin = $this->createUserWithRole('Admin');
        $piket = $this->createUserWithRole('Guru Piket');
        $guruTerkunci = Guru::create(['nama' => 'Guru Terkunci']);
        $guruBebas = Guru::create(['nama' => 'Guru Bebas']);
        $tanggal = '2026-10-24';

        $this->actingAs($admin)->post(route('admin.absensi_guru.lock'), [
            'guru_id' => $guruTerkunci->id,
            'tanggal' => $tanggal,
            'locked' => 1,
        ]);

        $this->actingAs($piket)
            ->post(route('piket.absensi_guru.hadir_semua'), ['tanggal' => $tanggal])
            ->assertSessionHas('warning');

        $this->assertDatabaseMissing('absensi_guru', [
            'guru_id' => $guruTerkunci->id,
            'tanggal' => $tanggal,
        ]);
        $this->assertSame(
            'hadir',
            AbsensiGuru::where('guru_id', $guruBebas->id)
                ->whereDate('tanggal', $tanggal)
                ->value('status')
        );
    }

    public function test_admin_can_lock_and_unlock_all_active_teachers_for_selected_date(): void
    {
        $admin = $this->createUserWithRole('Admin');
        $piket = $this->createUserWithRole('Guru Piket');
        $guruAktif1 = Guru::create(['nama' => 'Guru Aktif 1', 'is_active' => true]);
        $guruAktif2 = Guru::create(['nama' => 'Guru Aktif 2', 'is_active' => true]);
        Guru::create(['nama' => 'Guru Nonaktif', 'is_active' => false]);
        $tanggal = '2026-10-24';

        $this->actingAs($admin)
            ->post(route('admin.absensi_guru.lock_all'), [
                'tanggal' => $tanggal,
                'locked' => 1,
            ])
            ->assertRedirect(route('admin.absensi_guru.index', ['tanggal' => $tanggal]))
            ->assertSessionHas('success', 'Input absensi berhasil dikunci untuk 2 guru aktif.');

        $this->assertDatabaseCount('absensi_guru_locks', 2);
        $this->actingAs($admin)
            ->get(route('admin.absensi_guru.index', ['tanggal' => $tanggal]))
            ->assertOk()
            ->assertSee('Buka Semua Kunci');

        $this->actingAs($piket)
            ->post(route('piket.absensi_guru.store'), [
                'guru_id' => $guruAktif1->id,
                'tanggal' => $tanggal,
                'status' => 'hadir',
            ])
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('admin.absensi_guru.lock_all'), [
                'tanggal' => $tanggal,
                'locked' => 0,
            ])
            ->assertRedirect(route('admin.absensi_guru.index', ['tanggal' => $tanggal]))
            ->assertSessionHas('success', 'Semua kunci input absensi pada tanggal ini berhasil dibuka.');

        $this->assertDatabaseCount('absensi_guru_locks', 0);
    }

    public function test_admin_can_configure_the_guru_piket_past_date_setting(): void
    {
        $admin = $this->createUserWithRole('Admin');
        $piket = $this->createUserWithRole('Guru Piket');

        $this->actingAs($admin)
            ->get(route('setting.absensi'))
            ->assertOk()
            ->assertSee('Pengaturan Absensi Guru Piket')
            ->assertDontSee('Blokir input dan perubahan absensi Guru Piket untuk tanggal terlewat');

        $this->actingAs($admin)
            ->put(route('setting.absensi.guru_piket.update'), [
                'block_past_dates_for_guru_piket' => 1,
            ])
            ->assertRedirect(route('setting.absensi', ['tab' => 'guru-piket']))
            ->assertSessionHas('success', 'Pengaturan absensi Guru Piket disimpan.');

        $this->assertTrue($this->settings->get('attendance.block_past_dates_for_guru_piket'));
        $this->actingAs($admin)
            ->get(route('setting.absensi', ['tab' => 'guru-piket']))
            ->assertOk()
            ->assertSee('Blokir input dan perubahan absensi Guru Piket untuk tanggal terlewat')
            ->assertSee('id="block_past_dates_for_guru_piket"', false)
            ->assertSee('checked', false);

        $this->actingAs($piket)
            ->put(route('setting.absensi.guru_piket.update'), [
                'block_past_dates_for_guru_piket' => 0,
            ])
            ->assertForbidden();
    }

    public function test_past_date_setting_blocks_piket_changes_but_keeps_admin_and_today_access(): void
    {
        Carbon::setTestNow('2026-10-30 12:00:00');
        $admin = $this->createUserWithRole('Admin');
        $piket = $this->createUserWithRole('Guru Piket');
        $guruPast = Guru::create(['nama' => 'Guru Tanggal Lampau']);
        $guruToday = Guru::create(['nama' => 'Guru Hari Ini']);
        $absensiLampau = AbsensiGuru::create([
            'guru_id' => $guruPast->id,
            'tanggal' => '2026-10-29',
            'status' => 'hadir',
        ]);
        $this->settings->set('attendance.block_past_dates_for_guru_piket', true);

        $this->actingAs($piket)
            ->get(route('piket.absensi_guru.index', ['tanggal' => '2026-10-29']))
            ->assertOk()
            ->assertSee('Pengaturan Admin memblokir input dan perubahan absensi Guru Piket')
            ->assertSee('Tanggal terlewat dikunci')
            ->assertSee('disabled', false);

        $this->actingAs($piket)
            ->post(route('piket.absensi_guru.store'), [
                'guru_id' => $guruPast->id,
                'tanggal' => '2026-10-29',
                'status' => 'sakit',
            ])
            ->assertForbidden();

        $this->actingAs($piket)
            ->post(route('piket.absensi_guru.bulk_store'), [
                'tanggal' => '2026-10-29',
                'attendance' => [$guruPast->id => ['status' => 'izin']],
            ])
            ->assertForbidden();

        $this->actingAs($piket)
            ->delete(route('piket.absensi_guru.delete'), [
                'guru_id' => $guruPast->id,
                'tanggal' => '2026-10-29',
            ])
            ->assertForbidden();

        $this->actingAs($piket)
            ->post(route('piket.absensi_guru.hadir_semua'), ['tanggal' => '2026-10-29'])
            ->assertForbidden();

        $this->assertSame('hadir', $absensiLampau->fresh()->status);

        $this->actingAs($piket)
            ->post(route('piket.absensi_guru.store'), [
                'guru_id' => $guruToday->id,
                'tanggal' => '2026-10-30',
                'status' => 'hadir',
            ])
            ->assertRedirect();
        $this->assertSame(
            'hadir',
            AbsensiGuru::where('guru_id', $guruToday->id)->whereDate('tanggal', '2026-10-30')->value('status')
        );

        $this->actingAs($admin)
            ->post(route('admin.absensi_guru.store'), [
                'guru_id' => $guruPast->id,
                'tanggal' => '2026-10-29',
                'status' => 'izin',
            ])
            ->assertSessionHas('success', 'Absensi guru berhasil disimpan.');
        $this->assertSame('izin', $absensiLampau->fresh()->status);
    }

    public function test_past_date_setting_defaults_to_existing_piket_behavior(): void
    {
        Carbon::setTestNow('2026-10-30 12:00:00');
        $piket = $this->createUserWithRole('Guru Piket');
        $guru = Guru::create(['nama' => 'Guru Tanggal Lampau']);

        $this->actingAs($piket)
            ->post(route('piket.absensi_guru.store'), [
                'guru_id' => $guru->id,
                'tanggal' => '2026-10-29',
                'status' => 'hadir',
            ])
            ->assertRedirect();

        $this->assertSame(
            'hadir',
            AbsensiGuru::where('guru_id', $guru->id)->whereDate('tanggal', '2026-10-29')->value('status')
        );
    }

    public function test_absence_evidence_is_uploaded_and_served_by_public_link(): void
    {
        Storage::fake('local');
        $admin = $this->createUserWithRole('Admin');
        $guru = Guru::create(['nama' => 'Guru Dengan Bukti']);
        $file = UploadedFile::fake()->createWithContent('surat.pdf', "%PDF-1.4\nSurat izin\n%%EOF");

        $this->actingAs($admin)
            ->get(route('admin.absensi_guru.index'))
            ->assertOk()
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="bukti"', false)
            ->assertSee('accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"', false)
            ->assertSee("['izin', 'sakit', 'dinas_luar']", false)
            ->assertSee('Upload bukti dukung (opsional)');

        $this->actingAs($admin)
            ->post(route('admin.absensi_guru.store'), [
                'guru_id' => $guru->id,
                'tanggal' => '2026-10-24',
                'status' => 'izin',
                'keterangan' => 'Keperluan keluarga',
                'bukti' => $file,
            ])
            ->assertSessionHas('success', 'Absensi guru berhasil disimpan.');

        $absensi = AbsensiGuru::where('guru_id', $guru->id)->firstOrFail();
        $evidencePath = $absensi->bukti_path;
        $this->assertNotEmpty($absensi->bukti_path);
        $this->assertNotEmpty($absensi->bukti_token);
        Storage::disk('local')->assertExists($absensi->bukti_path);

        $this->get(route('public.absensi-guru.evidence', $absensi->bukti_token))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->actingAs($admin)
            ->get(route('admin.absensi_guru.laporan', ['guru_id' => $guru->id]))
            ->assertOk()
            ->assertSee(route('public.absensi-guru.evidence', $absensi->bukti_token))
            ->assertSee('Buka bukti surat');

        $this->actingAs($admin)
            ->post(route('admin.absensi_guru.store'), [
                'guru_id' => $guru->id,
                'tanggal' => '2026-10-24',
                'status' => 'hadir',
            ])
            ->assertSessionHas('success', 'Absensi guru berhasil disimpan.');

        $absensi->refresh();
        $this->assertNull($absensi->bukti_path);
        $this->assertNull($absensi->bukti_token);
        Storage::disk('local')->assertMissing($evidencePath);
    }

    public function test_evidence_requires_absence_status_and_supported_file_type(): void
    {
        Storage::fake('local');
        $admin = $this->createUserWithRole('Admin');
        $guru = Guru::create(['nama' => 'Guru']);

        $this->actingAs($admin)
            ->from(route('admin.absensi_guru.index'))
            ->post(route('admin.absensi_guru.store'), [
                'guru_id' => $guru->id,
                'tanggal' => '2026-10-24',
                'status' => 'hadir',
                'bukti' => UploadedFile::fake()->createWithContent('surat.pdf', "%PDF-1.4\n%%EOF"),
            ])
            ->assertSessionHasErrors('bukti');

        $this->actingAs($admin)
            ->from(route('admin.absensi_guru.index'))
            ->post(route('admin.absensi_guru.store'), [
                'guru_id' => $guru->id,
                'tanggal' => '2026-10-24',
                'status' => 'izin',
                'bukti' => UploadedFile::fake()->create('bukti.html', 10, 'text/html'),
            ])
            ->assertSessionHasErrors('bukti');
    }

    private function createUserWithRole(string $roleName): User
    {
        $role = Role::firstOrCreate(['role_name' => $roleName]);

        return User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
    }
}

class FakeGuruPiketAttendanceSettingsManager extends SettingsManager
{
    private array $settings = [];

    public function get($key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function set($key, $value)
    {
        data_set($this->settings, $key, $value);
    }
}
