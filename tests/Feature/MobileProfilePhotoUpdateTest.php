<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MobileProfilePhotoUpdateTest extends TestCase
{
    public function test_student_can_update_profile_photo_via_mobile_api(): void
    {
        $role = Role::firstOrCreate(['role_name' => 'Siswa']);

        $user = User::factory()->create([
            'name' => 'Siswa Lama',
            'username' => 'siswa01',
            'email' => 'siswa@example.com',
            'role_id' => $role->id,
            'is_active' => true,
            'foto' => null,
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;
        Storage::fake('public');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/mobile/profile', [
                'name' => 'Siswa Baru',
                'email' => 'siswa-baru@example.com',
                'foto' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
            ]);

        $response->assertOk();
        $response->assertJsonPath('message', 'Profil berhasil diperbarui.');

        $user->refresh();

        $this->assertNotNull($user->foto);
        $this->assertTrue(Storage::disk('public')->exists($user->foto));
    }
}
