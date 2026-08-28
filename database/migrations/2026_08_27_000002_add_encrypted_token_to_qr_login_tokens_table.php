<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('qr_login_tokens') && ! Schema::hasColumn('qr_login_tokens', 'encrypted_token')) {
            Schema::table('qr_login_tokens', function (Blueprint $table) {
                $table->text('encrypted_token')->nullable()->after('token_hash');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('qr_login_tokens') && Schema::hasColumn('qr_login_tokens', 'encrypted_token')) {
            Schema::table('qr_login_tokens', function (Blueprint $table) {
                $table->dropColumn('encrypted_token');
            });
        }
    }
};
