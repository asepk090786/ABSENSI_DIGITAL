<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class PengembanganTemplateEditorModeTest extends TestCase
{
    public function test_store_persists_editor_mode()
    {
        if (! Schema::hasTable('pengembangan_sertifikat_templates')) {
            Schema::create('pengembangan_sertifikat_templates', function (Blueprint $table) {
                $table->id();
                $table->string('nama')->nullable();
                $table->text('template_html')->nullable();
                $table->string('editor_mode')->nullable();
                $table->boolean('include_verification_code')->default(false);
                $table->boolean('include_verification_qr')->default(false);
                $table->timestamps();
            });
        }

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

    public function test_store_persists_include_verification_code_and_qr_size()
    {
        DB::table('pengembangan_sertifikat_templates')->truncate();

        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('pengembangan.templates.store'), [
            'nama' => 'Template QR',
            'template_html' => '<div>QR</div>',
            'editor_mode' => 'image',
            'include_verification_code' => '1',
            'include_verification_qr' => '1',
        ]);

        $response->assertRedirect(route('pengembangan.templates.index'));
        $this->assertDatabaseHas('pengembangan_sertifikat_templates', [
            'nama' => 'Template QR',
            'include_verification_code' => 1,
            'include_verification_qr' => 1,
        ]);
    }
}
