<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class PagesSmokeTest extends TestCase
{
    public function test_public_pages()
    {
        $this->get('/login')->assertStatus(200);
    }

    public function test_protected_pages_redirect_when_guest()
    {
        $this->get('/home')->assertStatus(302);
        $this->get('/jam_belajar')->assertStatus(302);
        $this->get('/agenda_kelas')->assertStatus(302);
        $this->get('/setting')->assertStatus(302);
    }

    public function test_protected_pages_accessible_when_authenticated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get('/home')->assertStatus(200);
        $this->get('/jam_belajar')->assertStatus(200);
        $this->get('/agenda_kelas')->assertStatus(200);
        $this->get('/setting')->assertStatus(403);
    }
}
