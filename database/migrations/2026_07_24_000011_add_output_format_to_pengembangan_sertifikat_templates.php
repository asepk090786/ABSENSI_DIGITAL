<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembangan_sertifikat_templates', 'output_format')) {
                $table->string('output_format')->nullable()->default('pdf')->after('template_html');
            }
        });
    }

    public function down()
    {
        Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_sertifikat_templates', 'output_format')) {
                $table->dropColumn('output_format');
            }
        });
    }
};
