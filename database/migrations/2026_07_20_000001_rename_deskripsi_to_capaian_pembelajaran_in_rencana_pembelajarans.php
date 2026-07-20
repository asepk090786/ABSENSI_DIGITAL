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
        if (Schema::hasTable('rencana_pembelajarans') && Schema::hasColumn('rencana_pembelajarans', 'deskripsi')) {
            Schema::table('rencana_pembelajarans', function (Blueprint $table) {
                // Rename column to capaian_pembelajaran
                $table->renameColumn('deskripsi', 'capaian_pembelajaran');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rencana_pembelajarans') && Schema::hasColumn('rencana_pembelajarans', 'capaian_pembelajaran')) {
            Schema::table('rencana_pembelajarans', function (Blueprint $table) {
                $table->renameColumn('capaian_pembelajaran', 'deskripsi');
            });
        }
    }
};
