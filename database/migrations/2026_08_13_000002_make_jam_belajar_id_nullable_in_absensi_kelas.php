<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('absensi_kelas', function (Blueprint $table) {
            // Make jam_belajar_id nullable since manual verification can apply to the whole day
            $table->foreignId('jam_belajar_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('absensi_kelas', function (Blueprint $table) {
            // Revert back to NOT NULL
            $table->foreignId('jam_belajar_id')->nullable(false)->change();
        });
    }
};
