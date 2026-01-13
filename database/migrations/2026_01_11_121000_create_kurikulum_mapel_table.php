<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kurikulum_mapel', function (Blueprint $table) {
            $table->id();
            $table->string('tingkat', 10);
            $table->string('jurusan', 20)->nullable();
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['tingkat', 'jurusan', 'mata_pelajaran_id'], 'kurikulum_mapel_unique');
            $table->index(['tingkat', 'jurusan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kurikulum_mapel');
    }
};
