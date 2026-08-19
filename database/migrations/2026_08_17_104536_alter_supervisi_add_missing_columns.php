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
        Schema::table('supervisi', function (Blueprint $table) {
            $table->foreignId('supervisor_id')->nullable()->constrained('guru')->onDelete('set null');
            $table->text('tujuan')->nullable();
            $table->text('fokus')->nullable();
            $table->enum('status', ['Draft', 'Terjadwal', 'Berlangsung', 'Selesai', 'Dibatalkan'])->default('Draft');
            $table->longText('catatan_objektif')->nullable();
            $table->longText('refleksi_guru')->nullable();
            $table->longText('refleksi_supervisor')->nullable();
            $table->longText('umpan_balik')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supervisi', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['supervisor_id']);
            $table->dropColumn(['supervisor_id', 'tujuan', 'fokus', 'status', 'catatan_objektif', 'refleksi_guru', 'refleksi_supervisor', 'umpan_balik']);
        });
    }
};
