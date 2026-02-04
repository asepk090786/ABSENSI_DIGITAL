<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('ekstrakurikuler');

        Schema::create('ekstrakurikuler', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ekskul');
            $table->text('deskripsi')->nullable();
            $table->unsignedBigInteger('pembina_id');
            $table->timestamps();
            $table->index('pembina_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ekstrakurikuler');
    }
};
