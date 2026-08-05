<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembangan_sertifikat_templates', 'include_verification_code')) {
                $table->boolean('include_verification_code')->default(true)->after('placeholder_positions');
            }
            if (!Schema::hasColumn('pengembangan_sertifikat_templates', 'include_verification_qr')) {
                $table->boolean('include_verification_qr')->default(true)->after('include_verification_code');
            }
        });
    }

    public function down()
    {
        Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_sertifikat_templates', 'include_verification_qr')) {
                $table->dropColumn('include_verification_qr');
            }
            if (Schema::hasColumn('pengembangan_sertifikat_templates', 'include_verification_code')) {
                $table->dropColumn('include_verification_code');
            }
        });
    }
};
