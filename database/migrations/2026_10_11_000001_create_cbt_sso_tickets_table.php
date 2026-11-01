<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cbt_sso_tickets')) {
            Schema::create('cbt_sso_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->char('ticket_hash', 64)->unique();
                $table->char('state', 64);
                $table->timestamp('expires_at')->index();
                $table->timestamp('consumed_at')->nullable()->index();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cbt_sso_tickets');
    }
};
