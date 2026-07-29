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
        if (Schema::hasTable('ekskul_pembina')) return;
        Schema::create('ekskul_pembina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ekstrakurikuler_id')->constrained('ekstrakurikuler')->cascadeOnDelete();
            $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
            $table->string('jabatan', 100)->nullable()->comment('Ketua/Sekretaris dll');
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();
            $table->unique(['ekstrakurikuler_id', 'guru_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekskul_pembina');
    }
};
