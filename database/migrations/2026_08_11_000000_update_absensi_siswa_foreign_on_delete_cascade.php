<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('absensi_siswa')) {
            Schema::table('absensi_siswa', function (Blueprint $table) {
                // Drop existing foreign key (if present) and recreate with cascade on delete
                try {
                    $table->dropForeign(['siswa_id']);
                } catch (\Throwable $e) {
                    // ignore if it doesn't exist
                }

                $table->foreign('siswa_id')
                    ->references('id')
                    ->on('siswa')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('absensi_siswa')) {
            Schema::table('absensi_siswa', function (Blueprint $table) {
                try {
                    $table->dropForeign(['siswa_id']);
                } catch (\Throwable $e) {
                }

                // Recreate without cascade (restrict/no action)
                $table->foreign('siswa_id')
                    ->references('id')
                    ->on('siswa');
            });
        }
    }
};
