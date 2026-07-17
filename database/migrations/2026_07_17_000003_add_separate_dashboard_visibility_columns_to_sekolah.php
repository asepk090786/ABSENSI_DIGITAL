<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            if (! Schema::hasColumn('sekolah', 'tampilkan_jadwal_guru')) {
                $table->boolean('tampilkan_jadwal_guru')->default(true)->after('tampilkan_jadwal');
            }
            if (! Schema::hasColumn('sekolah', 'tampilkan_jadwal_siswa')) {
                $table->boolean('tampilkan_jadwal_siswa')->default(true)->after('tampilkan_jadwal_guru');
            }
            if (! Schema::hasColumn('sekolah', 'tampilkan_nama_wali_kelas_guru')) {
                $table->boolean('tampilkan_nama_wali_kelas_guru')->default(true)->after('tampilkan_nama_wali_kelas');
            }
            if (! Schema::hasColumn('sekolah', 'tampilkan_nama_wali_kelas_siswa')) {
                $table->boolean('tampilkan_nama_wali_kelas_siswa')->default(true)->after('tampilkan_nama_wali_kelas_guru');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            if (Schema::hasColumn('sekolah', 'tampilkan_jadwal_guru')) {
                $table->dropColumn('tampilkan_jadwal_guru');
            }
            if (Schema::hasColumn('sekolah', 'tampilkan_jadwal_siswa')) {
                $table->dropColumn('tampilkan_jadwal_siswa');
            }
            if (Schema::hasColumn('sekolah', 'tampilkan_nama_wali_kelas_guru')) {
                $table->dropColumn('tampilkan_nama_wali_kelas_guru');
            }
            if (Schema::hasColumn('sekolah', 'tampilkan_nama_wali_kelas_siswa')) {
                $table->dropColumn('tampilkan_nama_wali_kelas_siswa');
            }
        });
    }
};
