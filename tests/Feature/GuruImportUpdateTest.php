<?php

namespace Tests\Feature;

use App\Imports\GuruImport;
use App\Models\Guru;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GuruImportUpdateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['role_name' => 'Guru']);
    }

    public function test_it_updates_existing_guru_when_matching_identifier_is_provided_in_update_mode(): void
    {
        $guru = Guru::create([
            'nama' => 'Budi Santoso',
            'nip' => '198501012010011001',
            'kode_guru' => 'GURU001',
            'email' => 'budi@example.com',
            'jenis_kelamin' => 'L',
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'username' => 'budi',
            'password' => 'secret',
            'email' => 'budi@example.com',
            'jenis_kelamin' => 'L',
            'role_id' => 1,
            'guru_id' => $guru->id,
            'is_active' => true,
        ]);

        $import = new GuruImport('update');
        $import->collection(collect([[
            'no_id' => 1,
            'id_guru' => $guru->id,
            'nama' => 'Budi Updated',
            'kode_guru' => 'GURU999',
            'nip' => '198501012010011002',
            'pangkat_golongan' => 'Pembina',
            'email' => 'budi.updated@example.com',
            'telepon' => '081234567890',
            'tanggal_lahir' => '1990-02-02',
            'jenis_kelamin' => 'L',
            'alamat' => 'Jl. Baru',
            'username' => 'budi.updated',
            'password' => 'newpassword123',
        ]]));

        $guru->refresh();

        $this->assertSame('Budi Updated', $guru->nama);
        $this->assertSame('GURU999', $guru->kode_guru);
        $this->assertSame('198501012010011002', $guru->nip);
        $this->assertSame('Pembina', $guru->pangkat_golongan);
        $this->assertSame('budi.updated@example.com', $guru->email);

        $user = $guru->user()->first();
        $this->assertNotNull($user);
        $this->assertSame('budi.updated', $user->username);
        $this->assertSame('budi.updated@example.com', $user->email);

        $this->assertSame(1, $import->getUpdatedCount());
        $this->assertSame(0, $import->getCreatedCount());
        $this->assertSame([], $import->getErrors());
    }
}
