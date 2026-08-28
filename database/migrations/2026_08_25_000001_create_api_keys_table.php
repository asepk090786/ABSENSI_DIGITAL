<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('api_keys')) {
            Schema::create('api_keys', function (Blueprint $table) {
                $table->id();
                $table->string('name')->default('Master Data');
                $table->string('key_prefix', 12);
                $table->string('key_hash', 64)->unique();
                $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('revoked_at')->nullable();
                $table->timestamps();

                $table->index(['revoked_at', 'key_prefix']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
