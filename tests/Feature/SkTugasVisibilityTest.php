<?php

namespace Tests\Feature;

use App\Models\SkTugas;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SkTugasVisibilityTest extends TestCase
{

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
