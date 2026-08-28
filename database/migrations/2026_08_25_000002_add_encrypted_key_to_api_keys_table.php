<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('api_keys') && ! Schema::hasColumn('api_keys', 'encrypted_key')) {
            Schema::table('api_keys', function (Blueprint $table) {
                $table->text('encrypted_key')->nullable()->after('key_hash');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('api_keys') && Schema::hasColumn('api_keys', 'encrypted_key')) {
            Schema::table('api_keys', function (Blueprint $table) {
                $table->dropColumn('encrypted_key');
            });
        }
    }
};
