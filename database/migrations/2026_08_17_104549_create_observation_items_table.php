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
        if (! Schema::hasTable('observation_items')) {
            Schema::create('observation_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('supervisi_id')->constrained('supervisi')->onDelete('cascade');
                $table->foreignId('indicator_id')->constrained('supervision_indicators')->onDelete('cascade');
                $table->integer('skor')->nullable();
                $table->text('catatan')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('observation_items');
    }
};
