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
        if (!Schema::hasTable('ekstrakurikuler')) return;

        // Rename nama_ekskul → nama if needed
        if (Schema::hasColumn('ekstrakurikuler', 'nama_ekskul') && !Schema::hasColumn('ekstrakurikuler', 'nama')) {
            DB::statement('ALTER TABLE ekstrakurikuler CHANGE nama_ekskul nama VARCHAR(150) NOT NULL');
        }

        // Rename pembina_id → guru_id if needed
        if (Schema::hasColumn('ekstrakurikuler', 'pembina_id') && !Schema::hasColumn('ekstrakurikuler', 'guru_id')) {
            DB::statement('ALTER TABLE ekstrakurikuler CHANGE pembina_id guru_id BIGINT UNSIGNED NULL');
        }

        // Add new columns if missing
        if (!Schema::hasColumn('ekstrakurikuler', 'lokasi')) {
            Schema::table('ekstrakurikuler', function (Blueprint $table) {
                $table->string('lokasi', 200)->nullable()->after('deskripsi');
            });
        }
        if (!Schema::hasColumn('ekstrakurikuler', 'logo')) {
            Schema::table('ekstrakurikuler', function (Blueprint $table) {
                $table->string('logo', 255)->nullable()->after('lokasi');
            });
        }
        if (!Schema::hasColumn('ekstrakurikuler', 'kuota_max')) {
            Schema::table('ekstrakurikuler', function (Blueprint $table) {
                $table->integer('kuota_max')->nullable()->after('logo');
            });
        }
        if (!Schema::hasColumn('ekstrakurikuler', 'status')) {
            Schema::table('ekstrakurikuler', function (Blueprint $table) {
                $table->enum('status', ['aktif', 'nonaktif'])->default('aktif')->after('kuota_max');
            });
        }

        // Add foreign key for guru_id if column was renamed and constraint doesn't exist
        if (Schema::hasColumn('ekstrakurikuler', 'guru_id') && !DB::select("SELECT 1 FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA = DATABASE() AND TABLE_NAME = 'ekstrakurikuler' AND CONSTRAINT_NAME = 'ekstrakurikuler_guru_id_foreign' LIMIT 1")) {
            try {
                Schema::table('ekstrakurikuler', function (Blueprint $table) {
                    $table->foreign('guru_id')->references('id')->on('guru')->nullOnDelete();
                });
            } catch (\Throwable $e) {
                // ignore if FK fails
            }
        }
    }

    public function down(): void
    {
        // No rollback for column renames
    }
};
