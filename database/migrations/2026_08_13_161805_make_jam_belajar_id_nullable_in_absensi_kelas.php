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
        Schema::table('absensi_kelas', function (Blueprint $table) {
            $table->unsignedBigInteger('jam_belajar_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('absensi_kelas', function (Blueprint $table) {
            $table->unsignedBigInteger('jam_belajar_id')->nullable(false)->change();
        });
    }
};
