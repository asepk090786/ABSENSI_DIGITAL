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

        Schema::dropIfExists('pengembangan_sertifikats');
        Schema::dropIfExists('pengembangan_peserta');
        Schema::dropIfExists('pengembangan_diri');
        Schema::dropIfExists('guru');
        Schema::dropIfExists('siswa');

        Schema::create('guru', function ($table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('siswa', function ($table) {
            $table->id();
            $table->string('nama');
            $table->timestamps();
        });

        Schema::create('pengembangan_diri', function ($table) {
            $table->id();
            $table->string('nama_kegiatan');
            $table->timestamps();
        });

        Schema::create('pengembangan_peserta', function ($table) {
            $table->id();
            $table->foreignId('pengembangan_id');
            $table->string('peserta_type');
            $table->unsignedBigInteger('peserta_id');
            $table->timestamps();
        });

        Schema::create('pengembangan_sertifikats', function ($table) {
            $table->id();
            $table->foreignId('pengembangan_id');
            $table->string('peserta_type');
            $table->unsignedBigInteger('peserta_id');
            $table->string('file_path')->nullable();
            $table->string('barcode')->unique();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
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
