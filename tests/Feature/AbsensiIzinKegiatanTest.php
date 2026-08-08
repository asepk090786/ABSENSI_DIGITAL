<?php

namespace Tests\Feature;

use App\Models\AbsensiKelas;
use App\Models\AbsensiSiswa;
use App\Models\Guru;
use App\Models\JamBelajar;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class AbsensiIzinKegiatanTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_dispensasi_marks_attendance_as_hadir_and_blocks_manual_changes(): void
    {
        $guru = Guru::create([
            'nama' => 'Guru Uji',
            'nip' => '9001',
            'hari_piket' => [now()->locale('id')->translatedFormat('l')],
        ]);
        $user = User::factory()->create(['guru_id' => $guru->id]);

        $kelas = Kelas::create([
            'nama_kelas' => 'XII A',
            'kode_kelas' => '12A',
            'tingkat_kelas' => 'XII',
        ]);

        $siswa = Siswa::create([
            'nis' => '1001',
            'nisn' => '1001001',
            'nama' => 'Siswa Uji',
            'kelas_id' => $kelas->id,
            'status_aktif' => 1,
        ]);

        $jamBelajar = JamBelajar::create([
            'hari' => 'Senin',
            'urutan' => 1,
            'jam_mulai' => '07:00',
            'jam_selesai' => '08:00',
        ]);

        $tahunAjaran = TahunAjaran::create([
            'nama_tahun' => '2025/2026',
            'is_active' => true,
        ]);

        $semester = Semester::create([
            'tahun_ajaran_id' => $tahunAjaran->id,
            'nama_semester' => 'Ganjil',
            'is_active' => true,
        ]);

        $absensi = AbsensiKelas::create([
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'jam_belajar_id' => $jamBelajar->id,
            'tanggal' => now()->toDateString(),
            'tahun_ajaran_id' => $tahunAjaran->id,
            'semester_id' => $semester->id,
        ]);

        AbsensiSiswa::create([
            'absensi_kelas_id' => $absensi->id,
            'siswa_id' => $siswa->id,
            'status' => 'alpa',
            'keterangan' => 'awal',
        ]);

        $response = $this->actingAs($user)->post(route('absensi.izin-kegiatan.store', $absensi->id), [
            'siswa_ids' => [$siswa->id],
            'jenis_kegiatan' => 'dispensasi',
            'keterangan_kegiatan' => 'Kegiatan sekolah',
            'tanggal_mulai' => $absensi->tanggal->toDateString(),
            'tanggal_selesai' => $absensi->tanggal->copy()->addDay()->toDateString(),
        ]);

        $response->assertRedirect();

        $absensiSiswa = AbsensiSiswa::where('absensi_kelas_id', $absensi->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        $this->assertNotNull($absensiSiswa);
        $this->assertSame('hadir', $absensiSiswa->status);

        $changeResponse = $this->actingAs($user)->post(route('absensi.siswa.update_status', ['absensi' => $absensi->id, 'siswa' => $siswa->id]), [
            'status' => 'izin',
            'keterangan' => 'coba ubah',
        ]);

        $changeResponse->assertStatus(403);
    }
}
