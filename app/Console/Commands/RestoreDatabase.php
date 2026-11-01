<?php

namespace App\Console\Commands;

use App\Models\CbtSsoTicket;
use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class RestoreDatabase extends Command
{
    protected $signature = 'db:restore {--file=} {--latest}';

    protected $description = 'Restore database from an existing backup file or latest backup directory';

    public function handle(BackupService $svc): int
    {
        $file = $this->option('file');
        if ($this->option('latest')) {
            $backups = $svc->listBackups();
            $latest = $backups[0]['path'] ?? null;

            if (! $latest) {
                $this->error('Tidak ada backup yang tersedia untuk restore.');

                return 1;
            }

            $file = $latest;
        }

        if (empty($file)) {
            $this->error('Pilih file backup dengan --file=/path/to/backup atau gunakan --latest.');

            return 1;
        }

        if (! file_exists($file)) {
            $this->error('File backup tidak ditemukan: ' . $file);

            return 1;
        }

        $this->info('Memulai restore database dari: ' . $file);

        $ok = $svc->import($file);

        if ($ok) {
            if (Schema::hasTable('cbt_sso_tickets')) {
                CbtSsoTicket::query()->delete();
            }

            $this->info('Restore database selesai.');

            return 0;
        }

        $this->error('Restore database gagal. Periksa konfigurasi MySQL dan file backup.');

        return 1;
    }
}
