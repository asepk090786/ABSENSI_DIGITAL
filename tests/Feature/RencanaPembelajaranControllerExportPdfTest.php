<?php

namespace Tests\Feature;

use App\Http\Controllers\RencanaPembelajaranController;
use App\Models\RencanaPembelajaran;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class RencanaPembelajaranControllerExportPdfTest extends TestCase
{
    public function test_it_uses_onlyoffice_pdf_response_when_conversion_succeeds(): void
    {
        Storage::put('rencana_pembelajaran/docx/rpp-test.docx', 'dummy docx content');

        URL::shouldReceive('to')->andReturn('https://example.test');
        URL::shouldReceive('forceRootUrl')->andReturnNull();
        URL::shouldReceive('temporarySignedRoute')->andReturn('https://example.test/rencana_pembelajaran/42/document');

        Http::fake([
            'http://onlyoffice.test/ConvertService.ashx' => Http::response([
                'fileUrl' => 'https://onlyoffice.test/download.pdf',
            ], 200, ['Content-Type' => 'application/json']),
            'https://onlyoffice.test/download.pdf' => Http::response('pdf-content', 200, ['Content-Type' => 'application/pdf']),
        ]);

        $controller = new class extends RencanaPembelajaranController {
            public function runTryExportPdfViaOnlyOffice(RencanaPembelajaran $rencanaPembelajaran, string $serverUrl, ?string $jwtToken = null)
            {
                return $this->tryExportPdfViaOnlyOffice($rencanaPembelajaran, $serverUrl, $jwtToken);
            }
        };

        $rencanaPembelajaran = new RencanaPembelajaran([
            'id' => 42,
            'judul' => 'RPP Uji Coba',
            'original_docx_path' => 'rencana_pembelajaran/docx/rpp-test.docx',
            'updated_at' => now(),
        ]);

        $response = $controller->runTryExportPdfViaOnlyOffice($rencanaPembelajaran, 'http://onlyoffice.test', null);

        $this->assertNotNull($response);
        $this->assertSame('pdf-content', $response->getContent());
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Rencana_Pembelajaran_rpp-uji-coba', $response->headers->get('Content-Disposition'));
    }
}
