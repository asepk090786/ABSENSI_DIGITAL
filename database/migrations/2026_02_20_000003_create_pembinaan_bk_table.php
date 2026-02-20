<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pembinaan_bk')) {
            Schema::create('pembinaan_bk', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('kelas_id');
                $table->unsignedBigInteger('guru_bk_id');
                $table->unsignedBigInteger('siswa_id');
                $table->string('wali_kelas_nama')->nullable();
                $table->unsignedInteger('hadir')->default(0);
                $table->unsignedInteger('sakit')->default(0);
                $table->unsignedInteger('izin')->default(0);
                $table->unsignedInteger('alpa')->default(0);
                $table->unsignedInteger('terlambat')->default(0);
                $table->text('deskripsi_permasalahan');
                $table->text('penanganan');
                $table->text('tindak_lanjut')->nullable();
                $table->text('bukti_dukung_absensi')->nullable();
                $table->text('laporan_guru')->nullable();
                $table->text('laporan_wali_kelas')->nullable();
                $table->json('bukti_dukung_files')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pembinaan_bk');
    }
};
