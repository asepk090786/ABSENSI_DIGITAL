<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('pengembangan_sertifikats', function (Blueprint $table) {
            if (!Schema::hasColumn('pengembangan_sertifikats', 'is_visible')) {
                $table->boolean('is_visible')->default(true)->after('template_id');
            }
        });
    }

    public function down()
    {
        Schema::table('pengembangan_sertifikats', function (Blueprint $table) {
            if (Schema::hasColumn('pengembangan_sertifikats', 'is_visible')) {
                $table->dropColumn('is_visible');
            }
        });
    }
};
