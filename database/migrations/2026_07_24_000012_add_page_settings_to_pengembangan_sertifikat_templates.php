<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembangan_sertifikat_templates', 'page_size')) {
                $table->string('page_size')->nullable()->default('A4')->after('output_format');
            }
            if (!Schema::hasColumn('pengembangan_sertifikat_templates', 'page_orientation')) {
                $table->string('page_orientation')->nullable()->default('portrait')->after('page_size');
            }
        });
    }

    public function down()
    {
        Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_sertifikat_templates', 'page_orientation')) {
                $table->dropColumn('page_orientation');
            }
            if (Schema::hasColumn('pengembangan_sertifikat_templates', 'page_size')) {
                $table->dropColumn('page_size');
            }
        });
    }
};
