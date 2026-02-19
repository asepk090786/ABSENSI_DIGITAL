<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('absensi_guru')) {
            return;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE absensi_guru MODIFY status ENUM('hadir','tidak_hadir','izin','sakit') NOT NULL");
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('absensi_guru')) {
            return;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE absensi_guru MODIFY status ENUM('hadir','tidak_hadir') NOT NULL");
        }
    }
};
