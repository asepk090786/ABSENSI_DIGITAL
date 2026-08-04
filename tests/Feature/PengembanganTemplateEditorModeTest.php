<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PengembanganTemplateEditorModeTest extends TestCase
{
    public function test_store_persists_editor_mode()
    {
        DB::table('pengembangan_sertifikat_templates')->truncate();

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('pengembangan.templates.store'), [
            'nama' => 'Template Uji',
            'template_html' => '<div>Isi template</div>',
            'editor_mode' => 'html',
        ]);

        $response->assertRedirect(route('pengembangan.templates.index'));
        $this->assertDatabaseHas('pengembangan_sertifikat_templates', [
            'nama' => 'Template Uji',
            'editor_mode' => 'html',
        ]);
    }

    public function test_store_persists_include_barcode_and_qr_size()
    {
        DB::table('pengembangan_sertifikat_templates')->truncate();

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('pengembangan.templates.store'), [
            'nama' => 'Template QR',
            'template_html' => '<div>QR</div>',
            'editor_mode' => 'image',
            'include_barcode' => '1',
        ]);

        $response->assertRedirect(route('pengembangan.templates.index'));
        $this->assertDatabaseHas('pengembangan_sertifikat_templates', [
            'nama' => 'Template QR',
            'include_barcode' => 1,
            'barcode_is_qr' => 1,
            'barcode_qr_size' => 180,
        ]);
    }
}
