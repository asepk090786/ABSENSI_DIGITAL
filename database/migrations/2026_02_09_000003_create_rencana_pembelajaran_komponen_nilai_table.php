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
        if (!Schema::hasTable('rencana_pembelajaran_komponen_nilai')) {
            Schema::create('rencana_pembelajaran_komponen_nilai', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rencana_pembelajaran_id');
                $table->unsignedBigInteger('komponen_nilai_id');
                $table->timestamps();
                
                // Foreign key constraints with shorter names
                $table->foreign('rencana_pembelajaran_id', 'rp_id_fk')
                    ->references('id')
                    ->on('rencana_pembelajarans')
                    ->cascadeOnDelete();
                
                $table->foreign('komponen_nilai_id', 'kn_id_fk')
                    ->references('id')
                    ->on('komponen_nilai')
                    ->cascadeOnDelete();
                
                // Unique constraint to prevent duplicates
                $table->unique(['rencana_pembelajaran_id', 'komponen_nilai_id'], 'rp_kn_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rencana_pembelajaran_komponen_nilai');
    }
};
