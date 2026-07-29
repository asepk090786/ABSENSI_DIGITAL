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
        if (Schema::hasTable('ekskul_absensi')) return;
        Schema::create('ekskul_absensi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikuler')->cascadeOnDelete();
            $table->foreignId('ekskul_agenda_id')->nullable()->constrained('ekskul_agenda')->nullOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alpha', 'tanpa_keterangan'])->default('hadir');
            $table->text('keterangan')->nullable();
            $table->foreignId('dibukukan_oleh')->nullable()->constrained('guru')->nullOnDelete();
            $table->timestamps();
            $table->index(['ekstrakurikuler_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekskul_absensi');
    }
};
