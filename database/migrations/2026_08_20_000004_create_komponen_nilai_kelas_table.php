<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('komponen_nilai_kelas')) {
            Schema::create('komponen_nilai_kelas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('komponen_nilai_id')->constrained('komponen_nilai')->cascadeOnDelete();
                $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['komponen_nilai_id', 'kelas_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('komponen_nilai_kelas');
    }
};