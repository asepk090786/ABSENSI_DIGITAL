<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (! Schema::hasColumn('guru', 'username')) {
                $table->string('username')->nullable()->unique()->after('nama');
            }
            if (! Schema::hasColumn('guru', 'password')) {
                $table->string('password')->nullable()->after('username');
            }
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (Schema::hasColumn('guru', 'username')) {
                $table->dropColumn('username');
            }
            if (Schema::hasColumn('guru', 'password')) {
                $table->dropColumn('password');
            }
        });
    }
};
