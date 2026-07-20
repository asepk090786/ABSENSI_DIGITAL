<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('absensi_siswa') && ! Schema::hasColumn('absensi_siswa', 'updated_by')) {
            Schema::table('absensi_siswa', function (Blueprint $table) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('keterangan')->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('absensi_siswa') && Schema::hasColumn('absensi_siswa', 'updated_by')) {
            Schema::table('absensi_siswa', function (Blueprint $table) {
                $table->dropColumn('updated_by');
            });
        }
    }
};
