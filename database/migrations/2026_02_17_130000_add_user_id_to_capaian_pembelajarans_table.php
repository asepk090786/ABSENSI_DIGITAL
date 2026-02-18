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
        if (!Schema::hasColumn('capaian_pembelajarans', 'user_id')) {
            Schema::table('capaian_pembelajarans', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('tahun_ajaran_id')->constrained('users')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('capaian_pembelajarans', 'user_id')) {
            Schema::table('capaian_pembelajarans', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};
