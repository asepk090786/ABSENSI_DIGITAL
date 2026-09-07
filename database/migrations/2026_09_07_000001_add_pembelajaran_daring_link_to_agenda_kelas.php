<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agenda_kelas', function (Blueprint $table) {
            $table->string('platform_pembelajaran_daring', 30)->nullable()->after('kegiatan');
            $table->string('link_pembelajaran_daring', 2048)->nullable()->after('platform_pembelajaran_daring');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_kelas', function (Blueprint $table) {
            $table->dropColumn(['platform_pembelajaran_daring', 'link_pembelajaran_daring']);
        });
    }
};
