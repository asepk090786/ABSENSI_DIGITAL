<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('laporan_siswa_guru')) {
            Schema::create('laporan_siswa_guru', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('absensi_kelas_id')->nullable();
                $table->unsignedBigInteger('kelas_id');
                $table->unsignedBigInteger('siswa_id');
                $table->unsignedBigInteger('guru_pelapor_id');
                $table->unsignedBigInteger('wali_kelas_id')->nullable();
                $table->unsignedBigInteger('guru_bk_id')->nullable();
                $table->text('deskripsi_permasalahan');
                $table->timestamps();

                $table->index(['kelas_id', 'siswa_id']);
                $table->index('guru_pelapor_id');
                $table->index('wali_kelas_id');
                $table->index('guru_bk_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('laporan_siswa_guru');
    }
};
