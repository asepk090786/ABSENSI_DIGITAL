<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
