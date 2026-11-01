<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NilaiIndexFormativeAverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_formative_average_is_shown_for_saved_scores_without_an_active_schedule(): void
    {
        $role = Role::create(['role_name' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $kelas = Kelas::create(['nama_kelas' => 'Kelas Uji']);
        $mapel = MataPelajaran::create(['nama_mapel' => 'Mapel Uji']);
        $siswa = Siswa::create([
            'nama' => 'Siswa Uji',
            'kelas_id' => $kelas->id,
        ]);
        $tahunAjaran = TahunAjaran::create(['nama_tahun' => '2026/2027']);
        $semester = Semester::create([
            'tahun_ajaran_id' => $tahunAjaran->id,
            'nama_semester' => 'Ganjil',
        ]);

        DB::table('nilai_harian')->insert([
            'siswa_id' => $siswa->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
            'tanggal' => '2026-09-01',
            'nilai' => 80,
            'kategori_asesmen' => 'formatif',
            'assessment_uuid' => 'b6c6846f-4ca9-4fb1-a379-b2a9712da9fe',
            'tahun_ajaran_id' => $tahunAjaran->id,
            'semester_id' => $semester->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('nilai.index', [
            'kelas_id' => $kelas->id,
        ]));

        $response->assertOk();
        $response->assertSee('Rata-rata: 80.00');
    }
}
