<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rencana_pembelajarans', function (Blueprint $table) {
            foreach ([
                'capaian_pembelajaran',
                'tujuan',
                'metode',
                'media',
                'sumber',
                'penilaian',
                'alokasi_waktu',
                'praktik_pedagogis',
                'lingkungan_pembelajaran',
                'pemanfaatan_digital',
                'pengalaman_pembelajaran',
                'refleksi_pembelajaran',
            ] as $column) {
                if (Schema::hasColumn('rencana_pembelajarans', $column)) {
                    $table->longText($column)->nullable()->change();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rencana_pembelajarans', function (Blueprint $table) {
            foreach ([
                'capaian_pembelajaran',
                'tujuan',
                'metode',
                'media',
                'sumber',
                'penilaian',
                'alokasi_waktu',
                'praktik_pedagogis',
                'lingkungan_pembelajaran',
                'pemanfaatan_digital',
                'pengalaman_pembelajaran',
                'refleksi_pembelajaran',
            ] as $column) {
                if (Schema::hasColumn('rencana_pembelajarans', $column)) {
                    $table->text($column)->nullable()->change();
                }
            }
        });
    }
};
