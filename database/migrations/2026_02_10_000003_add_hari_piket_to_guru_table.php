<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (! Schema::hasColumn('guru', 'hari_piket')) {
                $table->json('hari_piket')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (Schema::hasColumn('guru', 'hari_piket')) {
                $table->dropColumn('hari_piket');
            }
        });
    }
};
