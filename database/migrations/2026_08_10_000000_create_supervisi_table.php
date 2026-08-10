<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supervisi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->onDelete('cascade');
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('jadwal_kbm_id')->nullable()->constrained('jadwal_kbm')->onDelete('set null');
            $table->date('tanggal');
            $table->integer('jam_ke');
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->unique(['guru_id', 'tanggal', 'jam_ke', 'kelas_id', 'mata_pelajaran_id'], 'unique_supervisi_entry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supervisi');
    }
};
