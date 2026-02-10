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
        Schema::create('capaian_pembelajarans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_capaian_pembelajaran')->unique();
            $table->longText('deskripsi')->nullable();
            $table->string('fase')->nullable()->comment('Fase pembelajaran (A, B, C, D, E, F)');
            $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capaian_pembelajarans');
    }
};
