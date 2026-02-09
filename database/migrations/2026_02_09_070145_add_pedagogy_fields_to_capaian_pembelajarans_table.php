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
        Schema::table('capaian_pembelajarans', function (Blueprint $table) {
            $table->longText('tujuan_pembelajaran')->nullable()->after('deskripsi');
            $table->longText('alur_tujuan_pembelajaran')->nullable()->after('tujuan_pembelajaran');
            $table->longText('indikator_kriteria')->nullable()->after('alur_tujuan_pembelajaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capaian_pembelajarans', function (Blueprint $table) {
            $table->dropColumn(['tujuan_pembelajaran', 'alur_tujuan_pembelajaran', 'indikator_kriteria']);
        });
    }
};
