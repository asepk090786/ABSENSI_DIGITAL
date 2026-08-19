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
        Schema::create('action_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_conference_id')->constrained('post_conferences')->onDelete('cascade');
            $table->text('tujuan');
            $table->text('aktivitas');
            $table->text('rekomendasi')->nullable();
            $table->foreignId('penanggung_jawab_id')->nullable()->constrained('guru')->onDelete('set null');
            $table->date('target_selesai');
            $table->enum('status', ['Belum Mulai', 'Berjalan', 'Selesai', 'Ditunda', 'Dibatalkan'])->default('Belum Mulai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_plans');
    }
};
