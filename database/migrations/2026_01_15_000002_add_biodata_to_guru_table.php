<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (! Schema::hasColumn('guru', 'telepon')) {
                $table->string('telepon')->nullable()->after('username');
            }
            if (! Schema::hasColumn('guru', 'alamat')) {
                $table->string('alamat')->nullable()->after('telepon');
            }
            if (! Schema::hasColumn('guru', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable()->after('alamat');
            }
            if (! Schema::hasColumn('guru', 'jenis_kelamin')) {
                $table->string('jenis_kelamin', 2)->nullable()->after('tanggal_lahir');
            }
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (Schema::hasColumn('guru', 'telepon')) {
                $table->dropColumn('telepon');
            }
            if (Schema::hasColumn('guru', 'alamat')) {
                $table->dropColumn('alamat');
            }
            if (Schema::hasColumn('guru', 'tanggal_lahir')) {
                $table->dropColumn('tanggal_lahir');
            }
            if (Schema::hasColumn('guru', 'jenis_kelamin')) {
                $table->dropColumn('jenis_kelamin');
            }
        });
    }
};
