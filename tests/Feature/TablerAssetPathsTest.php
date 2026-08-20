<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TablerAssetPathsTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_uses_cdn_tabler_assets(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $response->assertSee('https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css');
        $response->assertDontSee('/vendor/tabler/dist/css/tabler.min.css');
    }
}
