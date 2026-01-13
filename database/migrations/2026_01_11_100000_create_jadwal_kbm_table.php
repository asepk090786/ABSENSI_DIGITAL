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
        Schema::create('jadwal_kbm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('guru_id')->constrained('guru')->onDelete('cascade');
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->onDelete('cascade');
            $table->foreignId('jam_belajar_id')->constrained('jam_belajar')->onDelete('cascade');
            $table->string('hari'); // Senin, Selasa, dst
            $table->integer('jam_ke'); // Jam ke berapa
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->onDelete('set null');
            $table->foreignId('semester_id')->nullable()->constrained('semester')->onDelete('set null');
            $table->timestamps();

            // Unique constraint: satu kelas tidak boleh ada 2 jadwal di waktu yang sama
            $table->unique(['kelas_id', 'hari', 'jam_ke', 'tahun_ajaran_id', 'semester_id'], 'unique_jadwal_kelas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kbm');
    }
};
