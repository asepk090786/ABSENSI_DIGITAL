<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengembangan_diri', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembangan_diri', 'tema_kegiatan')) {
                $table->string('tema_kegiatan')->nullable()->after('nama_kegiatan');
            }
        });
    }

    public function down()
    {
        Schema::table('pengembangan_diri', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_diri', 'tema_kegiatan')) {
                $table->dropColumn('tema_kegiatan');
            }
        });
    }
};
