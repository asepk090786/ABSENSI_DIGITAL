<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
        
        Schema::table('jadwal_kbm', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique('unique_jadwal_kelas');
        });

        Schema::table('jadwal_kbm', function (Blueprint $table) {
            // Add new unique constraint that allows multiple guru-mapel for same kelas-hari-jam
            // Instead, ensure unique guru-mapel combination per time slot
            $table->unique(['guru_id', 'mata_pelajaran_id', 'hari', 'jam_ke', 'tahun_ajaran_id', 'semester_id'], 'unique_jadwal_guru_mapel');
        });
        
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }
        
        Schema::table('jadwal_kbm', function (Blueprint $table) {
            $table->dropUnique('unique_jadwal_guru_mapel');
        });

        Schema::table('jadwal_kbm', function (Blueprint $table) {
            $table->unique(['kelas_id', 'hari', 'jam_ke', 'tahun_ajaran_id', 'semester_id'], 'unique_jadwal_kelas');
        });
        
        if (DB::getDriverName() === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }
};
