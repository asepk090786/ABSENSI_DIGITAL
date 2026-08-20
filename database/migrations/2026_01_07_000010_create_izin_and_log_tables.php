<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jenis_izin', function (Blueprint $table) {
            $table->id();
            $table->string('nama_izin');
            $table->boolean('butuh_bukti')->default(false);
            $table->timestamps();
        });

        Schema::create('izin_siswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa');
            $table->foreignId('kelas_id')->constrained('kelas');
            $table->foreignId('jenis_izin_id')->constrained('jenis_izin');
            $table->date('tanggal');
            $table->time('jam_keluar')->nullable();
            $table->time('jam_kembali')->nullable();
            $table->text('alasan')->nullable();
            $table->string('bukti')->nullable();
            $table->string('status')->default('menunggu');
            $table->foreignId('guru_piket_id')->nullable()->constrained('guru');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran');
            $table->foreignId('semester_id')->constrained('semester');
            $table->timestamps();
        });

        if (! Schema::hasTable('log_keamanan')) {
            Schema::create('log_keamanan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('siswa_id')->constrained('siswa');
                $table->foreignId('izin_siswa_id')->constrained('izin_siswa');
                $table->time('jam_keluar_aktual')->nullable();
                $table->time('jam_kembali_aktual')->nullable();
                $table->foreignId('petugas_keamanan_id')->nullable()->constrained('guru');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('log_keamanan');
        Schema::dropIfExists('izin_siswa');
        Schema::dropIfExists('jenis_izin');
    }
};
