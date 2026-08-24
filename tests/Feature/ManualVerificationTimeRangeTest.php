<?php

namespace Tests\Feature;

use App\Models\AbsensiKelas;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class ManualVerificationTimeRangeTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_can_save_manual_verification_with_time_range(): void
    {
        $guru = Guru::create(['nama' => 'Guru Penguji', 'nip' => '12345']);
        $user = User::factory()->create(['guru_id' => $guru->id]);
        $kelas = Kelas::create(['nama_kelas' => 'X-A', 'kode_kelas' => 'XA', 'tingkat_kelas' => 'X']);
        $tahun = TahunAjaran::create(['nama_tahun' => '2025/2026', 'is_active' => true]);
        $semester = Semester::create(['tahun_ajaran_id' => $tahun->id, 'nama_semester' => 'Ganjil', 'is_active' => true]);

        $response = $this->actingAs($user)->postJson(route('absensi.verification.manual.save'), [
            'kelas_id' => $kelas->id,
            'tanggal' => '2026-08-24',
            'verifikasi_manual_aktif' => 1,
            'verifikasi_manual_valid_from' => '07:00',
            'verifikasi_manual_valid_to' => '08:00',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'verificationManualActive' => true,
            'verificationManualValidFrom' => '07:00',
            'verificationManualValidTo' => '08:00',
        ]);

        $this->assertDatabaseHas('absensi_kelas', [
            'kelas_id' => $kelas->id,
            'tanggal' => '2026-08-24 00:00:00',
            'verifikasi_manual_aktif' => 1,
            'verifikasi_manual_valid_from' => '07:00:00',
            'verifikasi_manual_valid_to' => '08:00:00',
        ]);
    }

    public function test_rejects_invalid_time_range_where_end_is_before_start(): void
    {
        $guru = Guru::create(['nama' => 'Guru Penguji', 'nip' => '12345']);
        $user = User::factory()->create(['guru_id' => $guru->id]);
        $kelas = Kelas::create(['nama_kelas' => 'X-A', 'kode_kelas' => 'XA', 'tingkat_kelas' => 'X']);

        $response = $this->actingAs($user)->postJson(route('absensi.verification.manual.save'), [
            'kelas_id' => $kelas->id,
            'tanggal' => '2026-08-24',
            'verifikasi_manual_aktif' => 1,
            'verifikasi_manual_valid_from' => '08:00',
            'verifikasi_manual_valid_to' => '07:00',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Waktu akhir harus sama atau setelah waktu mulai.',
        ]);
    }

    public function test_load_verification_state_returns_manual_time_range(): void
    {
        $guru = Guru::create(['nama' => 'Guru Penguji', 'nip' => '12345']);
        $user = User::factory()->create(['guru_id' => $guru->id]);
        $kelas = Kelas::create(['nama_kelas' => 'X-A', 'kode_kelas' => 'XA', 'tingkat_kelas' => 'X']);
        $tahun = TahunAjaran::create(['nama_tahun' => '2025/2026', 'is_active' => true]);
        $semester = Semester::create(['tahun_ajaran_id' => $tahun->id, 'nama_semester' => 'Ganjil', 'is_active' => true]);

        AbsensiKelas::create([
            'kelas_id' => $kelas->id,
            'tanggal' => '2026-08-24',
            'tahun_ajaran_id' => $tahun->id,
            'semester_id' => $semester->id,
            'guru_id' => $guru->id,
            'verifikasi_manual_aktif' => true,
            'verifikasi_manual_valid_from' => '07:15',
            'verifikasi_manual_valid_to' => '08:30',
        ]);

        $response = $this->actingAs($user)->postJson(route('absensi.verification.load-state'), [
            'kelas_id' => $kelas->id,
            'tanggal' => '2026-08-24',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'verificationManualActive' => true,
            'verificationManualValidFrom' => '07:15',
            'verificationManualValidTo' => '08:30',
        ]);
    }

    public function test_view_contains_manual_verification_time_range_inputs(): void
    {
        $contents = file_get_contents(base_path('resources/views/absensi/create.blade.php'));

        $this->assertStringContainsString('id="verificationManualValidFrom"', $contents);
        $this->assertStringContainsString('id="verificationManualValidTo"', $contents);
        $this->assertStringContainsString('id="saveManualVerificationConfigBtn"', $contents);
        $this->assertStringContainsString('id="verificationManualConfigSection"', $contents);
    }
}
