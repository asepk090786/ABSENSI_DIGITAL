<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('role_user')) {
            Schema::create('role_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('role_id');
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                
                $table->index('role_id');
                $table->index('user_id');
                $table->unique(['role_id', 'user_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
    }
};
