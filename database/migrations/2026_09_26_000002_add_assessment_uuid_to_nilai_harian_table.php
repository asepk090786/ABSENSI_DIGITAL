<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('nilai_harian', 'assessment_uuid')) {
            Schema::table('nilai_harian', function (Blueprint $table) {
                $table->string('assessment_uuid', 36)->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('nilai_harian', 'assessment_uuid')) {
            Schema::table('nilai_harian', function (Blueprint $table) {
                $table->dropIndex(['assessment_uuid']);
                $table->dropColumn('assessment_uuid');
            });
        }
    }
};