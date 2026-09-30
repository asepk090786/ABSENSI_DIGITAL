<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('nilai_harian', 'materi_pelajaran')) {
            Schema::table('nilai_harian', function (Blueprint $table) {
                $table->string('materi_pelajaran', 255)->nullable()->after('rencana_pembelajaran_id');
            });
        }

        if (! Schema::hasColumn('nilai_harian', 'jenis_penilaian')) {
            Schema::table('nilai_harian', function (Blueprint $table) {
                $table->string('jenis_penilaian', 255)->nullable()->after('materi_pelajaran');
            });
        }

        $nilaiHarians = DB::table('nilai_harian')->get([
            'id',
            'materi_pelajaran',
            'jenis_penilaian',
            'rencana_pembelajaran_id',
            'komponen_id',
        ]);

        $rencanaJudulById = Schema::hasTable('rencana_pembelajarans')
            ? DB::table('rencana_pembelajarans')->pluck('judul', 'id')->all()
            : [];

        $komponenNamaById = Schema::hasTable('komponen_nilai')
            ? DB::table('komponen_nilai')->pluck('nama_komponen', 'id')->all()
            : [];

        foreach ($nilaiHarians as $nilai) {
            $materiSaatIni = trim((string) ($nilai->materi_pelajaran ?? ''));
            $jenisSaatIni = trim((string) ($nilai->jenis_penilaian ?? ''));

            $needsMateriUpdate = $nilai->materi_pelajaran === null || $materiSaatIni === '';
            $needsJenisUpdate = $nilai->jenis_penilaian === null || $jenisSaatIni === '';

            if (! $needsMateriUpdate && ! $needsJenisUpdate) {
                continue;
            }

            $materiBaru = $materiSaatIni !== ''
                ? $materiSaatIni
                : trim((string) ($rencanaJudulById[$nilai->rencana_pembelajaran_id] ?? ''));

            if ($materiBaru === '') {
                $materiBaru = trim((string) ($komponenNamaById[$nilai->komponen_id] ?? ''));
            }

            if ($materiBaru === '') {
                $materiBaru = 'Harian / Umum';
            }

            $jenisBaru = $jenisSaatIni !== ''
                ? $jenisSaatIni
                : trim((string) ($komponenNamaById[$nilai->komponen_id] ?? ''));

            if ($jenisBaru === '') {
                $jenisBaru = 'Harian / Umum';
            }

            DB::table('nilai_harian')
                ->where('id', $nilai->id)
                ->update([
                    'materi_pelajaran' => $needsMateriUpdate ? $materiBaru : $nilai->materi_pelajaran,
                    'jenis_penilaian' => $needsJenisUpdate ? $jenisBaru : $nilai->jenis_penilaian,
                ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('nilai_harian', 'jenis_penilaian')) {
            Schema::table('nilai_harian', function (Blueprint $table) {
                $table->dropColumn('jenis_penilaian');
            });
        }

        if (Schema::hasColumn('nilai_harian', 'materi_pelajaran')) {
            Schema::table('nilai_harian', function (Blueprint $table) {
                $table->dropColumn('materi_pelajaran');
            });
        }
    }
};
