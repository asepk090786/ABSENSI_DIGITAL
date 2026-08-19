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
        Schema::create('observation_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisi_id')->constrained('supervisi')->onDelete('cascade');
            $table->enum('jenis', ['foto', 'video', 'dokumen'])->default('foto');
            $table->string('file_path'); // Path file di storage
            $table->string('nama_file');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observation_evidences');
    }
};
