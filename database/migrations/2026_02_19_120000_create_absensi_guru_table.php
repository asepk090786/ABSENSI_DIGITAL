<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('absensi_guru')) {
            return;
        }

        Schema::create('absensi_guru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('pencatat_guru_id')->nullable()->constrained('guru')->nullOnDelete();
            $table->date('tanggal');
            $table->enum('status', ['hadir', 'tidak_hadir', 'izin', 'sakit']);
            $table->string('keterangan')->nullable();
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->nullOnDelete();
            $table->foreignId('semester_id')->nullable()->constrained('semester')->nullOnDelete();
            $table->timestamps();

            $table->unique(['guru_id', 'tanggal']);
            $table->index('tanggal');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_guru');
    }
};
