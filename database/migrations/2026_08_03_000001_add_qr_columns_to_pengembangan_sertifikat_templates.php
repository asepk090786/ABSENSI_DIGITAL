<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('pengembangan_sertifikat_templates', 'include_barcode')) {
                $table->boolean('include_barcode')->default(false)->after('placeholder_positions');
            }
            if (! Schema::hasColumn('pengembangan_sertifikat_templates', 'barcode_is_qr')) {
                $table->boolean('barcode_is_qr')->default(true)->after('include_barcode');
            }
            if (! Schema::hasColumn('pengembangan_sertifikat_templates', 'barcode_qr_size')) {
                $table->integer('barcode_qr_size')->nullable()->after('barcode_is_qr');
            }
        });
    }

    public function down()
    {
        Schema::table('pengembangan_sertifikat_templates', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_sertifikat_templates', 'barcode_qr_size')) {
                $table->dropColumn('barcode_qr_size');
            }
            if (Schema::hasColumn('pengembangan_sertifikat_templates', 'barcode_is_qr')) {
                $table->dropColumn('barcode_is_qr');
            }
            if (Schema::hasColumn('pengembangan_sertifikat_templates', 'include_barcode')) {
                $table->dropColumn('include_barcode');
            }
        });
    }
};
