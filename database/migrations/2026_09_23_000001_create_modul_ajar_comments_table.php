<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modul_ajar_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('modul_ajar_id')->constrained('rencana_pembelajarans')->cascadeOnDelete();
            $table->foreignId('pengawas_user_id')->constrained('users')->cascadeOnDelete();
            $table->text('komentar');
            $table->timestamps();

            $table->index(['modul_ajar_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modul_ajar_comments');
    }
};
