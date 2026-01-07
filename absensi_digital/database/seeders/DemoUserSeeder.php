<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run()
    {
        $adminRole = DB::table('roles')->where('role_name','Admin')->first();

        if ($adminRole) {
            DB::table('users')->updateOrInsert(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Administrator',
                    'email' => 'admin@example.com',
                    'password' => Hash::make('password'),
                    'role_id' => $adminRole->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
