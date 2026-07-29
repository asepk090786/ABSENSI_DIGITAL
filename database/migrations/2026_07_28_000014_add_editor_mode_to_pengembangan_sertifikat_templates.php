<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembangan_sertifikat_templates', 'editor_mode')) {
                $table->string('editor_mode')->nullable()->after('placeholder_positions');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_sertifikat_templates', 'editor_mode')) {
                $table->dropColumn('editor_mode');
            }
        });
    }
};
