<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('komponen_nilai', 'domain')) {
            Schema::table('komponen_nilai', function (Blueprint $table) {
                $table->string('domain', 20)->nullable()->after('bobot');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('komponen_nilai', 'domain')) {
            Schema::table('komponen_nilai', function (Blueprint $table) {
                $table->dropColumn('domain');
            });
        }
    }
};
