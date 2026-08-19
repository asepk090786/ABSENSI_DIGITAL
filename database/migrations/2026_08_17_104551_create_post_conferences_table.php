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
        Schema::create('post_conferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supervisi_id')->constrained('supervisi')->onDelete('cascade');
            $table->longText('refleksi_guru')->nullable();
            $table->longText('refleksi_supervisor')->nullable();
            $table->dateTime('tanggal_pelaksanaan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_conferences');
    }
};
