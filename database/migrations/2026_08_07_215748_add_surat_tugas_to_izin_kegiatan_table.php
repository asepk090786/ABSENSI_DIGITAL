<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('izin_kegiatan', 'surat_tugas')) {
            Schema::table('izin_kegiatan', function (Blueprint $table) {
                $table->string('surat_tugas')->nullable()->after('keterangan_kegiatan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('izin_kegiatan', 'surat_tugas')) {
            Schema::table('izin_kegiatan', function (Blueprint $table) {
                $table->dropColumn('surat_tugas');
            });
        }
    }
};
