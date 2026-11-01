<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private string $table = 'ekskul_sertifikats';

    public function up(): void
    {
        if (!Schema::hasColumn($this->table, 'ekskul_agenda_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->foreignId('ekskul_agenda_id')
                    ->nullable()
                    ->after('ekstrakurikuler_id')
                    ->constrained('ekskul_agenda')
                    ->cascadeOnDelete();
            });
        }

        $duplicates = DB::table($this->table)
            ->select('ekskul_agenda_id', 'siswa_id')
            ->whereNotNull('ekskul_agenda_id')
            ->groupBy('ekskul_agenda_id', 'siswa_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicates) {
            throw new RuntimeException('Duplicate certificates exist for the same agenda and student.');
        }

        if (!$this->hasIndex(['ekstrakurikuler_id'])) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->index('ekstrakurikuler_id', 'ekskul_sertifikats_ekskul_id_index');
            });
        }

        foreach ($this->indexes() as $name => $index) {
            if ($index['unique'] && $index['columns'] === ['ekstrakurikuler_id', 'siswa_id']) {
                Schema::table($this->table, function (Blueprint $table) use ($name) {
                    $table->dropUnique($name);
                });
            }
        }

        if (!$this->hasIndex(['ekskul_agenda_id', 'siswa_id'], true)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->unique(['ekskul_agenda_id', 'siswa_id'], 'ekskul_sertifikats_agenda_siswa_unique');
            });
        }
    }

    public function down(): void
    {
        if (DB::table($this->table)->whereNotNull('ekskul_agenda_id')->exists()) {
            throw new RuntimeException('Cannot roll back agenda certificates without discarding their agenda links.');
        }

        $duplicates = DB::table($this->table)
            ->select('ekstrakurikuler_id', 'siswa_id')
            ->groupBy('ekstrakurikuler_id', 'siswa_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($duplicates) {
            throw new RuntimeException('Cannot restore the legacy unique key while multiple certificates exist per student and extracurricular.');
        }

        foreach ($this->indexes() as $name => $index) {
            if ($index['unique'] && $index['columns'] === ['ekskul_agenda_id', 'siswa_id']) {
                Schema::table($this->table, function (Blueprint $table) use ($name) {
                    $table->dropUnique($name);
                });
            }
        }

        if (!$this->hasIndex(['ekstrakurikuler_id', 'siswa_id'], true)) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->unique(['ekstrakurikuler_id', 'siswa_id']);
            });
        }

        if (Schema::hasColumn($this->table, 'ekskul_agenda_id')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropForeign('ekskul_sertifikats_ekskul_agenda_id_foreign');
                $table->dropColumn('ekskul_agenda_id');
            });
        }
    }

    private function hasIndex(array $columns, bool $unique = false): bool
    {
        foreach ($this->indexes() as $index) {
            if ($index['columns'] === $columns && (!$unique || $index['unique'])) {
                return true;
            }
        }

        return false;
    }

    private function indexes(): array
    {
        $indexes = [];

        if (DB::getDriverName() === 'mysql') {
            foreach (DB::select('SHOW INDEX FROM `' . $this->table . '`') as $row) {
                $indexes[$row->Key_name]['columns'][(int) $row->Seq_in_index] = $row->Column_name;
                $indexes[$row->Key_name]['unique'] = !(bool) $row->Non_unique;
            }
        } elseif (DB::getDriverName() === 'sqlite') {
            foreach (DB::select("PRAGMA index_list('" . $this->table . "')") as $row) {
                $columns = DB::select('PRAGMA index_info("' . str_replace('"', '""', $row->name) . '")');
                $indexes[$row->name] = [
                    'columns' => array_map(fn ($column) => $column->name, $columns),
                    'unique' => (bool) $row->unique,
                ];
            }
        } else {
            throw new RuntimeException('Unsupported database driver for certificate index migration.');
        }

        foreach ($indexes as &$index) {
            ksort($index['columns']);
            $index['columns'] = array_values($index['columns']);
        }

        return $indexes;
    }
};