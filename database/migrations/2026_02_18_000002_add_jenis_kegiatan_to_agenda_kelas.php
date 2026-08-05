<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agenda_kelas', function (Blueprint $table) {
            $table->string('jenis_kegiatan')->default('kbm')->after('guru_id');
            $table->string('nama_kegiatan')->nullable()->after('kegiatan');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE agenda_kelas MODIFY kelas_id BIGINT UNSIGNED NULL');
            DB::statement('ALTER TABLE agenda_kelas MODIFY jam_belajar_id BIGINT UNSIGNED NULL');
        }
    }

    public function down(): void
    {
        // Ensure default values for nullable columns before removing them
        DB::statement("UPDATE agenda_kelas SET jenis_kegiatan = 'kbm' WHERE jenis_kegiatan IS NULL");
        DB::statement("UPDATE agenda_kelas SET kelas_id = (SELECT id FROM kelas ORDER BY id LIMIT 1) WHERE kelas_id IS NULL");
        DB::statement("UPDATE agenda_kelas SET jam_belajar_id = (SELECT id FROM jam_belajar ORDER BY id LIMIT 1) WHERE jam_belajar_id IS NULL");

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE agenda_kelas MODIFY kelas_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE agenda_kelas MODIFY jam_belajar_id BIGINT UNSIGNED NOT NULL');
        }

        Schema::table('agenda_kelas', function (Blueprint $table) {
            $table->dropColumn('nama_kegiatan');
            $table->dropColumn('jenis_kegiatan');
        });
    }
};
