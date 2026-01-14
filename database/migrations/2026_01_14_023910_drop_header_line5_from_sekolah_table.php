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
            $table->dropColumn(['header_line5', 'header_line5_spacing']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->text('header_line5')->nullable()->after('header_line4_spacing');
            $table->decimal('header_line5_spacing', 3, 1)->default(1.0)->after('header_line5');
        });
    }
};
