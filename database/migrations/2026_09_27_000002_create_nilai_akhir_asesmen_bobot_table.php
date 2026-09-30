<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nilai_akhir_asesmen_bobot', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('mapel_id')->constrained('mata_pelajaran')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->foreignId('semester_id')->constrained('semester')->cascadeOnDelete();
            $table->decimal('bobot_formatif', 5, 2)->default(50);
            $table->decimal('bobot_sumatif', 5, 2)->default(50);
            $table->timestamps();
            $table->unique(
                ['kelas_id', 'mapel_id', 'tahun_ajaran_id', 'semester_id'],
                'nilai_akhir_asesmen_bobot_scope_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_akhir_asesmen_bobot');
    }
};