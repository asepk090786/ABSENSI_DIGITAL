<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('absensi_guru')) {
            return;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE absensi_guru MODIFY status ENUM('hadir','tidak_hadir','izin','sakit','dinas_luar') NOT NULL");
            return;
        }

        if ($driver === 'sqlite') {
            $rows = DB::table('absensi_guru')->get()->map(function ($row) {
                return [
                    'id' => $row->id,
                    'guru_id' => $row->guru_id,
                    'pencatat_guru_id' => $row->pencatat_guru_id,
                    'tanggal' => $row->tanggal,
                    'status' => $row->status,
                    'keterangan' => $row->keterangan,
                    'tahun_ajaran_id' => $row->tahun_ajaran_id,
                    'semester_id' => $row->semester_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            })->all();

            Schema::dropIfExists('absensi_guru');
            Schema::create('absensi_guru', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
                $table->foreignId('pencatat_guru_id')->nullable()->constrained('guru')->nullOnDelete();
                $table->date('tanggal');
                $table->enum('status', ['hadir', 'tidak_hadir', 'izin', 'sakit', 'dinas_luar']);
                $table->string('keterangan')->nullable();
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->nullOnDelete();
                $table->foreignId('semester_id')->nullable()->constrained('semester')->nullOnDelete();
                $table->timestamps();
                $table->unique(['guru_id', 'tanggal']);
                $table->index('tanggal');
                $table->index('status');
            });

            foreach ($rows as $row) {
                DB::table('absensi_guru')->insert($row);
            }
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('absensi_guru')) {
            return;
        }

        $driver = DB::getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE absensi_guru MODIFY status ENUM('hadir','tidak_hadir','izin','sakit') NOT NULL");
            return;
        }

        if ($driver === 'sqlite') {
            $rows = DB::table('absensi_guru')->get()->map(function ($row) {
                return [
                    'id' => $row->id,
                    'guru_id' => $row->guru_id,
                    'pencatat_guru_id' => $row->pencatat_guru_id,
                    'tanggal' => $row->tanggal,
                    'status' => $row->status,
                    'keterangan' => $row->keterangan,
                    'tahun_ajaran_id' => $row->tahun_ajaran_id,
                    'semester_id' => $row->semester_id,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ];
            })->all();

            Schema::dropIfExists('absensi_guru');
            Schema::create('absensi_guru', function (Blueprint $table) {
                $table->id();
                $table->foreignId('guru_id')->constrained('guru')->cascadeOnDelete();
                $table->foreignId('pencatat_guru_id')->nullable()->constrained('guru')->nullOnDelete();
                $table->date('tanggal');
                $table->enum('status', ['hadir', 'tidak_hadir', 'izin', 'sakit']);
                $table->string('keterangan')->nullable();
                $table->foreignId('tahun_ajaran_id')->nullable()->constrained('tahun_ajaran')->nullOnDelete();
                $table->foreignId('semester_id')->nullable()->constrained('semester')->nullOnDelete();
                $table->timestamps();
                $table->unique(['guru_id', 'tanggal']);
                $table->index('tanggal');
                $table->index('status');
            });

            foreach ($rows as $row) {
                if ($row['status'] === 'dinas_luar') {
                    $row['status'] = 'tidak_hadir';
                }
                DB::table('absensi_guru')->insert($row);
            }
        }
    }
};
