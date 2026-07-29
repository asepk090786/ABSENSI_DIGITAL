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
        if (!Schema::hasTable('pengembangan_sertifikat_templates')) return;
        if (!Schema::hasColumn('pengembangan_sertifikat_templates', 'font_file')) {
            Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
                $table->string('font_file', 255)->nullable()->after('placeholder_positions');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pengembangan_sertifikat_templates', 'font_file')) {
            Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
                $table->dropColumn('font_file');
            });
        }
    }
};
