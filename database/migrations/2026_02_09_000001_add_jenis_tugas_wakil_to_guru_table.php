<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            if (! Schema::hasColumn('guru', 'jenis_tugas_wakil')) {
                $table->string('jenis_tugas_wakil')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('guru', function (Blueprint $table) {
            $table->dropColumn('jenis_tugas_wakil');
        });
    }
};
