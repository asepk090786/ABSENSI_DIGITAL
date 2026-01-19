<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('agenda_kelas', function (Blueprint $table) {
            $table->text('tujuan_pembelajaran')->nullable()->after('kegiatan');
            $table->text('strategi_pembelajaran')->nullable()->after('tujuan_pembelajaran');
            $table->text('media_pembelajaran')->nullable()->after('strategi_pembelajaran');
            $table->text('sumber_belajar')->nullable()->after('media_pembelajaran');
            $table->text('penilaian')->nullable()->after('sumber_belajar');
            $table->text('catatan_tambahan')->nullable()->after('penilaian');
        });
    }

    public function down(): void
    {
        Schema::table('agenda_kelas', function (Blueprint $table) {
            $table->dropColumn([
                'tujuan_pembelajaran',
                'strategi_pembelajaran',
                'media_pembelajaran',
                'sumber_belajar',
                'penilaian',
                'catatan_tambahan'
            ]);
        });
    }
};
