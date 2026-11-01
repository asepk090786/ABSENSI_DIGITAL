<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\AbsensiGuru;
use App\Models\AbsensiGuruLog;
use App\Models\Guru;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminAbsensiGuruAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_admin_attendance_input_and_edits_are_logged_with_actor_and_statuses(): void
    {
        $actorGuru = Guru::create(['nama' => 'Guru Piket']);
        $actor = $this->createUserWithRole('Admin', $actorGuru);
        $targetGuru = Guru::create(['nama' => 'Guru Absensi']);

        $this->actingAs($actor)->post(route('admin.absensi_guru.store'), [
            'tanggal' => '2026-10-24',
            'guru_id' => $targetGuru->id,
            'status' => 'hadir',
            'keterangan' => 'Catatan awal',
        ])->assertSessionHas('success', 'Absensi guru berhasil disimpan.');

        $editResponse = $this->actingAs($actor)->post(route('admin.absensi_guru.store'), [
            'tanggal' => '2026-10-24',
            'guru_id' => $targetGuru->id,
            'status' => 'sakit',
            'keterangan' => 'Catatan diperbarui',
        ]);
        $this->assertSame(
            'Absensi guru berhasil disimpan.',
            $editResponse->getSession()->get('success'),
            (string) $editResponse->getSession()->get('error')
        );

        $absensi = AbsensiGuru::where('guru_id', $targetGuru->id)->firstOrFail();
        $this->assertSame('Catatan diperbarui', $absensi->keterangan);
        $this->assertSame(2, AbsensiGuruLog::where('absensi_guru_id', $absensi->id)->count());

        $logs = $absensi->logs;
        $this->assertNull($logs[0]->old_status);
        $this->assertSame('hadir', $logs[0]->new_status);
        $this->assertSame('Guru Piket', $logs[0]->actor_name);
        $this->assertSame('hadir', $logs[1]->old_status);
        $this->assertSame('sakit', $logs[1]->new_status);

        DB::table('absensi_guru')
            ->where('id', $absensi->id)
            ->update(['tanggal' => '2026-10-24 08:30:00']);

        $this->actingAs($actor)
            ->get(route('admin.absensi_guru.index', ['tanggal' => '2026-10-24']))
            ->assertOk()
            ->assertSee('btn-success', false)
            ->assertSee('Edit')
            ->assertSee('Guru Piket mengubah status:')
            ->assertSee('Hadir')
            ->assertSee('Sakit')
            ->assertSee('Catatan diperbarui');
    }

    public function test_admin_accepts_dinas_luar_status_and_displays_label(): void
    {
        $actorGuru = Guru::create(['nama' => 'Guru Admin']);
        $actor = $this->createUserWithRole('Admin', $actorGuru);
        $targetGuru = Guru::create(['nama' => 'Guru Dinas']);

        $this->actingAs($actor)->post(route('admin.absensi_guru.store'), [
            'tanggal' => '2026-10-24',
            'guru_id' => $targetGuru->id,
            'status' => 'dinas_luar',
            'keterangan' => 'Kegiatan sekolah di luar kantor',
        ])->assertSessionHas('success', 'Absensi guru berhasil disimpan.');

        $this->assertSame('dinas_luar', AbsensiGuru::where('guru_id', $targetGuru->id)
            ->whereDate('tanggal', '2026-10-24')
            ->value('status'));

        $this->actingAs($actor)
            ->get(route('admin.absensi_guru.index', ['tanggal' => '2026-10-24']))
            ->assertOk()
            ->assertSee('Dinas Luar');
    }

    public function test_admin_attendance_report_renders_teacher_recap_and_attendance_percentage(): void
    {
        $actor = $this->createUserWithRole('Admin');
        $targetGuru = Guru::create([
            'nama' => 'Guru Laporan',
            'nip' => '123456',
            'pangkat_golongan' => 'III/a',
        ]);

        $this->actingAs($actor)->post(route('admin.absensi_guru.store'), [
            'tanggal' => '2026-10-24',
            'guru_id' => $targetGuru->id,
            'status' => 'hadir',
        ])->assertSessionHas('success', 'Absensi guru berhasil disimpan.');

        foreach ([
            ['2026-10-25', 'sakit', 'Surat dokter'],
            ['2026-10-26', 'izin', 'Keperluan keluarga'],
            ['2026-10-27', 'tidak_hadir', null],
            ['2026-10-28', 'dinas_luar', 'Kegiatan sekolah'],
            ['2026-10-29', 'hadir', null],
        ] as [$tanggal, $status, $keterangan]) {
            AbsensiGuru::create([
                'guru_id' => $targetGuru->id,
                'tanggal' => $tanggal,
                'status' => $status,
                'keterangan' => $keterangan,
            ]);
        }

        $this->actingAs($actor)
            ->get(route('admin.absensi_guru.laporan', [
                'start_date' => '2026-10-24',
                'end_date' => '2026-10-29',
            ]))
            ->assertOk()
            ->assertSee('REKAP KEHADIRAN GURU')
            ->assertSee('Nama')
            ->assertSee('NIP')
            ->assertSee('Golongan')
            ->assertSee('Dinas Luar')
            ->assertSee('Persentase Kehadiran')
            ->assertSee('Guru Laporan')
            ->assertSee('123456')
            ->assertSee('III/a')
            ->assertSee('Surat dokter')
            ->assertSee('33,3%');
    }

    public function test_piket_route_keeps_existing_behavior_without_admin_audit(): void
    {
        $actor = $this->createUserWithRole('Guru Piket');
        $targetGuru = Guru::create(['nama' => 'Guru Absensi']);
        $tanggal = now()->toDateString();

        $this->actingAs($actor)->post(route('piket.absensi_guru.store'), [
            'tanggal' => $tanggal,
            'guru_id' => $targetGuru->id,
            'status' => 'hadir',
        ])->assertRedirect();

        $this->assertSame('hadir', AbsensiGuru::where('guru_id', $targetGuru->id)
            ->whereDate('tanggal', $tanggal)
            ->value('status'));
        $this->assertDatabaseCount('absensi_guru_logs', 0);

        $this->actingAs($actor)
            ->get(route('piket.absensi_guru.index', ['tanggal' => $tanggal]))
            ->assertOk()
            ->assertSee('>Input', false)
            ->assertDontSee('class="absensi-audit-log"', false);
    }

    private function createUserWithRole(string $roleName, ?Guru $guru = null): User
    {
        $role = Role::firstOrCreate(['role_name' => $roleName]);

        return User::factory()->create([
            'role_id' => $role->id,
            'guru_id' => $guru?->id,
            'is_active' => true,
        ]);
    }
}
