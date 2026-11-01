<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\TugasGuru;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CbtMasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_data_export_requires_api_key(): void
    {
        $this->getJson('/api/cbt/master-data')->assertUnauthorized();
    }

    public function test_academic_and_teaching_exports_require_api_key(): void
    {
        $this->getJson('/api/cbt/academic-data')->assertUnauthorized();
        $this->getJson('/api/cbt/teaching-data')->assertUnauthorized();
    }

    public function test_api_key_rotation_allows_overlap_then_rejects_revoked_key(): void
    {
        $oldKey = $this->createApiKey();
        $newKey = $this->createApiKey();

        $this->getJson('/api/cbt/master-data', ['X-API-Key' => $oldKey])->assertOk();
        $this->getJson('/api/cbt/master-data', ['X-API-Key' => $newKey])->assertOk();

        ApiKey::query()
            ->where('key_hash', hash('sha256', $oldKey))
            ->update(['revoked_at' => now()]);

        $this->getJson('/api/cbt/master-data', ['X-API-Key' => $oldKey])->assertUnauthorized();
        $this->getJson('/api/cbt/master-data', ['X-API-Key' => $newKey])->assertOk();
    }

    public function test_export_contains_minimal_teacher_and_student_identity_without_passwords(): void
    {
        $apiKey = $this->createApiKey();

        $teacher = Guru::create([
            'nip' => '198001012005011001',
            'nama' => 'Guru Uji',
            'is_active' => true,
        ]);
        $teacherUser = User::factory()->create([
            'name' => 'Guru Uji',
            'username' => 'guru.sso',
            'guru_id' => $teacher->id,
            'password' => 'secret-password',
            'is_active' => true,
        ]);

        $class = Kelas::create([
            'nama_kelas' => 'Kelas Uji',
            'kode_kelas' => 'KU-1',
            'tingkat_kelas' => '10',
            'jurusan' => 'RPL',
            'wali_kelas_id' => $teacher->id,
        ]);
        $student = Siswa::create([
            'nis' => '12345',
            'nisn' => '0012345678',
            'nama' => 'Siswa Uji',
            'kelas_id' => $class->id,
            'status_aktif' => true,
        ]);
        $studentUser = User::factory()->create([
            'name' => 'Siswa Uji',
            'username' => 'siswa.sso',
            'siswa_id' => $student->id,
            'password' => 'another-secret',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/cbt/master-data?per_page=10', [
            'X-API-Key' => $apiKey,
        ])->assertOk()
            ->assertJsonPath('source', 'simadis')
            ->assertJsonPath('schema_version', 1)
            ->assertJsonPath('password_included', false)
            ->assertJsonPath('guru.data.0.simadis_id', (string) $teacher->id)
            ->assertJsonPath('guru.data.0.simadis_user_id', (string) $teacherUser->id)
            ->assertJsonPath('guru.data.0.nip', '198001012005011001')
            ->assertJsonPath('guru.data.0.username', $teacherUser->username)
            ->assertJsonPath('siswa.data.0.simadis_id', (string) $student->id)
            ->assertJsonPath('siswa.data.0.simadis_user_id', (string) $studentUser->id)
            ->assertJsonPath('siswa.data.0.nis', '12345')
            ->assertJsonPath('siswa.data.0.nisn', '0012345678')
            ->assertJsonPath('siswa.data.0.foto_available', false)
            ->assertJsonPath('siswa.data.0.current_class_id', (string) $class->id)
            ->assertJsonPath('siswa.data.0.current_class.nama_kelas', 'Kelas Uji')
            ->assertJsonPath('siswa.data.0.current_class.kode_kelas', 'KU-1')
            ->assertJsonPath('siswa.data.0.username', $studentUser->username)
            ->assertJsonPath('guru.pagination.per_page', 10);

        $response->assertJsonMissingPath('guru.data.0.password');
        $response->assertJsonMissingPath('siswa.data.0.password');
        $response->assertJsonMissingPath('guru.data.0.email');
        $response->assertJsonMissingPath('siswa.data.0.email');
    }

    public function test_student_photo_export_is_api_key_protected_and_returns_the_current_profile_photo(): void
    {
        Storage::fake('public');
        $apiKey = $this->createApiKey();
        $student = Siswa::create([
            'nis' => '12345',
            'nisn' => '0012345678',
            'nama' => 'Siswa Foto',
            'status_aktif' => true,
        ]);
        $photo = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pU8AAAAASUVORK5CYII=',
            true
        );
        Storage::disk('public')->put('user_photos/siswa.png', $photo);
        User::factory()->create([
            'name' => 'Siswa Foto',
            'username' => 'siswa.foto',
            'siswa_id' => $student->id,
            'foto' => 'user_photos/siswa.png',
            'is_active' => true,
        ]);

        $this->getJson('/api/cbt/master-data', ['X-API-Key' => $apiKey])
            ->assertOk()
            ->assertJsonPath('siswa.data.0.foto_available', true);

        $this->get('/api/cbt/students/' . $student->id . '/photo')
            ->assertUnauthorized();

        $this->get('/api/cbt/students/' . $student->id . '/photo', ['X-API-Key' => $apiKey])
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertStreamedContent($photo);
    }

    public function test_student_photo_export_marks_a_missing_source_file_as_unavailable(): void
    {
        Storage::fake('public');
        $apiKey = $this->createApiKey();
        $student = Siswa::create([
            'nis' => '12345',
            'nisn' => '0012345678',
            'nama' => 'Siswa Foto',
            'status_aktif' => true,
        ]);
        User::factory()->create([
            'name' => 'Siswa Foto',
            'username' => 'siswa.foto',
            'siswa_id' => $student->id,
            'foto' => 'user_photos/missing.png',
            'is_active' => true,
        ]);

        $this->getJson('/api/cbt/master-data', ['X-API-Key' => $apiKey])
            ->assertOk()
            ->assertJsonPath('siswa.data.0.foto_available', false);
    }

    public function test_student_photo_export_supports_a_full_class_sync_without_the_default_api_throttle(): void
    {
        Storage::fake('public');
        $apiKey = $this->createApiKey();
        $student = Siswa::create([
            'nis' => '12345',
            'nisn' => '0012345678',
            'nama' => 'Siswa Foto',
            'status_aktif' => true,
        ]);
        Storage::disk('public')->put('user_photos/siswa.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/pU8AAAAASUVORK5CYII=',
            true
        ));
        User::factory()->create([
            'name' => 'Siswa Foto',
            'username' => 'siswa.foto',
            'siswa_id' => $student->id,
            'foto' => 'user_photos/siswa.png',
            'is_active' => true,
        ]);

        for ($request = 0; $request < 65; $request++) {
            $this->get('/api/cbt/students/' . $student->id . '/photo', ['X-API-Key' => $apiKey])
                ->assertOk();
        }
    }

    public function test_export_rejects_page_sizes_above_the_limit(): void
    {
        $this->getJson('/api/cbt/master-data?per_page=501', [
            'X-API-Key' => $this->createApiKey(),
        ])->assertUnprocessable();
    }

    public function test_academic_export_provides_period_classes_and_explicit_membership_limitations(): void
    {
        $apiKey = $this->createApiKey();
        $teacher = Guru::create([
            'nip' => '198001012005011001',
            'nama' => 'Guru Wali',
            'is_active' => true,
        ]);
        $teacherUser = User::factory()->create([
            'name' => 'Guru Wali',
            'username' => 'guru.wali',
            'guru_id' => $teacher->id,
            'is_active' => true,
        ]);
        $year = TahunAjaran::create(['nama_tahun' => '2026/2027', 'is_active' => true]);
        $semester = Semester::create([
            'tahun_ajaran_id' => $year->id,
            'nama_semester' => 'Ganjil',
            'is_active' => true,
        ]);
        $class = Kelas::create([
            'nama_kelas' => 'X RPL 1',
            'kode_kelas' => 'X-RPL-1',
            'tingkat_kelas' => '10',
            'jurusan' => 'RPL',
            'wali_kelas_id' => $teacher->id,
        ]);
        $student = Siswa::create([
            'nis' => '22222',
            'nama' => 'Siswa Uji',
            'kelas_id' => $class->id,
            'status_aktif' => true,
        ]);

        $this->getJson('/api/cbt/academic-data?per_page=10', [
            'X-API-Key' => $apiKey,
        ])->assertOk()
            ->assertJsonPath('source', 'simadis')
            ->assertJsonPath('schema_version', 1)
            ->assertJsonPath('password_included', false)
            ->assertJsonPath('class_membership_scope', 'current_only')
            ->assertJsonPath('period_membership_available', false)
            ->assertJsonPath('tahun_ajaran.data.0.label', '2026/2027')
            ->assertJsonPath('semester.data.0.label', 'Ganjil')
            ->assertJsonPath('semester.data.0.tahun_ajaran_label', '2026/2027')
            ->assertJsonPath('kelas.data.0.simadis_id', (string) $class->id)
            ->assertJsonPath('kelas.data.0.kode_kelas', 'X-RPL-1')
            ->assertJsonMissingPath('kelas.data.0.is_active')
            ->assertJsonPath('kelas.data.0.current_student_count', 1)
            ->assertJsonPath('kelas.data.0.wali_kelas.nip', '198001012005011001')
            ->assertJsonPath('kelas.data.0.wali_kelas.username', $teacherUser->username)
            ->assertJsonPath('limitations.class_period', 'SIMADIS class records are not linked to a specific academic year or semester.');
    }

    public function test_teaching_export_includes_subjects_assignments_and_limitations(): void
    {
        $apiKey = $this->createApiKey();
        $teacher = Guru::create([
            'nip' => '198001012005011001',
            'nama' => 'Guru Uji',
            'is_active' => true,
        ]);
        $subject = MataPelajaran::create([
            'kode_mapel' => 'MTK',
            'nama_mapel' => 'Matematika',
            'kategori' => 'Umum',
        ]);
        $class = Kelas::create([
            'nama_kelas' => 'X RPL 1',
            'kode_kelas' => 'X-RPL-1',
            'tingkat_kelas' => '10',
            'jurusan' => 'RPL',
        ]);
        $assignment = TugasGuru::create([
            'guru_id' => $teacher->id,
            'mata_pelajaran_id' => $subject->id,
            'tingkat_kelas' => '10',
            'kelas_id' => $class->id,
            'is_active' => true,
        ]);

        $this->getJson('/api/cbt/teaching-data?per_page=10', [
            'X-API-Key' => $apiKey,
        ])->assertOk()
            ->assertJsonPath('source', 'simadis')
            ->assertJsonPath('password_included', false)
            ->assertJsonPath('assignment_period_available', false)
            ->assertJsonPath('mata_pelajaran.data.0.kode_mapel', 'MTK')
            ->assertJsonPath('mata_pelajaran.data.0.nama_mapel', 'Matematika')
            ->assertJsonPath('penugasan.data.0.simadis_id', (string) $assignment->id)
            ->assertJsonPath('penugasan.data.0.guru.nip', '198001012005011001')
            ->assertJsonPath('penugasan.data.0.mata_pelajaran.kode_mapel', 'MTK')
            ->assertJsonPath('penugasan.data.0.kelas.kode_kelas', 'X-RPL-1')
            ->assertJsonPath('limitations.assignment_period', 'SIMADIS teaching assignments are not linked to an academic year or semester.')
            ->assertJsonPath('limitations.cbt_assignment_structure', 'Garuda CBT stores teacher subject assignments in serialized jabatan_guru fields; normalized mapping requires manual review.');
    }

    private function createApiKey(): string
    {
        $apiKey = Str::random(48);
        ApiKey::create([
            'name' => 'Garuda CBT master data test',
            'key_prefix' => substr($apiKey, 0, 12),
            'key_hash' => hash('sha256', $apiKey),
        ]);

        return $apiKey;
    }
}
