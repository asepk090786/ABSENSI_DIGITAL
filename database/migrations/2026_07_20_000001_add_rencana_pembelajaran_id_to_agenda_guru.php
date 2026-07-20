<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agenda_guru', function (Blueprint $table) {
            if (!Schema::hasColumn('agenda_guru', 'rencana_pembelajaran_id')) {
                $table->foreignId('rencana_pembelajaran_id')->nullable()->after('kegiatan')->constrained('rencana_pembelajarans');
            }
        });
    }

    public function down(): void
    {
        Schema::table('agenda_guru', function (Blueprint $table) {
            if (Schema::hasColumn('agenda_guru', 'rencana_pembelajaran_id')) {
                $table->dropForeign(['rencana_pembelajaran_id']);
                $table->dropColumn('rencana_pembelajaran_id');
            }
        });
    }
};
