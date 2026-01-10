<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (! Schema::hasColumn('siswa', 'nisn')) {
                $table->string('nisn')->nullable()->unique()->after('nis');
            }
            if (! Schema::hasColumn('siswa', 'jenis_kelamin')) {
                $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('nama');
            }
            if (! Schema::hasColumn('siswa', 'email')) {
                $table->string('email')->nullable()->unique()->after('kelas_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('siswa', function (Blueprint $table) {
            if (Schema::hasColumn('siswa', 'nisn')) {
                $table->dropColumn('nisn');
            }
            if (Schema::hasColumn('siswa', 'jenis_kelamin')) {
                $table->dropColumn('jenis_kelamin');
            }
            if (Schema::hasColumn('siswa', 'email')) {
                $table->dropColumn('email');
            }
        });
    }
};
