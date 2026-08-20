<?php

namespace Tests\Feature;

use App\Models\CapaianPembelajaran;
use App\Models\Guru;
use App\Models\KomponenNilai;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KomponenNilaiVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guru_user_only_sees_components_created_by_their_account(): void
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
            'guru_id' => $ownerGuru->id,
            'nama_komponen' => 'Komponen dari guru lain',
            'bobot' => 20,
        ]);
        KomponenNilai::create([
            'guru_id' => $viewerGuru->id,
            'nama_komponen' => 'Komponen milik guru yang login',
            'bobot' => 30,
        ]);

        $response = $this->actingAs($viewer)->get(route('komponen_nilai.index'));

        $response->assertOk();
        $response->assertDontSee('Komponen dari guru lain');
        $response->assertDontSee('Capaian milik guru lain');
        $response->assertDontSee('Filter Guru');
    }

    public function test_guru_filter_only_shows_components_owned_by_selected_teacher(): void
    {
        $role = Role::create(['role_name' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $selectedGuru = Guru::create(['nama' => 'Guru Terpilih', 'nip' => '1003']);
        $otherGuru = Guru::create(['nama' => 'Guru Lain', 'nip' => '1004']);

        KomponenNilai::create([
            'guru_id' => $selectedGuru->id,
            'nama_komponen' => 'Komponen Guru Terpilih',
        ]);
        KomponenNilai::create([
            'guru_id' => $otherGuru->id,
            'nama_komponen' => 'Komponen Guru Lain',
        ]);

        $response = $this->actingAs($admin)->get(route('komponen_nilai.index', [
            'guru_id' => $selectedGuru->id,
        ]));

        $response->assertOk();
        $response->assertDontSee('Komponen Guru Lain');
    }

    public function test_empty_guru_filter_does_not_show_all_components(): void
    {
        $role = Role::create(['role_name' => 'Admin']);
        $admin = User::factory()->create(['role_id' => $role->id]);
        $guru = Guru::create(['nama' => 'Guru Uji', 'nip' => '1005']);

        KomponenNilai::create([
            'guru_id' => $guru->id,
            'nama_komponen' => 'Komponen Yang Tidak Ditampilkan',
        ]);

        $response = $this->actingAs($admin)->get(route('komponen_nilai.index', [
            'guru_id' => '',
        ]));

        $response->assertOk();
        $response->assertDontSee('Komponen Yang Tidak Ditampilkan');
    }
}
