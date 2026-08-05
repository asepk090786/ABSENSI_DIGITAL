<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pengembangan_sertifikats', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_sertifikats', 'peserta_id')) {
                $table->unsignedBigInteger('peserta_id')->nullable()->change();
            }
        });
    }

    public function down()
    {
        Schema::table('pengembangan_sertifikats', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_sertifikats', 'peserta_id')) {
                $table->unsignedBigInteger('peserta_id')->nullable(false)->change();
            }
        });
    }
};
