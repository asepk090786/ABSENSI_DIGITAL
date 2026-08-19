<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisiModularMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_supervisi_dashboard_and_master_routes_are_available_for_admin(): void
    {
        $role = Role::create(['role_name' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->actingAs($user)
            ->get(route('supervisi.dashboard'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('supervisi.prasupervisi'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('supervisi.instrumen.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('supervisi.indikator.index'))
            ->assertOk();
    }
}
