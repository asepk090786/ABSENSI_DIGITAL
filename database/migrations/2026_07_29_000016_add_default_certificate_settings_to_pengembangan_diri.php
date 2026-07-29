<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengembangan_diri', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembangan_diri', 'default_nomor_sertifikat')) {
                $table->string('default_nomor_sertifikat')->nullable()->after('deskripsi');
            }
            if (!Schema::hasColumn('pengembangan_diri', 'default_template_id')) {
                $table->unsignedBigInteger('default_template_id')->nullable()->after('default_nomor_sertifikat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengembangan_diri', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_diri', 'default_template_id')) {
                $table->dropColumn('default_template_id');
            }
            if (Schema::hasColumn('pengembangan_diri', 'default_nomor_sertifikat')) {
                $table->dropColumn('default_nomor_sertifikat');
            }
        });
    }
};
