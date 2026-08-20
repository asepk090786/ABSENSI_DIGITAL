<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('komponen_nilai', 'kelas_id')) {
            Schema::table('komponen_nilai', function (Blueprint $table) {
                $table->foreignId('kelas_id')
                    ->nullable()
                    ->after('mata_pelajaran_id')
                    ->constrained('kelas')
                    ->nullOnDelete();
                $table->index('kelas_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('komponen_nilai', 'kelas_id')) {
            Schema::table('komponen_nilai', function (Blueprint $table) {
                $table->dropForeign(['kelas_id']);
                $table->dropIndex(['kelas_id']);
                $table->dropColumn('kelas_id');
            });
        }
    }
};