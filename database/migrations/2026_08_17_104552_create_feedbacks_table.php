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
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_conference_id')->constrained('post_conferences')->onDelete('cascade');
            $table->longText('kekuatan'); // Hal-hal positif/kekuatan guru
            $table->longText('area_pengembangan'); // Area pengembangan
            $table->longText('umpan_balik'); // Feedback lengkap
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
