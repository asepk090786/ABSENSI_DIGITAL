<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('absensi_kelas', function (Blueprint $table) {
            $table->time('verifikasi_manual_valid_from')->nullable()->after('verifikasi_manual_expires_at');
            $table->time('verifikasi_manual_valid_to')->nullable()->after('verifikasi_manual_valid_from');
        });
    }

    public function down(): void
    {
        Schema::table('absensi_kelas', function (Blueprint $table) {
            $table->dropColumn(['verifikasi_manual_valid_from', 'verifikasi_manual_valid_to']);
        });
    }
};
