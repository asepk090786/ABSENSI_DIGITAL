<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tindak_lanjut_bk')) {
            return;
        }

        Schema::create('tindak_lanjut_bk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('guru_bk_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->string('nama_siswa', 150);
            $table->string('nama_kelas', 100);
            $table->string('nis', 50)->nullable();
            $table->string('nisn', 50)->nullable();
            $table->string('nama_wali_kelas', 150)->nullable();
            $table->string('nama_guru_bk', 150)->nullable();
            $table->string('waktu', 255);
            $table->string('nama_penyusun', 150)->nullable();
            $table->json('rencana_items');
            $table->timestamps();

            $table->index(['kelas_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tindak_lanjut_bk');
    }
};
