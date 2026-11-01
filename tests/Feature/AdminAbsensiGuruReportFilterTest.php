<?php

namespace Tests\Feature;

use App\Models\AbsensiGuru;
use App\Models\Guru;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAbsensiGuruReportFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_personal_report_is_limited_to_the_logged_in_teacher(): void
    {
        $role = Role::firstOrCreate(['role_name' => 'Guru']);
        $guruSaya = Guru::create(['nama' => 'Guru Saya']);
        $guruLain = Guru::create(['nama' => 'Guru Lainnya']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'guru_id' => $guruSaya->id,
            'is_active' => true,
        ]);

        AbsensiGuru::create([
            'guru_id' => $guruSaya->id,
            'tanggal' => '2026-10-28',
            'status' => 'izin',
            'keterangan' => 'Catatan saya',
        ]);
        AbsensiGuru::create([
            'guru_id' => $guruLain->id,
            'tanggal' => '2026-10-28',
            'status' => 'sakit',
            'keterangan' => 'Catatan rahasia guru lain',
        ]);

        $this->actingAs($user)
            ->get(route('guru.absensi_guru.laporan', ['guru_id' => $guruLain->id]))
            ->assertOk()
            ->assertSee('Guru Saya')
            ->assertSee('Catatan saya')
            ->assertSee('Rekap Absensi Saya')
            ->assertDontSee('Guru Lainnya')
            ->assertDontSee('Catatan rahasia guru lain')
            ->assertDontSee('name="guru_id"', false);
    }

    public function test_teacher_without_linked_teacher_record_cannot_open_personal_report(): void
    {
        $role = Role::firstOrCreate(['role_name' => 'Guru']);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('guru.absensi_guru.laporan'))
            ->assertForbidden();
    }

    public function test_report_print_contains_only_selected_teacher_and_separates_status_notes(): void
    {
        $role = Role::firstOrCreate(['role_name' => 'Admin']);
        $admin = User::factory()->create([
            'role_id' => $role->id,
            'is_active' => true,
        ]);
        $selectedGuru = Guru::create([
            'nama' => 'Guru Terpilih',
            'nip' => '1987654321',
            'pangkat_golongan' => 'III/a',
        ]);
        User::factory()->create([
            'guru_id' => $selectedGuru->id,
            'foto' => 'user_photos/terpilih.jpg',
            'is_active' => true,
        ]);
        $otherGuru = Guru::create(['nama' => 'Guru Lain']);

        AbsensiGuru::create([
            'guru_id' => $selectedGuru->id,
            'tanggal' => '2026-10-27',
            'status' => 'sakit',
            'keterangan' => 'Surat dokter',
        ]);
        AbsensiGuru::create([
            'guru_id' => $selectedGuru->id,
            'tanggal' => '2026-10-28',
            'status' => 'izin',
            'keterangan' => 'Izin keperluan keluarga',
        ]);
        AbsensiGuru::create([
            'guru_id' => $selectedGuru->id,
            'tanggal' => '2026-10-29',
            'status' => 'dinas_luar',
            'keterangan' => 'Tugas luar sekolah',
        ]);
        AbsensiGuru::create([
            'guru_id' => $selectedGuru->id,
            'tanggal' => '2026-10-30',
            'status' => 'izin',
            'keterangan' => 'Di luar periode laporan',
        ]);
        AbsensiGuru::create([
            'guru_id' => $otherGuru->id,
            'tanggal' => '2026-10-28',
            'status' => 'izin',
            'keterangan' => 'Catatan guru lain',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.absensi_guru.laporan', [
                'guru_id' => $selectedGuru->id,
                'start_date' => '2026-10-27',
                'end_date' => '2026-10-29',
            ]))
            ->assertOk()
            ->assertSee('Guru Terpilih')
            ->assertSee('Nama:')
            ->assertSee('NIP:')
            ->assertSee('1987654321')
            ->assertSee('Golongan:')
            ->assertSee('III/a')
            ->assertSee('/storage/user_photos/terpilih.jpg')
            ->assertDontSee('<th rowspan="2">NAMA</th>', false)
            ->assertDontSee('<th rowspan="2">NIP</th>', false)
            ->assertDontSee('<th rowspan="2">Golongan</th>', false)
            ->assertSee('Rekap Kehadiran')
            ->assertSee('Rincian Ketidakhadiran')
            ->assertSee('<th>Status</th>', false)
            ->assertSee('<th>Tanggal</th>', false)
            ->assertSee('<th>Keterangan</th>', false)
            ->assertDontSee('<td>Guru Lain</td>', false)
            ->assertDontSee('Catatan guru lain')
            ->assertSee('Sakit')
            ->assertSee('Izin')
            ->assertSee('Dinas Luar')
            ->assertSee('27/10/2026')
            ->assertSee('28/10/2026')
            ->assertSee('29/10/2026')
            ->assertDontSee('30/10/2026')
            ->assertSee('Izin keperluan keluarga')
            ->assertSee('Surat dokter')
            ->assertSee('Tugas luar sekolah');
    }
}
