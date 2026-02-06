<?php

namespace App\Console\Commands;

use App\Models\Guru;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DedupeGuru extends Command
{
    protected $signature = 'guru:dedupe {--dry-run : Show duplicates without applying changes}';

    protected $description = 'Merge duplicate guru records by NIP or name and reassign guru_id references.';

    public function handle(): int
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'mysql') {
            $this->error('This command currently supports MySQL only.');
            return Command::FAILURE;
        }

        $database = DB::connection()->getDatabaseName();
        $tables = DB::select(
            'select table_name from information_schema.columns where table_schema = ? and column_name = ?',
            [$database, 'guru_id']
        );

        $tableNames = array_values(array_unique(array_map(function ($row) {
            return $row->table_name ?? $row->TABLE_NAME ?? null;
        }, $tables)));
        $tableNames = array_values(array_filter($tableNames));

        if (empty($tableNames)) {
            $this->info('No tables with guru_id found.');
            return Command::SUCCESS;
        }

        $gurus = Guru::select('id', 'nama', 'nip', 'email', 'created_at')
            ->orderBy('id')
            ->get();

        $groups = $gurus->groupBy(function ($guru) {
            $nip = trim((string) $guru->nip);
            if ($nip !== '') {
                return 'nip:' . strtolower($nip);
            }
            return 'nama:' . strtolower(trim((string) $guru->nama));
        });

        $duplicates = $groups->filter(function ($group) {
            return $group->count() > 1;
        });

        if ($duplicates->isEmpty()) {
            $this->info('No duplicate guru records found.');
            return Command::SUCCESS;
        }

        $this->info('Duplicate groups: ' . $duplicates->count());

        if ($this->option('dry-run')) {
            $this->reportDuplicates($duplicates, $tableNames);
            return Command::SUCCESS;
        }

        DB::beginTransaction();
        try {
            foreach ($duplicates as $key => $group) {
                $stats = $this->buildStats($group, $tableNames);
                $keep = $stats[0];
                $keepId = $keep['guru']->id;

                $this->line('Keeping guru_id ' . $keepId . ' for group ' . $key . '.');

                foreach ($stats as $index => $entry) {
                    if ($index === 0) {
                        continue;
                    }
                    $dupId = $entry['guru']->id;

                    foreach ($tableNames as $table) {
                        DB::table($table)
                            ->where('guru_id', $dupId)
                            ->update(['guru_id' => $keepId]);
                    }

                    Guru::where('id', $dupId)->delete();
                    $this->line('Merged and deleted guru_id ' . $dupId . '.');
                }
            }

            DB::commit();
            $this->info('Deduplication completed.');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Deduplication failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function reportDuplicates($duplicates, array $tableNames): void
    {
        foreach ($duplicates as $key => $group) {
            $this->line('Group ' . $key . ':');
            $stats = $this->buildStats($group, $tableNames);
            foreach ($stats as $index => $entry) {
                $guru = $entry['guru'];
                $prefix = $index === 0 ? '  * keep' : '  - drop';
                $this->line($prefix . ' id=' . $guru->id . ' nama=' . $guru->nama . ' nip=' . ($guru->nip ?? '-') . ' refs=' . $entry['refCount'] . ' user=' . ($entry['hasUser'] ? 'yes' : 'no'));
            }
        }
    }

    private function buildStats($group, array $tableNames): array
    {
        $stats = [];
        foreach ($group as $guru) {
            $refCount = 0;
            foreach ($tableNames as $table) {
                $refCount += DB::table($table)->where('guru_id', $guru->id)->count();
            }

            $stats[] = [
                'guru' => $guru,
                'refCount' => $refCount,
                'hasUser' => DB::table('users')->where('guru_id', $guru->id)->exists(),
            ];
        }

        usort($stats, function ($a, $b) {
            if ($a['refCount'] !== $b['refCount']) {
                return $b['refCount'] <=> $a['refCount'];
            }
            if ($a['hasUser'] !== $b['hasUser']) {
                return $b['hasUser'] <=> $a['hasUser'];
            }
            return $a['guru']->id <=> $b['guru']->id;
        });

        return $stats;
    }
}
