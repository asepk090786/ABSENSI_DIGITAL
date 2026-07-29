<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('ekskul_anggota')) return;
        Schema::create('ekskul_anggota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikuler')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->enum('status_pendaftaran', ['pending', 'diterima', 'ditolak'])->default('pending');
            $table->date('tanggal_daftar');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->unique(['ekstrakurikuler_id', 'siswa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekskul_anggota');
    }
};
