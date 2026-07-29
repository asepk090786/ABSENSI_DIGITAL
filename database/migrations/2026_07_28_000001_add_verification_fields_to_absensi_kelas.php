<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('absensi_kelas', function (Blueprint $table) {
            $table->boolean('verifikasi_aktif')->default(false)->after('semester_id');
            $table->string('kode_verifikasi')->nullable()->after('verifikasi_aktif');
            $table->dateTime('kode_verifikasi_expires_at')->nullable()->after('kode_verifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_kelas', function (Blueprint $table) {
            $table->dropColumn(['verifikasi_aktif', 'kode_verifikasi', 'kode_verifikasi_expires_at']);
        });
    }
};
