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
        Schema::table('rencana_pembelajarans', function (Blueprint $table) {
            $table->foreignId('capaian_pembelajaran_id')->nullable()->after('mata_pelajaran_id')->constrained('capaian_pembelajarans')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rencana_pembelajarans', function (Blueprint $table) {
            $table->dropColumn('capaian_pembelajaran_id');
        });
    }
};
