<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->string('telepon')->nullable()->after('username');
            $table->string('alamat')->nullable()->after('telepon');
            $table->date('tanggal_lahir')->nullable()->after('alamat');
            $table->string('jenis_kelamin', 2)->nullable()->after('tanggal_lahir');
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn(['telepon', 'alamat', 'tanggal_lahir', 'jenis_kelamin']);
        });
    }
};
