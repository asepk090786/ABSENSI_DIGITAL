<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('absensi_kelas', function (Blueprint $table) {
            $table->boolean('verifikasi_manual_aktif')->default(false)->after('kode_verifikasi_expires_at');
            $table->dateTime('verifikasi_manual_expires_at')->nullable()->after('verifikasi_manual_aktif');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_kelas', function (Blueprint $table) {
            $table->dropColumn(['verifikasi_manual_aktif', 'verifikasi_manual_expires_at']);
        });
    }
};
