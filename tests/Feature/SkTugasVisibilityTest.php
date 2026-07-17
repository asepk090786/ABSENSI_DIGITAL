<?php

namespace Tests\Feature;

use App\Models\SkTugas;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SkTugasVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('sk_tugas');

        Schema::create('sk_tugas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guru_id')->nullable();
            $table->string('judul');
            $table->string('file');
            $table->boolean('is_visible_to_guru')->default(true);
            $table->timestamps();
        });
    }

    public function test_visible_to_guru_scope_only_returns_visible_items(): void
    {
        SkTugas::create([
            'judul' => 'Visible Item',
            'file' => 'visible.pdf',
            'is_visible_to_guru' => true,
        ]);

        SkTugas::create([
            'judul' => 'Hidden Item',
            'file' => 'hidden.pdf',
            'is_visible_to_guru' => false,
        ]);

        $items = SkTugas::visibleToGuru()->get();

        $this->assertSame(1, $items->count());
        $this->assertSame('Visible Item', $items->first()->judul);
    }
}
