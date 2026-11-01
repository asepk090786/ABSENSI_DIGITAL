<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('absensi_guru', function (Blueprint $table) {
            $table->string('bukti_path')->nullable();
            $table->uuid('bukti_token')->nullable()->unique();
        });

        Schema::create('absensi_guru_locks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->date('tanggal');
            $table->foreignId('locked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['guru_id', 'tanggal']);
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_guru_locks');

        Schema::table('absensi_guru', function (Blueprint $table) {
            $table->dropUnique(['bukti_token']);
            $table->dropColumn(['bukti_path', 'bukti_token']);
        });
    }
};
