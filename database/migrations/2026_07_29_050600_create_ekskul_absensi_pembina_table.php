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
        if (Schema::hasTable('ekskul_absensi_pembina')) return;
        Schema::create('ekskul_absensi_pembina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikuler')->cascadeOnDelete();
            $table->foreignId('ekskul_agenda_id')->nullable()->constrained('ekskul_agenda')->nullOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_checkin');
            $table->string('foto', 255)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekskul_absensi_pembina');
    }
};
