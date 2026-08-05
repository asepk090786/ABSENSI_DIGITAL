<?php

namespace Tests\Feature;

use App\Http\Controllers\PengembanganController;
use App\Models\PengembanganSertifikat;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengembanganCertificateDownloadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('pengembangan_sertifikats');

        Schema::create('pengembangan_sertifikats', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengembangan_id');
            $table->string('peserta_type');
            $table->unsignedBigInteger('peserta_id')->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->string('barcode')->nullable();
            $table->string('nomor_sertifikat')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('pengembangan_sertifikats');

        parent::tearDown();
    }

    public function test_download_certificate_uses_public_storage_path(): void
    {
        Storage::disk('public')->put('certificates/test-download.pdf', 'pdf-content');

        $cert = PengembanganSertifikat::create([
            'pengembangan_id' => 1,
            'peserta_type' => 'guru',
            'peserta_id' => 1,
            'file_path' => 'certificates/test-download.pdf',
            'barcode' => 'abc123',
            'nomor_sertifikat' => null,
            'template_id' => null,
        ]);

        $response = (new PengembanganController())->downloadCertificate($cert->id);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('pdf-content', $response->getFile()->getContent());

        Storage::disk('public')->delete('certificates/test-download.pdf');
    }
}
