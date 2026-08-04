<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pengembangan_sertifikats', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembangan_sertifikats', 'bukti_dukung_daftar_hadir')) {
                $table->string('bukti_dukung_daftar_hadir')->nullable()->after('template_id');
            }
            if (!Schema::hasColumn('pengembangan_sertifikats', 'bukti_dukung_dokumentasi')) {
                $table->json('bukti_dukung_dokumentasi')->nullable()->after('bukti_dukung_daftar_hadir');
            }
            if (!Schema::hasColumn('pengembangan_sertifikats', 'bukti_dukung_materi')) {
                $table->json('bukti_dukung_materi')->nullable()->after('bukti_dukung_dokumentasi');
            }
        });
    }

    public function down()
    {
        Schema::table('pengembangan_sertifikats', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_sertifikats', 'bukti_dukung_materi')) {
                $table->dropColumn('bukti_dukung_materi');
            }
            if (Schema::hasColumn('pengembangan_sertifikats', 'bukti_dukung_dokumentasi')) {
                $table->dropColumn('bukti_dukung_dokumentasi');
            }
            if (Schema::hasColumn('pengembangan_sertifikats', 'bukti_dukung_daftar_hadir')) {
                $table->dropColumn('bukti_dukung_daftar_hadir');
            }
        });
    }
};
