<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mata_pelajaran', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mapel')->nullable();
            $table->string('nama_mapel');
            $table->timestamps();
        });

        Schema::create('komponen_nilai', function (Blueprint $table) {
            $table->id();
            $table->string('nama_komponen');
            $table->decimal('bobot',5,2)->default(0);
            $table->timestamps();
        });

        Schema::create('nilai_harian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa');
            $table->foreignId('guru_id')->nullable()->constrained('guru');
            $table->foreignId('kelas_id')->nullable()->constrained('kelas');
            $table->foreignId('mapel_id')->nullable()->constrained('mata_pelajaran');
            $table->foreignId('komponen_id')->nullable()->constrained('komponen_nilai');
            $table->date('tanggal');
            $table->decimal('nilai',5,2)->nullable();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran');
            $table->foreignId('semester_id')->constrained('semester');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nilai_harian');
        Schema::dropIfExists('komponen_nilai');
        Schema::dropIfExists('mata_pelajaran');
    }
};
