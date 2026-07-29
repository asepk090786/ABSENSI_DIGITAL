<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pengembangan_sertifikats', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembangan_sertifikats', 'nomor_sertifikat')) {
                $table->string('nomor_sertifikat')->nullable()->after('barcode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pengembangan_sertifikats', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_sertifikats', 'nomor_sertifikat')) {
                $table->dropColumn('nomor_sertifikat');
            }
        });
    }
};
