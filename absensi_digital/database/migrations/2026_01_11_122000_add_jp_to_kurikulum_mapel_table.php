<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kurikulum_mapel', function (Blueprint $table) {
            $table->unsignedSmallInteger('jp')->default(0)->after('mata_pelajaran_id');
        });
    }

    public function down(): void
    {
        Schema::table('kurikulum_mapel', function (Blueprint $table) {
            $table->dropColumn('jp');
        });
    }
};
