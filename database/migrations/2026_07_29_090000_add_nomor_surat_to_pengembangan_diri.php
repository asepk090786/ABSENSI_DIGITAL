<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pengembangan_diri', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembangan_diri', 'nomor_surat')) {
                $table->string('nomor_surat', 255)->nullable()->after('tema_kegiatan');
            }
        });
    }

    public function down()
    {
        Schema::table('pengembangan_diri', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_diri', 'nomor_surat')) {
                $table->dropColumn('nomor_surat');
            }
        });
    }
};
