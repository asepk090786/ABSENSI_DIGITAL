<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembangan_sertifikat_templates', 'placeholder_positions')) {
                $table->text('placeholder_positions')->nullable()->after('template_html');
            }
            if (!Schema::hasColumn('pengembangan_sertifikat_templates', 'background_image')) {
                $table->string('background_image')->nullable()->after('placeholder_positions');
            }
        });
    }

    public function down()
    {
        Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_sertifikat_templates', 'background_image')) {
                $table->dropColumn('background_image');
            }
            if (Schema::hasColumn('pengembangan_sertifikat_templates', 'placeholder_positions')) {
                $table->dropColumn('placeholder_positions');
            }
        });
    }
};
