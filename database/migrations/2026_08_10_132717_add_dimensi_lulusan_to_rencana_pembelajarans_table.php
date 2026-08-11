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
        if (!Schema::hasTable('rencana_pembelajarans')) {
            return;
        }

        if (!Schema::hasColumn('rencana_pembelajarans', 'dimensi_lulusan')) {
            Schema::table('rencana_pembelajarans', function (Blueprint $table) {
                $table->text('dimensi_lulusan')->nullable()->after('alokasi_waktu');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rencana_pembelajarans', function (Blueprint $table) {
            $table->dropColumn('dimensi_lulusan');
        });
    }
};
