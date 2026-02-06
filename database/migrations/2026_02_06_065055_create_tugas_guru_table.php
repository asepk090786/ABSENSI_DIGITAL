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
        Schema::create('tugas_guru', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guru_id');
            $table->unsignedBigInteger('mata_pelajaran_id');
            $table->string('tingkat_kelas', 10); // X, XI, XII
            $table->unsignedBigInteger('kelas_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('keterangan')->nullable();
            $table->timestamps();

            // Indexes for better query performance and relationships
            $table->index('guru_id');
            $table->index('mata_pelajaran_id');
            $table->index('kelas_id');
            $table->index(['guru_id', 'is_active']);
            $table->index(['mata_pelajaran_id', 'tingkat_kelas']);
            
            // Unique constraint to prevent duplicate assignments
            $table->unique(['guru_id', 'mata_pelajaran_id', 'tingkat_kelas', 'kelas_id'], 'tugas_guru_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tugas_guru');
    }
};
