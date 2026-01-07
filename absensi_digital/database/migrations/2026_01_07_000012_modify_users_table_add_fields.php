<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'role_id')) {
                $table->foreignId('role_id')->nullable()->constrained('roles');
            }
            if (! Schema::hasColumn('users', 'guru_id')) {
                $table->foreignId('guru_id')->nullable()->constrained('guru');
            }
            if (! Schema::hasColumn('users', 'siswa_id')) {
                $table->foreignId('siswa_id')->nullable()->constrained('siswa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
            $table->dropForeign(['guru_id']);
            $table->dropColumn('guru_id');
            $table->dropForeign(['siswa_id']);
            $table->dropColumn('siswa_id');
        });
    }
};
