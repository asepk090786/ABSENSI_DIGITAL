<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('izin_kegiatan')) {
            Schema::create('izin_kegiatan', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kelas_id')->constrained('kelas');
                $table->foreignId('siswa_id')->constrained('siswa');
                $table->string('jenis_kegiatan');
                $table->text('keterangan_kegiatan')->nullable();
                $table->date('tanggal_mulai');
                $table->date('tanggal_selesai');
                $table->boolean('is_active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('izin_kegiatan');
    }
};
