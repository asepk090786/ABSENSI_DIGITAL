<?php

namespace Tests\Feature;

use App\Http\Controllers\RencanaPembelajaranController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory;
use Tests\TestCase;

class RencanaPembelajaranImportTest extends TestCase
{
    public function test_import_extracts_images_from_nested_word_elements(): void
    {
        $tmpDir = sys_get_temp_dir() . '/rpp-import-test-' . uniqid();
        mkdir($tmpDir, 0777, true);

        $imagePath = $tmpDir . '/sample.png';
        file_put_contents($imagePath, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAACklEQVR4nGMAAIAAeIhvAAAAAElFTkSuQmCC'));

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $section->addText('Contoh teks');
        $textRun = $section->addTextRun();
        $textRun->addImage($imagePath, ['width' => 120, 'height' => 80]);

        $docxPath = $tmpDir . '/sample.docx';
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($docxPath);

        $uploadedFile = new UploadedFile($docxPath, 'sample.docx', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', null, true);

        $request = new Request();
        $request->files->set('file', $uploadedFile);

        $controller = new RencanaPembelajaranController();
        $response = $controller->import($request);

        $this->assertSame(200, $response->getStatusCode());
        $payload = json_decode($response->getContent(), true);
        $this->assertTrue($payload['success'] ?? false);
        $this->assertNotEmpty($payload['images'] ?? []);
        $this->assertStringContainsString('modul-ajar-imports', $payload['images'][0]);
    }
}
