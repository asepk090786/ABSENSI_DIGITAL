<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'kepala_sekolah_id')) {
                $table->foreignId('kepala_sekolah_id')->nullable()->constrained('kepala_sekolah');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'kepala_sekolah_id')) {
                $table->dropForeign(['kepala_sekolah_id']);
                $table->dropColumn('kepala_sekolah_id');
            }
        });
    }
};
