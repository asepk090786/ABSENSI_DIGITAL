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
        if (!Schema::hasTable('ekstrakurikuler')) {
            Schema::create('ekstrakurikuler', function (Blueprint $table) {
                $table->id();
                $table->string('nama', 150);
                $table->text('deskripsi')->nullable();
                $table->string('lokasi', 200)->nullable();
                $table->string('logo', 255)->nullable();
                $table->integer('kuota_max')->nullable();
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
                $table->foreignId('guru_id')->nullable()->constrained('guru')->nullOnDelete()->comment('Kepala pembina');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ekstrakurikuler');
    }
};
