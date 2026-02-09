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
        Schema::table('komponen_nilai', function (Blueprint $table) {
            $table->longText('capaian_pembelajaran')->nullable()->after('bobot');
            $table->longText('tujuan_pembelajaran')->nullable()->after('capaian_pembelajaran');
            $table->longText('alur_tujuan_pembelajaran')->nullable()->after('tujuan_pembelajaran');
            $table->longText('indikator_kriteria')->nullable()->after('alur_tujuan_pembelajaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('komponen_nilai', function (Blueprint $table) {
            $table->dropColumn(['capaian_pembelajaran', 'tujuan_pembelajaran', 'alur_tujuan_pembelajaran', 'indikator_kriteria']);
        });
    }
};
