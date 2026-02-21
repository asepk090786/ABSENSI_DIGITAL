<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jenis_pelanggaran')) {
            return;
        }

        Schema::create('jenis_pelanggaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 30)->unique();
            $table->string('nama', 150);
            $table->unsignedInteger('poin_default')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('jenis_pelanggaran')->insert([
            [
                'kode' => 'Terlambat',
                'nama' => 'Terlambat Masuk Sekolah',
                'poin_default' => 5,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'Seragam',
                'nama' => 'Seragam Tidak Lengkap',
                'poin_default' => 10,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'Atribut',
                'nama' => 'Atribut Tidak Sesuai',
                'poin_default' => 5,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'kode' => 'Disiplin',
                'nama' => 'Pelanggaran Disiplin Kelas',
                'poin_default' => 15,
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_pelanggaran');
    }
};
