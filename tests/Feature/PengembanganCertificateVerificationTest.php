<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PengembanganCertificateVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('pengembangan_sertifikats');
        Schema::dropIfExists('pengembangan_diri');

        Schema::create('pengembangan_diri', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kegiatan')->nullable();
            $table->string('tema_kegiatan')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->timestamps();
        });

        Schema::create('pengembangan_sertifikats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengembangan_id');
            $table->string('peserta_type');
            $table->unsignedBigInteger('peserta_id')->nullable();
            $table->string('peserta_name')->nullable();
            $table->string('file_path')->nullable();
            $table->string('barcode')->nullable();
            $table->string('nomor_sertifikat')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('pengembangan_sertifikats');
        Schema::dropIfExists('pengembangan_diri');
        parent::tearDown();
    }

    public function test_verify_route_shows_event_date_and_participant()
    {
        $pid = DB::table('pengembangan_diri')->insertGetId([
            'nama_kegiatan' => 'Kegiatan Uji QR',
            'tema_kegiatan' => 'Tema Tes',
            'tanggal_mulai' => '2026-08-01',
            'tanggal_selesai' => '2026-08-02',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $barcode = 'TEST-BARCODE-'.uniqid();

        DB::table('pengembangan_sertifikats')->insert([
            'pengembangan_id' => $pid,
            'peserta_type' => 'external',
            'peserta_id' => null,
            'peserta_name' => 'John Doe',
            'file_path' => null,
            'barcode' => $barcode,
            'nomor_sertifikat' => null,
            'template_id' => null,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $controller = new \App\Http\Controllers\PengembanganController();
        $response = $controller->verify($barcode);

        // If controller returned a View, inspect its data directly to avoid rendering layout
        if ($response instanceof \Illuminate\Contracts\View\View || $response instanceof \Illuminate\View\View) {
            $data = $response->getData();
            $this->assertArrayHasKey('item', $data);
            $this->assertArrayHasKey('participant_name', $data);
            $this->assertEquals('Kegiatan Uji QR', $data['item']->nama_kegiatan);
            $this->assertEquals('John Doe', $data['participant_name']);
            $this->assertEquals('2026-08-01', $data['item']->tanggal_mulai->format('Y-m-d'));
        } else {
            $this->fail('Expected a View from verify controller');
        }
    }
}
