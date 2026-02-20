<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('layanan_bk')) {
            Schema::create('layanan_bk', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
                $table->foreignId('guru_bk_id')->constrained('guru')->cascadeOnDelete();
                $table->foreignId('siswa_id')->nullable()->constrained('siswa')->nullOnDelete();
                $table->date('tanggal');
                $table->string('jenis_layanan', 100);
                $table->text('deskripsi_layanan');
                $table->text('hasil_layanan')->nullable();
                $table->text('rencana_tindak_lanjut')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('layanan_bk')) {
            Schema::dropIfExists('layanan_bk');
        }
    }
};
