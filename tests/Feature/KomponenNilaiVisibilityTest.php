<?php

namespace Tests\Feature;

use App\Models\CapaianPembelajaran;
use App\Models\Guru;
use App\Models\KomponenNilai;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KomponenNilaiVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_user_can_see_komponen_nilai_belonging_to_other_teachers(): void
    {
        $ownerGuru = Guru::create(['nama' => 'Guru Pemilik', 'nip' => '1001']);
        $viewerGuru = Guru::create(['nama' => 'Guru Lihat', 'nip' => '1002']);

        $owner = User::factory()->create([
            'guru_id' => $ownerGuru->id,
        ]);

        $viewer = User::factory()->create([
            'guru_id' => $viewerGuru->id,
        ]);

        $capaian = CapaianPembelajaran::create([
            'nama_capaian_pembelajaran' => 'Capaian milik guru lain',
            'user_id' => $owner->id,
        ]);

        KomponenNilai::create([
            'capaian_pembelajaran_id' => $capaian->id,
            'nama_komponen' => 'Komponen dari guru lain',
            'bobot' => 20,
        ]);

        $response = $this->actingAs($viewer)->get(route('komponen_nilai.index'));

        $response->assertOk();
        $response->assertDontSee('Komponen dari guru lain');
        $response->assertDontSee('Capaian milik guru lain');
    }
}
