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
        if (! Schema::hasTable('supervision_instruments')) {
            Schema::create('supervision_instruments', function (Blueprint $table) {
                $table->id();
                $table->string('nama')->unique();
                $table->text('deskripsi')->nullable();
                $table->string('kategori')->nullable();
                $table->string('tipe')->default('checklist');
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
        Schema::dropIfExists('supervision_instruments');
    }
};
