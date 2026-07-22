<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackupService;

class BackupDatabase extends Command
{
    protected $signature = 'db:backup {--format=sql}';
    protected $description = 'Create a database backup (sql or zip)';

    public function handle()
    {
        $format = $this->option('format') === 'zip' ? 'zip' : 'sql';
        $svc = new BackupService();
        $name = $svc->createBackup($format);
        $this->info('Backup created: ' . $name);
        return 0;
    }
}
