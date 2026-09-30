<?php

namespace Tests\Feature;

use App\Models\CapaianPembelajaran;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\KomponenNilai;
use App\Models\MataPelajaran;
use App\Models\Role;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\AssessmentCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KomponenNilaiVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_user_only_sees_components_created_by_their_account(): void
    {
        $ownerGuru = Guru::create(['nama' => 'Guru Pemilik', 'nip' => '1001']);
        $viewerGuru = Guru::create(['nama' => 'Guru Lihat', 'nip' => '1002']);

        $owner = User::factory()->create([
            'guru_id' => $ownerGuru->id,
        ]);

        $viewer = User::factory()->create([
            'guru_id' => $viewerGuru->id,
        ]);

        $capaian = CapaianPembelajaran::create([
            'nama_capaian_pembelajaran' => 'Capaian milik guru lain',
            'user_id' => $owner->id,
        ]);

        KomponenNilai::create([
            'capaian_pembelajaran_id' => $capaian->id,
            'guru_id' => $ownerGuru->id,
            'nama_komponen' => 'Komponen dari guru lain',
            'bobot' => 20,
        ]);
        KomponenNilai::create([
            'guru_id' => $viewerGuru->id,
            'nama_komponen' => 'Komponen milik guru yang login',
            'bobot' => 30,
        ]);

        $response = $this->actingAs($viewer)->get(route('komponen_nilai.index'));

        $response->assertOk();
        $response->assertDontSee('Komponen dari guru lain');
        $response->assertDontSee('Capaian milik guru lain');
        $response->assertDontSee('Filter Guru');
    }

    public function test_guru_filter_only_shows_components_owned_by_selected_teacher(): void
    {
        $role = Role::create(['role_name' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $selectedGuru = Guru::create(['nama' => 'Guru Terpilih', 'nip' => '1003']);
        $otherGuru = Guru::create(['nama' => 'Guru Lain', 'nip' => '1004']);

        KomponenNilai::create([
            'guru_id' => $selectedGuru->id,
            'nama_komponen' => 'Komponen Guru Terpilih',
        ]);
        KomponenNilai::create([
            'guru_id' => $otherGuru->id,
            'nama_komponen' => 'Komponen Guru Lain',
        ]);

        $response = $this->actingAs($admin)->get(route('komponen_nilai.index', [
            'guru_id' => $selectedGuru->id,
        ]));

        $response->assertOk();
        $response->assertDontSee('Komponen Guru Lain');
    }

    public function test_empty_guru_filter_does_not_show_all_components(): void
    {
        $role = Role::create(['role_name' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $guru = Guru::create(['nama' => 'Guru Uji', 'nip' => '1005']);

        KomponenNilai::create([
            'guru_id' => $guru->id,
            'nama_komponen' => 'Komponen Yang Tidak Ditampilkan',
        ]);

        $response = $this->actingAs($admin)->get(route('komponen_nilai.index', [
            'guru_id' => '',
        ]));

        $response->assertOk();
        $response->assertDontSee('Komponen Yang Tidak Ditampilkan');
    }

    public function test_teacher_only_sees_own_penilaian_components_on_penilaian_tab(): void
    {
        $ownerGuru = Guru::create(['nama' => 'Guru Pemilik Penilaian', 'nip' => '1006']);
        $otherGuru = Guru::create(['nama' => 'Guru Lain', 'nip' => '1007']);

        $owner = User::factory()->create(['guru_id' => $ownerGuru->id]);
        User::factory()->create(['guru_id' => $otherGuru->id]);

        KomponenNilai::create([
            'guru_id' => $ownerGuru->id,
            'nama_komponen' => 'Penilaian Milik Saya',
            'bobot' => 50,
        ]);

        KomponenNilai::create([
            'guru_id' => $otherGuru->id,
            'nama_komponen' => 'Penilaian Milik Guru Lain',
            'bobot' => 40,
        ]);

        $response = $this->actingAs($owner)->get(route('komponen_nilai.index'));

        $response->assertOk();
        $response->assertSee('Penilaian Milik Saya');
        $response->assertDontSee('Penilaian Milik Guru Lain');
    }

    public function test_final_rekap_statistics_are_scoped_to_teacher_owner(): void
    {
        $ownerGuru = Guru::create(['nama' => 'Guru Rekap', 'nip' => '1008']);
        $otherGuru = Guru::create(['nama' => 'Guru Lain Rekap', 'nip' => '1009']);

        $kelas = Kelas::create(['nama_kelas' => 'VII A', 'tingkat_kelas' => 7, 'wali_kelas_id' => $ownerGuru->id]);
        $mapel = MataPelajaran::create(['nama_mapel' => 'Biologi']);
        $tahun = TahunAjaran::create(['nama_tahun' => '2025/2026', 'is_active' => true]);
        $semester = Semester::create(['tahun_ajaran_id' => $tahun->id, 'nama_semester' => 'Ganjil', 'is_active' => true]);

        $siswa = Siswa::create([
            'kelas_id' => $kelas->id,
            'nis' => '001',
            'nisn' => '001',
            'nama' => 'Siswa Uji',
        ]);

        $ownerKognitif = KomponenNilai::create([
            'guru_id' => $ownerGuru->id,
            'nama_komponen' => 'Kognitif Rekap',
            'bobot' => 100,
            'domain' => 'kognitif',
        ]);
        $ownerAfektif = KomponenNilai::create([
            'guru_id' => $ownerGuru->id,
            'nama_komponen' => 'Afektif Rekap',
            'bobot' => 100,
            'domain' => 'afektif',
        ]);
        $ownerPsikomotorik = KomponenNilai::create([
            'guru_id' => $ownerGuru->id,
            'nama_komponen' => 'Psikomotorik Rekap',
            'bobot' => 100,
            'domain' => 'psikomotorik',
        ]);

        $otherKognitif = KomponenNilai::create([
            'guru_id' => $otherGuru->id,
            'nama_komponen' => 'Kognitif Lain',
            'bobot' => 100,
            'domain' => 'kognitif',
        ]);
        $otherAfektif = KomponenNilai::create([
            'guru_id' => $otherGuru->id,
            'nama_komponen' => 'Afektif Lain',
            'bobot' => 100,
            'domain' => 'afektif',
        ]);
        $otherPsikomotorik = KomponenNilai::create([
            'guru_id' => $otherGuru->id,
            'nama_komponen' => 'Psikomotorik Lain',
            'bobot' => 100,
            'domain' => 'psikomotorik',
        ]);

        DB::table('nilai_harian')->insert([
            [
                'siswa_id' => $siswa->id,
                'guru_id' => $ownerGuru->id,
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'komponen_id' => $ownerKognitif->id,
                'tanggal' => '2026-09-01',
                'nilai' => 80,
                'tahun_ajaran_id' => $tahun->id,
                'semester_id' => $semester->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'siswa_id' => $siswa->id,
                'guru_id' => $ownerGuru->id,
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'komponen_id' => $ownerAfektif->id,
                'tanggal' => '2026-09-02',
                'nilai' => 80,
                'tahun_ajaran_id' => $tahun->id,
                'semester_id' => $semester->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'siswa_id' => $siswa->id,
                'guru_id' => $ownerGuru->id,
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'komponen_id' => $ownerPsikomotorik->id,
                'tanggal' => '2026-09-03',
                'nilai' => 80,
                'tahun_ajaran_id' => $tahun->id,
                'semester_id' => $semester->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'siswa_id' => $siswa->id,
                'guru_id' => $otherGuru->id,
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'komponen_id' => $otherKognitif->id,
                'tanggal' => '2026-09-04',
                'nilai' => 20,
                'tahun_ajaran_id' => $tahun->id,
                'semester_id' => $semester->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'siswa_id' => $siswa->id,
                'guru_id' => $otherGuru->id,
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'komponen_id' => $otherAfektif->id,
                'tanggal' => '2026-09-05',
                'nilai' => 20,
                'tahun_ajaran_id' => $tahun->id,
                'semester_id' => $semester->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'siswa_id' => $siswa->id,
                'guru_id' => $otherGuru->id,
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'komponen_id' => $otherPsikomotorik->id,
                'tanggal' => '2026-09-06',
                'nilai' => 20,
                'tahun_ajaran_id' => $tahun->id,
                'semester_id' => $semester->id,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $calculator = new AssessmentCalculator();
        $result = $calculator->getClassStatistics(
            $kelas->id,
            $mapel->id,
            $tahun->id,
            $semester->id,
            $ownerGuru->id
        );

        $this->assertSame(80.0, $result['stats']['rata_rata_kelas']);
        $this->assertSame('TUNTAS', $result['assessments'][0]['status']);
    }
}
