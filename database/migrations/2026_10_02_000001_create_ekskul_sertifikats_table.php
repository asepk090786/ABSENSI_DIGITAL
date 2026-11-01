<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ekskul_sertifikats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikuler')->cascadeOnDelete();
            $table->foreignId('siswa_id')->constrained('siswa')->cascadeOnDelete();
            $table->string('file_path')->nullable();
            $table->string('barcode')->unique();
            $table->string('nomor_sertifikat')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            $table->unique(['ekstrakurikuler_id', 'siswa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekskul_sertifikats');
    }
};