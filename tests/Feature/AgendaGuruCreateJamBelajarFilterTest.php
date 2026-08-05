<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AgendaGuruCreateJamBelajarFilterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $tahunId = DB::table('tahun_ajaran')->where('nama_tahun', '2026/2027')->value('id');
        if (! $tahunId) {
            $tahunId = DB::table('tahun_ajaran')->insertGetId([
                'nama_tahun' => '2026/2027',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $semesterId = DB::table('semester')->where('nama_semester', 'Ganjil')->value('id');
        if (! $semesterId) {
            $semesterId = DB::table('semester')->insertGetId([
                'nama_semester' => 'Ganjil',
                'tahun_ajaran_id' => $tahunId,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $guruId = DB::table('guru')->insertGetId([
            'nama' => 'Guru Uji',
            'nip' => '1234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Ensure a kelas and mata_pelajaran exist for ids used in jadwal_kbm
        $kelasId = DB::table('kelas')->where('nama_kelas', 'Kelas Uji')->value('id') ?: DB::table('kelas')->insertGetId([
            'nama_kelas' => 'Kelas Uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mapelId = DB::table('mata_pelajaran')->where('kode_mapel', 'MAPEL-UJI')->value('id') ?: DB::table('mata_pelajaran')->insertGetId([
            'kode_mapel' => 'MAPEL-UJI',
            'nama_mapel' => 'Mapel Uji',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::create([
            'name' => 'Guru Uji',
            'username' => 'guruuji',
            'email' => 'guruuji@example.com',
            'password' => bcrypt('password'),
            'guru_id' => $guruId,
        ]);

        $this->actingAs($user);

        // Insert jam_belajar and capture IDs
        $jam1 = DB::table('jam_belajar')->insertGetId(['hari' => 'Kamis', 'urutan' => 1, 'jam_mulai' => '07:00', 'jam_selesai' => '07:45', 'jenis' => 'KBM', 'created_at' => now(), 'updated_at' => now()]);
        $jam2 = DB::table('jam_belajar')->insertGetId(['hari' => 'Kamis', 'urutan' => 2, 'jam_mulai' => '07:45', 'jam_selesai' => '08:30', 'jenis' => 'KBM', 'created_at' => now(), 'updated_at' => now()]);
        $jam3 = DB::table('jam_belajar')->insertGetId(['hari' => 'Kamis', 'urutan' => 5, 'jam_mulai' => '10:00', 'jam_selesai' => '10:45', 'jenis' => 'KBM', 'created_at' => now(), 'updated_at' => now()]);
        $jam4 = DB::table('jam_belajar')->insertGetId(['hari' => 'Kamis', 'urutan' => 6, 'jam_mulai' => '10:45', 'jam_selesai' => '11:30', 'jenis' => 'KBM', 'created_at' => now(), 'updated_at' => now()]);

        DB::table('jadwal_kbm')->insertOrIgnore([
            ['kelas_id' => $kelasId, 'guru_id' => $guruId, 'mata_pelajaran_id' => $mapelId, 'jam_belajar_id' => $jam1, 'hari' => 'Kamis', 'jam_ke' => 1, 'tahun_ajaran_id' => $tahunId, 'semester_id' => $semesterId, 'created_at' => now(), 'updated_at' => now()],
            ['kelas_id' => $kelasId, 'guru_id' => $guruId, 'mata_pelajaran_id' => $mapelId, 'jam_belajar_id' => $jam2, 'hari' => 'Kamis', 'jam_ke' => 2, 'tahun_ajaran_id' => $tahunId, 'semester_id' => $semesterId, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_create_view_only_shows_jam_that_exist_in_teacher_schedule_for_selected_day(): void
    {
        $response = $this->get('/agenda_guru/create?tanggal=2026-07-23');

        $response->assertOk();
        $response->assertSee('10:00 - 10:45');
        $response->assertSee('10:45 - 11:30');
        $response->assertDontSee('07:00 - 07:45');
        $response->assertDontSee('07:45 - 08:30');
    }
}
