<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('nilai_harian', 'kategori_asesmen')) {
            Schema::table('nilai_harian', function (Blueprint $table) {
                $table->string('kategori_asesmen', 20)->nullable()->after('jenis_penilaian');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('nilai_harian', 'kategori_asesmen')) {
            Schema::table('nilai_harian', function (Blueprint $table) {
                $table->dropColumn('kategori_asesmen');
            });
        }
    }
};