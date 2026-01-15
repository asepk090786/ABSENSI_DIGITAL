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
            // Header Line 1
            $table->string('header_line1')->nullable()->after('header_html');
            $table->string('header_line1_font', 50)->default('Arial')->after('header_line1');
            $table->boolean('header_line1_bold')->default(false)->after('header_line1_font');
            $table->boolean('header_line1_italic')->default(false)->after('header_line1_bold');
            $table->boolean('header_line1_underline')->default(false)->after('header_line1_italic');
            
            // Header Line 2
            $table->string('header_line2')->nullable()->after('header_line1_underline');
            $table->string('header_line2_font', 50)->default('Arial')->after('header_line2');
            $table->boolean('header_line2_bold')->default(true)->after('header_line2_font');
            $table->boolean('header_line2_italic')->default(false)->after('header_line2_bold');
            $table->boolean('header_line2_underline')->default(false)->after('header_line2_italic');
            
            // Header Line 3
            $table->string('header_line3')->nullable()->after('header_line2_underline');
            $table->string('header_line3_font', 50)->default('Arial')->after('header_line3');
            $table->boolean('header_line3_bold')->default(false)->after('header_line3_font');
            $table->boolean('header_line3_italic')->default(false)->after('header_line3_bold');
            $table->boolean('header_line3_underline')->default(false)->after('header_line3_italic');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sekolah', function (Blueprint $table) {
            $table->dropColumn([
                'header_line1',
                'header_line1_font',
                'header_line1_bold',
                'header_line1_italic',
                'header_line1_underline',
                'header_line2',
                'header_line2_font',
                'header_line2_bold',
                'header_line2_italic',
                'header_line2_underline',
                'header_line3',
                'header_line3_font',
                'header_line3_bold',
                'header_line3_italic',
                'header_line3_underline',
            ]);
        });
    }
};
