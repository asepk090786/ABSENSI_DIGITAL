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
        Schema::create('materi_pembelajarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->foreignId('rencana_pembelajaran_id')->constrained('rencana_pembelajarans')->cascadeOnDelete();
            $table->string('nama_kegiatan');
            $table->longText('materi_pembelajaran');
            $table->string('link_pembelajaran_daring')->nullable();
            $table->string('bukti_pembelajaran')->nullable(); // path to image/photo/screenshot
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi_pembelajarans');
    }
};
