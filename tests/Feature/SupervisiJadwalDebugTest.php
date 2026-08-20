<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\JadwalKbm;
use App\Models\Guru;
use App\Models\JamBelajar;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupervisiJadwalDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_jadwal_options_endpoint_works()
    {
        // Create admin user
        $role = Role::create(['role_name' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);

        // Create tahun ajaran and semester
        $tahunAjaran = TahunAjaran::create([
            'nama_tahun' => '2026/2027',
            'is_active' => true,
        ]);

        $semester = Semester::create([
            'tahun_ajaran_id' => $tahunAjaran->id,
            'nama_semester' => 'Semester 1',
            'is_active' => true,
        ]);

        // Create a guru
        $guru = Guru::create(['nama' => 'Guru Uji', 'nip' => '1001']);
        $kelas = Kelas::create(['nama_kelas' => 'XI A']);
        $mataPelajaran = MataPelajaran::create(['nama_mapel' => 'Biologi']);
        $jamBelajar = JamBelajar::create([
            'hari' => 'Kamis',
            'urutan' => 1,
            'jam_mulai' => '07:00:00',
            'jam_selesai' => '07:45:00',
            'jenis' => 'KBM',
        ]);

        // Create some jadwal_kbm for this guru on Thursday
        JadwalKbm::create([
            'guru_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'mata_pelajaran_id' => $mataPelajaran->id,
            'jam_belajar_id' => $jamBelajar->id,
            'tahun_ajaran_id' => $tahunAjaran->id,
            'semester_id' => $semester->id,
            'hari' => 'Kamis',
            'jam_ke' => 1,
        ]);

        // Test the API endpoint
        $this->actingAs($admin)
            ->get("/akademik/supervisi/get-jadwal-options/{$guru->id}/2026-08-20")
            ->assertStatus(200)
            ->assertJsonIsArray();
    }
}
