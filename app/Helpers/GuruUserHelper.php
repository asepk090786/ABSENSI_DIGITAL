<?php
use App\Models\Guru;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
/**
 * Membuat data guru dan user login secara otomatis (logika mirip GuruImport)
 */
function createGuruAndUser($kodeGuru, $namaGuru, $jenisKelamin)
{
    \Log::info('createGuruAndUser called', compact('kodeGuru', 'namaGuru', 'jenisKelamin'));
    $email = 'guru' . $kodeGuru . '@simadis.sch';
    $username = 'guru' . $kodeGuru;
    $password = 'guru' . $kodeGuru;

    // Cari atau buat data guru
    $guru = Guru::where('kode_guru', $kodeGuru)->first();
    if (!$guru) {
        $guru = Guru::create([
            'nama' => $namaGuru,
            'kode_guru' => $kodeGuru,
            'email' => $email,
            'jenis_kelamin' => $jenisKelamin,
        ]);
        \Log::info('Guru created', ['guru_id' => $guru->id]);
    } else {
        \Log::info('Guru exists', ['guru_id' => $guru->id]);
    }

    // Cari role guru
    $roleGuru = Role::where('role_name', 'Guru Mapel')->first();
    if (!$roleGuru) {
        \Log::error('Role Guru tidak ditemukan');
        return;
    }

    // Cek jika user sudah ada untuk guru ini
    $userExist = User::where('guru_id', $guru->id)->first();
    if (!$userExist) {
        User::create([
            'name' => $namaGuru,
            'username' => $username,
            'password' => Hash::make($password),
            'email' => $email,
            'jenis_kelamin' => $jenisKelamin,
            'role_id' => $roleGuru->id,
            'guru_id' => $guru->id,
            'is_active' => true,
        ]);
        \Log::info('User created', ['guru_id' => $guru->id, 'username' => $username]);
    } else {
        \Log::info('User already exists', ['guru_id' => $guru->id, 'username' => $username]);
    }
}
