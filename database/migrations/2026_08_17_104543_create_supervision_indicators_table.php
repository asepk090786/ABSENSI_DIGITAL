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
        if (! Schema::hasTable('supervision_indicators')) {
            Schema::create('supervision_indicators', function (Blueprint $table) {
                $table->id();
                $table->foreignId('instrument_id')->constrained('supervision_instruments')->onDelete('cascade');
                $table->string('kategori');
                $table->text('indikator');
                $table->text('deskripsi')->nullable();
                $table->integer('bobot')->default(1);
                $table->integer('urutan')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supervision_indicators');
    }
};
