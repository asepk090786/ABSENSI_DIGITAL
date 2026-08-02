<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('pengembangan_peserta')) {
            Schema::table('pengembangan_peserta', function (Blueprint $table) {
                $table->unsignedBigInteger('peserta_id')->nullable()->change();
                $table->string('peserta_name')->nullable()->after('peserta_id');
                $table->string('instansi')->nullable()->after('peserta_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pengembangan_peserta')) {
            Schema::table('pengembangan_peserta', function (Blueprint $table) {
                $table->dropColumn(['peserta_name', 'instansi']);
                $table->unsignedBigInteger('peserta_id')->nullable(false)->change();
            });
        }
    }
};
