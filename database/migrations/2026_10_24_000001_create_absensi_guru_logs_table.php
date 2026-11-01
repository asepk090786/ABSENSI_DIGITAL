<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absensi_guru_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('absensi_guru_id')->constrained('absensi_guru')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('actor_name');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->timestamps();

            $table->index(['absensi_guru_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absensi_guru_logs');
    }
};