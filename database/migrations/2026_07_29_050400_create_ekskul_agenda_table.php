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
        if (Schema::hasTable('ekskul_agenda')) return;
        Schema::create('ekskul_agenda', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikuler')->cascadeOnDelete();
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->date('tanggal');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('lokasi', 200)->nullable();
            $table->enum('jenis', ['rutin', 'khusus'])->default('rutin');
            $table->text('materi')->nullable();
            $table->enum('status', ['direncanakan', 'terlaksana', 'dibatalkan'])->default('direncanakan');
            $table->foreignId('dibuat_oleh')->nullable()->constrained('guru')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekskul_agenda');
    }
};
