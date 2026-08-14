<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'tenaga_pendidikan_id')) {
                $table->foreignId('tenaga_pendidikan_id')->nullable()->after('siswa_id')->constrained('tenaga_pendidikan')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'tenaga_pendidikan_id')) {
                $table->dropConstrainedForeignId('tenaga_pendidikan_id');
            }
        });
    }
};
