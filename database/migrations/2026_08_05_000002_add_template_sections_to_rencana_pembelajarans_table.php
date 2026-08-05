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
            if (!Schema::hasColumn('rencana_pembelajarans', 'alokasi_waktu')) {
                $table->text('alokasi_waktu')->nullable()->after('penilaian');
            }
            if (!Schema::hasColumn('rencana_pembelajarans', 'praktik_pedagogis')) {
                $table->text('praktik_pedagogis')->nullable()->after('alokasi_waktu');
            }
            if (!Schema::hasColumn('rencana_pembelajarans', 'lingkungan_pembelajaran')) {
                $table->text('lingkungan_pembelajaran')->nullable()->after('praktik_pedagogis');
            }
            if (!Schema::hasColumn('rencana_pembelajarans', 'pemanfaatan_digital')) {
                $table->text('pemanfaatan_digital')->nullable()->after('lingkungan_pembelajaran');
            }
            if (!Schema::hasColumn('rencana_pembelajarans', 'pengalaman_pembelajaran')) {
                $table->text('pengalaman_pembelajaran')->nullable()->after('pemanfaatan_digital');
            }
            if (!Schema::hasColumn('rencana_pembelajarans', 'refleksi_pembelajaran')) {
                $table->text('refleksi_pembelajaran')->nullable()->after('pengalaman_pembelajaran');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rencana_pembelajarans', function (Blueprint $table) {
            if (Schema::hasColumn('rencana_pembelajarans', 'refleksi_pembelajaran')) {
                $table->dropColumn('refleksi_pembelajaran');
            }
            if (Schema::hasColumn('rencana_pembelajarans', 'pengalaman_pembelajaran')) {
                $table->dropColumn('pengalaman_pembelajaran');
            }
            if (Schema::hasColumn('rencana_pembelajarans', 'pemanfaatan_digital')) {
                $table->dropColumn('pemanfaatan_digital');
            }
            if (Schema::hasColumn('rencana_pembelajarans', 'lingkungan_pembelajaran')) {
                $table->dropColumn('lingkungan_pembelajaran');
            }
            if (Schema::hasColumn('rencana_pembelajarans', 'praktik_pedagogis')) {
                $table->dropColumn('praktik_pedagogis');
            }
            if (Schema::hasColumn('rencana_pembelajarans', 'alokasi_waktu')) {
                $table->dropColumn('alokasi_waktu');
            }
        });
    }
};
