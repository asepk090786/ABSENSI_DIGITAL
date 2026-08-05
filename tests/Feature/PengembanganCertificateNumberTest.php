<?php

namespace Tests\Feature;

use App\Models\Pengembangan;
use App\Models\PengembanganPeserta;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PengembanganCertificateNumberTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable FK checks while manipulating test schemas to avoid SQLite FK errors
        Schema::disableForeignKeyConstraints();

        try { Schema::dropIfExists('pengembangan_sertifikats'); } catch (\Throwable $e) {}
        try { Schema::dropIfExists('pengembangan_peserta'); } catch (\Throwable $e) {}
        try { Schema::dropIfExists('pengembangan_diri'); } catch (\Throwable $e) {}
        try { Schema::dropIfExists('siswa'); } catch (\Throwable $e) {}
        try { Schema::dropIfExists('guru'); } catch (\Throwable $e) {}

        if (! Schema::hasTable('guru')) {
            Schema::create('guru', function ($table) {
                $table->id();
                $table->string('nama');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('siswa')) {
            Schema::create('siswa', function ($table) {
                $table->id();
                $table->string('nama');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('users')) {
            Schema::create('users', function ($table) {
                $table->id();
                $table->string('name');
                $table->string('username')->unique();
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pengembangan_diri')) {
            Schema::create('pengembangan_diri', function ($table) {
                $table->id();
                $table->string('nama_kegiatan');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pengembangan_peserta')) {
            Schema::create('pengembangan_peserta', function ($table) {
                $table->id();
                $table->foreignId('pengembangan_id');
                $table->string('peserta_type');
                $table->unsignedBigInteger('peserta_id');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pengembangan_sertifikats')) {
            Schema::create('pengembangan_sertifikats', function ($table) {
                $table->id();
                $table->foreignId('pengembangan_id');
                $table->string('peserta_type');
                $table->unsignedBigInteger('peserta_id');
                $table->string('peserta_name')->nullable();
                $table->string('instansi')->nullable();
                $table->string('file_path')->nullable();
                $table->string('nomor_sertifikat')->nullable();
                $table->string('barcode')->unique();
                $table->timestamp('verified_at')->nullable();
                $table->unsignedBigInteger('template_id')->nullable();
                $table->boolean('is_visible')->default(true);
                $table->string('bukti_dukung_daftar_hadir')->nullable();
            $table->text('bukti_dukung_dokumentasi')->nullable();
            $table->text('bukti_dukung_materi')->nullable();
            $table->timestamps();
        });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function test_generate_certificates_persists_certificate_number()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $pengembangan = Pengembangan::create([
            'nama_kegiatan' => 'Workshop Literasi',
        ]);

        $guruId = DB::table('guru')->insertGetId([
            'nama' => 'Budi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $participant = PengembanganPeserta::create([
            'pengembangan_id' => $pengembangan->id,
            'peserta_type' => 'guru',
            'peserta_id' => $guruId,
        ]);

        $response = $this->post(route('pengembangan.generate_certificates', $pengembangan->id), [
            'participant_ids' => [$participant->id],
            'nomor_sertifikat' => 'SRT-001/2026',
        ]);

        $response->assertRedirect(route('pengembangan.show', $pengembangan->id));
        $this->assertDatabaseHas('pengembangan_sertifikats', [
            'pengembangan_id' => $pengembangan->id,
            'peserta_type' => 'guru',
            'peserta_id' => $guruId,
            'nomor_sertifikat' => 'SRT-001/2026',
        ]);
    }
}
