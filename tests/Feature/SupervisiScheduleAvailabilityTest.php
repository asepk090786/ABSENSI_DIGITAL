<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\JadwalKbm;
use App\Models\JamBelajar;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Role;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisiScheduleAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_fetch_future_kbm_schedule_dates_outside_the_30_day_window(): void
    {
        $role = Role::create(['role_name' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $guru = Guru::create(['nama' => 'Guru Uji', 'nip' => '1001']);
        $tahun = TahunAjaran::create(['nama_tahun' => '2026/2027', 'is_active' => true]);
        $semester = Semester::create([
            'tahun_ajaran_id' => $tahun->id,
            'nama_semester' => 'Semester 1 (Ganjil)',
            'is_active' => true,
        ]);
        $kelas = Kelas::create([
            'nama_kelas' => 'XII A',
            'kode_kelas' => '12A',
            'tingkat_kelas' => 12,
        ]);
        $mataPelajaran = MataPelajaran::create([
            'kode_mapel' => 'BIO',
            'nama_mapel' => 'Biologi',
        ]);

        $targetDate = Carbon::today();
        $targetDate = $targetDate->next('Friday');
        while (Carbon::today()->diffInDays($targetDate, false) <= 210) {
            $targetDate = $targetDate->addWeek();
        }

        $dayName = match ($targetDate->format('l')) {
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        };

        $jamBelajar = JamBelajar::create([
            'hari' => $dayName,
            'urutan' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '07:45:00',
            'jenis' => 'KBM',
        ]);

        JadwalKbm::create([
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'mata_pelajaran_id' => $mataPelajaran->id,
            'jam_belajar_id' => $jamBelajar->id,
            'hari' => $dayName,
            'jam_ke' => 1,
            'tahun_ajaran_id' => $tahun->id,
            'semester_id' => $semester->id,
        ]);

        $availableDatesResponse = $this->actingAs($user)->getJson(route('akademik.supervisi.available_dates', $guru->id));
        $availableDatesResponse->assertOk();
        $this->assertContains($targetDate->toDateString(), $availableDatesResponse->json('dates'));

        $jadwalResponse = $this->actingAs($user)->getJson(route('akademik.supervisi.get_jadwal_options', [
            'guru' => $guru->id,
            'tanggal' => $targetDate->toDateString(),
        ]));

        $jadwalResponse->assertOk();
        $jadwalResponse->assertJsonCount(1);
        $jadwalResponse->assertJsonFragment([
            'kelas_nama' => 'XII A',
            'mata_pelajaran' => 'Biologi',
        ]);
    }
}
