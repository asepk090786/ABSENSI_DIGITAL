<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\JadwalKbm;
use App\Models\Guru;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupervisiJadwalDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_jadwal_options_endpoint_works()
    {
        // Create admin user
        $admin = User::factory()->create(['role' => 'admin']);

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
        $guru = Guru::factory()->create();

        // Create some jadwal_kbm for this guru on Thursday
        JadwalKbm::create([
            'guru_id' => $guru->id,
            'kelas_id' => 1,
            'mata_pelajaran_id' => 1,
            'tahun_ajaran_id' => $tahunAjaran->id,
            'semester_id' => $semester->id,
            'hari' => 'Kamis',
            'jam_ke' => 1,
        ]);

        // Test the API endpoint
        $this->actingAs($admin)
            ->get("/akademik/supervisi/get-jadwal-options/{$guru->id}/2026-08-21")
            ->assertStatus(200)
            ->assertJsonIsArray();
    }
}
