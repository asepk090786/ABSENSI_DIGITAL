<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('komponen_nilai', 'mata_pelajaran_id')) {
            Schema::table('komponen_nilai', function (Blueprint $table) {
                $table->foreignId('mata_pelajaran_id')
                    ->nullable()
                    ->after('guru_id')
                    ->constrained('mata_pelajaran')
                    ->nullOnDelete();
                $table->index('mata_pelajaran_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('komponen_nilai', 'mata_pelajaran_id')) {
            Schema::table('komponen_nilai', function (Blueprint $table) {
                $table->dropForeign(['mata_pelajaran_id']);
                $table->dropIndex(['mata_pelajaran_id']);
                $table->dropColumn('mata_pelajaran_id');
            });
        }
    }
};
