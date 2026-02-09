<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('nilai_harian', function (Blueprint $table) {
            $table->foreignId('rencana_pembelajaran_id')
                ->nullable()
                ->after('komponen_id')
                ->constrained('rencana_pembelajarans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('nilai_harian', function (Blueprint $table) {
            $table->dropForeign(['rencana_pembelajaran_id']);
            $table->dropColumn('rencana_pembelajaran_id');
        });
    }
};