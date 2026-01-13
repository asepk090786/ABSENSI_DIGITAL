<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add sequence number to support multiple sessions per day
        Schema::table('jam_belajar', function (Blueprint $table) {
            if (!Schema::hasColumn('jam_belajar', 'urutan')) {
                $table->integer('urutan')->default(1)->after('hari')->comment('Nomor urut jam (jam ke-1, jam ke-2, dst)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('jam_belajar', function (Blueprint $table) {
            if (Schema::hasColumn('jam_belajar', 'urutan')) {
                $table->dropColumn('urutan');
            }
        });
    }
};
