<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('absensi_siswa_history')) {
            Schema::create('absensi_siswa_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('absensi_siswa_id')->nullable()->index();
                $table->unsignedBigInteger('absensi_kelas_id')->nullable()->index();
                $table->unsignedBigInteger('siswa_id')->nullable()->index();
                $table->string('previous_status')->nullable();
                $table->string('new_status')->nullable();
                $table->text('previous_keterangan')->nullable();
                $table->text('new_keterangan')->nullable();
                $table->unsignedBigInteger('changed_by')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_siswa_history');
    }
};
