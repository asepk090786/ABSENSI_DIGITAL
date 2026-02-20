<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            if (!Schema::hasColumn('kelas', 'guru_bk_id')) {
                $table->foreignId('guru_bk_id')->nullable()->after('wali_kelas_id')->constrained('guru')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            if (Schema::hasColumn('kelas', 'guru_bk_id')) {
                $table->dropConstrainedForeignId('guru_bk_id');
            }
        });
    }
};
