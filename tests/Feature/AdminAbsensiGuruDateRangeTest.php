<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAbsensiGuruDateRangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_admin_records_and_locks_leave_status_for_each_date_in_the_selected_range(): void
    {
        $admin = $this->createUserWithRole('Admin');
        $piket = $this->createUserWithRole('Guru Piket');
        $guruPerStatus = [];

        foreach (['izin', 'sakit', 'dinas_luar'] as $status) {
            $guru = Guru::create(['nama' => 'Guru ' . $status]);
            $guruPerStatus[$status] = $guru;

            $this->actingAs($admin)
                ->post(route('admin.absensi_guru.store'), [
                    'guru_id' => $guru->id,
                    'tanggal' => '2026-11-02',
                    'tanggal_mulai' => '2026-11-02',
                    'tanggal_selesai' => '2026-11-04',
                    'status' => $status,
                    'keterangan' => 'Status rentang ' . $status,
                ])
                ->assertSessionHas('success', 'Absensi guru berhasil disimpan dan dikunci dari 02/11/2026 sampai 04/11/2026.');

            foreach (['2026-11-02', '2026-11-03', '2026-11-04'] as $tanggal) {
                $absensi = AbsensiGuru::where('guru_id', $guru->id)
                    ->whereDate('tanggal', $tanggal)
                    ->first();
                $this->assertNotNull($absensi);
                $this->assertSame($status, $absensi->status);
                $this->assertSame('Status rentang ' . $status, $absensi->keterangan);
                $this->assertDatabaseHas('absensi_guru_locks', [
                    'guru_id' => $guru->id,
                    'tanggal' => $tanggal,
                    'locked_by_user_id' => $admin->id,
                ]);
            }
        }

        $guruSakit = $guruPerStatus['sakit'];
        $this->assertSame(9, AbsensiGuru::count());

        $this->actingAs($admin)
            ->get(route('admin.absensi_guru.index', ['tanggal' => '2026-11-03']))
            ->assertOk()
            ->assertSee('Sakit')
            ->assertSee('Terkunci Admin')
            ->assertSee('name="tanggal_mulai"', false)
            ->assertSee('name="tanggal_selesai"', false);

        $this->actingAs($piket)
            ->post(route('piket.absensi_guru.store'), [
                'guru_id' => $guruSakit->id,
                'tanggal' => '2026-11-03',
                'status' => 'hadir',
            ])
            ->assertForbidden();

        $this->assertSame(
            'sakit',
            AbsensiGuru::where('guru_id', $guruSakit->id)
                ->whereDate('tanggal', '2026-11-03')
                ->value('status')
        );
    }

    public function test_evidence_is_available_on_each_attendance_record_in_a_date_range(): void
    {
        Storage::fake('local');
        $admin = $this->createUserWithRole('Admin');
        $guru = Guru::create(['nama' => 'Guru Dengan Bukti Rentang']);
        $file = UploadedFile::fake()->createWithContent('surat.pdf', "%PDF-1.4\nSurat sakit\n%%EOF");

        $this->actingAs($admin)
            ->post(route('admin.absensi_guru.store'), [
                'guru_id' => $guru->id,
                'tanggal' => '2026-11-02',
                'tanggal_mulai' => '2026-11-02',
                'tanggal_selesai' => '2026-11-03',
                'status' => 'sakit',
                'bukti' => $file,
            ])
            ->assertSessionHas('success', 'Absensi guru berhasil disimpan dan dikunci dari 02/11/2026 sampai 03/11/2026.');

        $absensi = AbsensiGuru::where('guru_id', $guru->id)->orderBy('tanggal')->get();
        $this->assertCount(2, $absensi);
        $this->assertNotSame($absensi[0]->bukti_token, $absensi[1]->bukti_token);

        foreach ($absensi as $catatan) {
            $this->assertNotEmpty($catatan->bukti_path);
            $this->assertNotEmpty($catatan->bukti_token);
            Storage::disk('local')->assertExists($catatan->bukti_path);
        }
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
