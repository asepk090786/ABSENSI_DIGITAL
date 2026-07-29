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
        Schema::table('attendance_verification_codes', function (Blueprint $table) {
            $table->time('valid_from')->nullable()->after('tanggal');
            $table->time('valid_to')->nullable()->after('valid_from');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_verification_codes', function (Blueprint $table) {
            $table->dropColumn(['valid_from', 'valid_to']);
        });
    }
};
