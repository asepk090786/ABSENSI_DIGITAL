<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('absensi_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('guru_id')->constrained('guru');
            $table->foreignId('jam_belajar_id')->constrained('jam_belajar');
            $table->date('tanggal');
            $table->string('status_kelas')->nullable();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran');
            $table->foreignId('semester_id')->constrained('semester');
            $table->timestamps();
        });

        Schema::create('absensi_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absensi_kelas_id')->constrained('absensi_kelas');
            $table->foreignId('siswa_id')->constrained('siswa');
            $table->string('status');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_siswa');
        Schema::dropIfExists('absensi_kelas');
    }
};
