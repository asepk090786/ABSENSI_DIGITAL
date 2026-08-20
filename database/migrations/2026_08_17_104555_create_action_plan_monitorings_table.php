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
        if (! Schema::hasTable('action_plan_monitorings')) {
            Schema::create('action_plan_monitorings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('action_plan_id')->constrained('action_plans')->onDelete('cascade');
                $table->date('tanggal_monitoring');
                $table->integer('progress_persen')->default(0);
                $table->text('catatan');
                $table->text('bukti')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('action_plan_monitorings');
    }
};
