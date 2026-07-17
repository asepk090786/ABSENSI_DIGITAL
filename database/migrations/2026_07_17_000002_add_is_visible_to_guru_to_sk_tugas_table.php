<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sk_tugas', 'is_visible_to_guru')) {
            Schema::table('sk_tugas', function (Blueprint $table) {
                $table->boolean('is_visible_to_guru')->default(true)->after('file');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sk_tugas', 'is_visible_to_guru')) {
            Schema::table('sk_tugas', function (Blueprint $table) {
                $table->dropColumn('is_visible_to_guru');
            });
        }
    }
};
