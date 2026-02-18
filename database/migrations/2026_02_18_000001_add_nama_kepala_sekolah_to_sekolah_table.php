<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->string('nama_kepala_sekolah')->nullable()->after('nama_sekolah');
        });
    }

    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->dropColumn('nama_kepala_sekolah');
        });
    }
};
