<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            if (! Schema::hasColumn('sekolah', 'tampilkan_nama_wali_kelas')) {
                $table->boolean('tampilkan_nama_wali_kelas')->default(true)->after('tampilkan_jadwal');
            }
            if (! Schema::hasColumn('sekolah', 'wali_kelas_hidden_message')) {
                $table->text('wali_kelas_hidden_message')->nullable()->after('jadwal_maintenance_message');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            if (Schema::hasColumn('sekolah', 'wali_kelas_hidden_message')) {
                $table->dropColumn('wali_kelas_hidden_message');
            }
            if (Schema::hasColumn('sekolah', 'tampilkan_nama_wali_kelas')) {
                $table->dropColumn('tampilkan_nama_wali_kelas');
            }
        });
    }
};