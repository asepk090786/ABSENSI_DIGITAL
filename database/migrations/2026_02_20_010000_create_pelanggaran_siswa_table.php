<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pelanggaran_siswa')) {
            return;
        }

        Schema::create('pelanggaran_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->foreignId('guru_piket_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->foreignId('absensi_kelas_id')->nullable()->constrained('absensi_kelas')->nullOnDelete();
            $table->date('tanggal');
            $table->string('status_absensi', 30)->nullable();
            $table->text('deskripsi_pelanggaran')->nullable();
            $table->time('jam_ke_1_mulai')->nullable();
            $table->dateTime('waktu_input_pelanggaran')->nullable();
            $table->unsignedInteger('terlambat_menit')->default(0);
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semester')->nullOnDelete();
            $table->timestamps();

            $table->unique(['kelas_id', 'siswa_id', 'tanggal']);
            $table->index(['kelas_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggaran_siswa');
    }
};
