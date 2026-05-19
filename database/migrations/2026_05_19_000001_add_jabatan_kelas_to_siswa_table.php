<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (! Schema::hasColumn('siswa', 'jabatan_kelas')) {
                $table->string('jabatan_kelas')->nullable()->after('kelas_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (Schema::hasColumn('siswa', 'jabatan_kelas')) {
                $table->dropColumn('jabatan_kelas');
            }
        });
    }
};
