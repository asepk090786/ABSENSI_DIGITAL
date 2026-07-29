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
        if (Schema::hasTable('ekskul_bukti_kegiatan')) return;
        Schema::create('ekskul_bukti_kegiatan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikuler')->cascadeOnDelete();
            $table->foreignId('ekskul_agenda_id')->nullable()->constrained('ekskul_agenda')->nullOnDelete();
            $table->string('judul', 200);
            $table->text('deskripsi')->nullable();
            $table->string('file_path', 255);
            $table->string('file_type', 50)->nullable();
            $table->foreignId('diupload_oleh')->nullable()->constrained('guru')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekskul_bukti_kegiatan');
    }
};
