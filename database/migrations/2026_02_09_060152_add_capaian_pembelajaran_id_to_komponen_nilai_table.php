<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('komponen_nilai', function (Blueprint $table) {
            $table->foreignId('capaian_pembelajaran_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('komponen_nilai', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['capaian_pembelajaran_id']);
            $table->dropColumn('capaian_pembelajaran_id');
        });
    }
};
