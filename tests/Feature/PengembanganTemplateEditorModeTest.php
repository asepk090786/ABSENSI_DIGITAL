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
}
