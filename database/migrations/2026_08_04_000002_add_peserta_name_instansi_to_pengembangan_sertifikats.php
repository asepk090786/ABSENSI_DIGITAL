<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pengembangan_sertifikats', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembangan_sertifikats', 'peserta_name')) {
                $table->string('peserta_name')->nullable()->after('peserta_id');
            }
            if (!Schema::hasColumn('pengembangan_sertifikats', 'instansi')) {
                $table->string('instansi')->nullable()->after('peserta_name');
            }
        });
    }

    public function down()
    {
        Schema::table('pengembangan_sertifikats', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_sertifikats', 'instansi')) {
                $table->dropColumn('instansi');
            }
            if (Schema::hasColumn('pengembangan_sertifikats', 'peserta_name')) {
                $table->dropColumn('peserta_name');
            }
        });
    }
};
