<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'Admin',
            'Kepala Sekolah',
            'Pengawas Pembina',
            'Wakil Kepala Sekolah',
            'Guru Kelas',
            'Wali Kelas',
            'Guru Mapel',
            'Guru BK',
            'Guru Piket',
            'Pembina',
            'Siswa',
            'Petugas Keamanan',
            'Tenaga Pendidikan',
        ];

        foreach ($roles as $r) {
            DB::table('roles')->updateOrInsert(['role_name' => $r]);
        }
    }
}
