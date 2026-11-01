<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ZipArchive;
use Tests\TestCase;

class RencanaPembelajaranTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_modul_ajar_and_displays_it_in_the_table(): void
    {
        $this->withoutMiddleware();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('rencana_pembelajaran.store'), [
            'title' => 'Modul Uji',
            'subject' => 'Biologi',
            'class' => 'XI',
            'status' => 'Draft',
            'duration' => '3 JP',
            'achievement' => 'Capaian uji',
            'objectives' => 'Tujuan uji',
            'methods' => 'Diskusi',
            'media' => 'Video',
            'resources' => 'Buku',
            'practice' => 'PBL',
            'environment' => 'Kelas',
            'digital' => 'LMS',
            'experience' => 'Pendahuluan',
            'reflection' => 'Refleksi',
            'assessment' => 'Formatif',
        ]);

        $response->assertRedirect(route('rencana_pembelajaran.index'));
        $response->assertSessionHas('success');

        $this->followRedirects($response)
            ->assertSee('Modul Uji')
            ->assertSee('Biologi');
    }

    public function test_it_downloads_a_word_template_matching_the_create_preview(): void
    {
        $this->withoutMiddleware();

        $response = $this->get(route('rencana_pembelajaran.template_preview'));
        $response->assertOk()->assertDownload('template_modul_ajar.docx');

        $filePath = $response->baseResponse->getFile()->getPathname();

        try {
            $wordDocument = \PhpOffice\PhpWord\IOFactory::load($filePath, 'Word2007');
            $this->assertCount(1, $wordDocument->getSections());

            $archive = new ZipArchive();
            $this->assertSame(true, $archive->open($filePath));
            $documentXml = $archive->getFromName('word/document.xml');
            $archive->close();

            $this->assertNotFalse($documentXml);
            $documentText = html_entity_decode(strip_tags($documentXml), ENT_QUOTES | ENT_XML1, 'UTF-8');
            $this->assertStringContainsString('Informasi Umum', $documentText);
            $this->assertStringContainsString('Kelas / Fase', $documentText);
            $this->assertStringContainsString('Capaian Pembelajaran', $documentText);
            $this->assertStringContainsString('Metode Pembelajaran', $documentText);
            $this->assertStringContainsString('Asesmen', $documentText);
        } finally {
            if (is_file($filePath)) {
                unlink($filePath);
            }
        }
    }
}
