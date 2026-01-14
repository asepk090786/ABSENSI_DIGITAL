<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->decimal('header_line1_spacing', 3, 1)->default(1.0)->after('header_line1');
            $table->decimal('header_line2_spacing', 3, 1)->default(1.0)->after('header_line2');
            $table->decimal('header_line3_spacing', 3, 1)->default(1.0)->after('header_line3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->dropColumn(['header_line1_spacing', 'header_line2_spacing', 'header_line3_spacing']);
        });
    }
};
