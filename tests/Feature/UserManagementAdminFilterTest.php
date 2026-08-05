<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Tests\TestCase;

class UserManagementAdminFilterTest extends TestCase
{
    public function test_admin_filter_shows_only_admin_accounts(): void
    {
        $adminRole = Role::create(['role_name' => 'Admin']);
        $guruRole = Role::create(['role_name' => 'Guru']);

        $adminUser = User::create([
            'name' => 'Admin Test',
            'username' => 'admin-test-' . uniqid(),
            'password' => bcrypt('secret123'),
            'email' => uniqid('admin').'@example.test',
            'role_id' => $adminRole->id,
        ]);

        $guruUser = User::create([
            'name' => 'Guru Test',
            'username' => 'guru-test-' . uniqid(),
            'password' => bcrypt('secret123'),
            'email' => uniqid('guru').'@example.test',
            'role_id' => $guruRole->id,
        ]);

        $this->actingAs($adminUser);

        $response = $this->get('/users?role=Admin');

        $response->assertStatus(200);
        $response->assertSee($adminUser->name);
        $response->assertDontSee($guruUser->name);
    }

    public function test_admin_route_filters_admin_accounts(): void
    {
        $adminRole = Role::create(['role_name' => 'Admin']);
        $guruRole = Role::create(['role_name' => 'Guru']);

        $adminUser = User::create([
            'name' => 'Admin Route',
            'username' => 'admin-route-' . uniqid(),
            'password' => bcrypt('secret123'),
            'email' => uniqid('admin-route').'@example.test',
            'role_id' => $adminRole->id,
        ]);

        $guruUser = User::create([
            'name' => 'Guru Route',
            'username' => 'guru-route-' . uniqid(),
            'password' => bcrypt('secret123'),
            'email' => uniqid('guru-route').'@example.test',
            'role_id' => $guruRole->id,
        ]);

        $this->actingAs($adminUser);

        $response = $this->get('/users/admin');

        $response->assertStatus(200);
        $response->assertSee($adminUser->name);
        $response->assertDontSee($guruUser->name);
    }

    public function test_admin_create_page_preselects_admin_role(): void
    {
        $adminRole = Role::create(['role_name' => 'Admin']);
        $adminUser = User::create([
            'name' => 'Admin Creator',
            'username' => 'admin-creator-' . uniqid(),
            'password' => bcrypt('secret123'),
            'email' => uniqid('admin-creator').'@example.test',
            'role_id' => $adminRole->id,
        ]);

        $this->actingAs($adminUser);

        $response = $this->get('/users/create?role=Admin');

        $response->assertStatus(200);
        $response->assertSee('Tambah Akun Admin');
        $response->assertSee('value="' . $adminRole->id . '" selected', false);
    }
}
