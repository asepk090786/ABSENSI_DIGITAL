<?php

namespace Tests\Feature;

use App\Services\BackupService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BackupServiceRestoreTest extends TestCase
{
    public function test_imports_backup_parts_in_manifest_order_after_checksum_validation(): void
    {
        $backupDir = $this->createBackupDirectory();
        $firstPart = 'CREATE TABLE users (id INT);';
        $secondPart = 'INSERT INTO users VALUES (1);';
        file_put_contents($backupDir . '/part-001.sql', $firstPart);
        file_put_contents($backupDir . '/part-002.sql', $secondPart);
        file_put_contents($backupDir . '/manifest.json', json_encode([
            'parts' => [
                ['name' => 'part-002.sql', 'checksum' => hash('sha256', $secondPart)],
                ['name' => 'part-001.sql', 'checksum' => hash('sha256', $firstPart)],
            ],
        ]));

        $service = new TestableBackupService();

        try {
            $this->assertTrue($service->import($backupDir));
            $this->assertSame($secondPart . $firstPart, $service->importedSql);
        } finally {
            $this->deleteDirectory($backupDir);
        }
    }

    public function test_rejects_backup_with_invalid_part_checksum_before_import(): void
    {
        $backupDir = $this->createBackupDirectory();
        file_put_contents($backupDir . '/part-001.sql', 'ALTER TABLE users ADD name VARCHAR(255);');
        file_put_contents($backupDir . '/manifest.json', json_encode([
            'parts' => [
                ['name' => 'part-001.sql', 'checksum' => hash('sha256', 'different SQL')],
            ],
        ]));

        $service = new TestableBackupService();

        try {
            $this->assertFalse($service->import($backupDir));
            $this->assertNull($service->importedSql);
        } finally {
            $this->deleteDirectory($backupDir);
        }
    }

    public function test_mysql_password_is_stored_in_private_defaults_file_not_command_arguments(): void
    {
        $service = new TestableBackupService();
        $password = "p'ass;word";
        $databaseConfig = [
            'host' => 'db host',
            'port' => 3307,
            'username' => 'restore user',
            'password' => $password,
            'database' => 'simadis test',
        ];

        $defaultsFile = $service->mysqlDefaultsFile($databaseConfig);
        $this->assertNotFalse($defaultsFile);

        try {
            $command = $service->mysqlImportCommand(
                '/tmp/mysql client',
                $databaseConfig,
                '/tmp/backup file.sql',
                $defaultsFile
            );

            $this->assertStringNotContainsString($password, $command);
            $this->assertStringNotContainsString('--password=', $command);
            $this->assertStringContainsString(escapeshellarg('--defaults-extra-file=' . $defaultsFile), $command);
            $this->assertSame(0600, fileperms($defaultsFile) & 0777);
            $this->assertStringContainsString('password="p\'ass;word"', file_get_contents($defaultsFile));
        } finally {
            unlink($defaultsFile);
        }
    }

    public function test_creates_and_restores_sqlite_backup_in_isolated_files(): void
    {
        $root = $this->createBackupDirectory();
        $sourcePath = $root . '/source.sqlite';
        $targetPath = $root . '/target.sqlite';
        $this->createSqliteDatabase($sourcePath, 'source-data');
        $this->createSqliteDatabase($targetPath, 'target-data');
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => $sourcePath,
        ]);
        DB::purge('sqlite');

        $service = new TestableBackupService();
        $service->backupDirectory = $root . '/backups';

        try {
            $backupName = $service->createBackup('zip');
            $backupPath = $service->backupDirectory . '/' . $backupName;

            $this->assertFileExists($backupPath);
            config(['database.connections.sqlite.database' => $targetPath]);
            $this->assertTrue($service->import($backupPath));

            $restored = new \SQLite3($targetPath, SQLITE3_OPEN_READONLY);
            try {
                $this->assertSame('source-data', $restored->querySingle('SELECT value FROM backup_test'));
            } finally {
                $restored->close();
            }
        } finally {
            DB::purge('sqlite');
            $this->deleteDirectory($root);
        }
    }

    public function test_rejects_sqlite_backup_with_invalid_manifest_checksum(): void
    {
        $root = $this->createBackupDirectory();
        $sourcePath = $root . '/source.sqlite';
        $targetPath = $root . '/target.sqlite';
        $this->createSqliteDatabase($sourcePath, 'backup-data');
        $this->createSqliteDatabase($targetPath, 'target-data');
        $backupPath = $root . '/backup';
        mkdir($backupPath, 0700, true);
        copy($sourcePath, $backupPath . '/SIMADIS_DATABASE.sqlite');
        file_put_contents($backupPath . '/manifest.json', json_encode([
            'database_driver' => 'sqlite',
            'sqlite_file' => 'SIMADIS_DATABASE.sqlite',
            'sqlite_checksum' => hash('sha256', 'incorrect checksum'),
        ]));
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.driver' => 'sqlite',
            'database.connections.sqlite.database' => $targetPath,
        ]);
        DB::purge('sqlite');

        $service = new TestableBackupService();

        try {
            $this->assertFalse($service->import($backupPath));

            $target = new \SQLite3($targetPath, SQLITE3_OPEN_READONLY);
            try {
                $this->assertSame('target-data', $target->querySingle('SELECT value FROM backup_test'));
            } finally {
                $target->close();
            }
        } finally {
            DB::purge('sqlite');
            $this->deleteDirectory($root);
        }
    }

    private function createSqliteDatabase(string $path, string $value): void
    {
        $database = new \SQLite3($path);
        try {
            $database->exec('CREATE TABLE backup_test (value TEXT)');
            $statement = $database->prepare('INSERT INTO backup_test (value) VALUES (:value)');
            $statement->bindValue(':value', $value, SQLITE3_TEXT);
            $statement->execute()->finalize();
        } finally {
            $database->close();
        }
    }

    private function createBackupDirectory(): string
    {
        $backupDir = sys_get_temp_dir() . '/simadis_backup_test_' . uniqid('', true);
        mkdir($backupDir, 0700, true);

        return $backupDir;
    }

    private function deleteDirectory(string $directory): void
    {
        foreach (array_diff(scandir($directory), ['.', '..']) as $item) {
            $path = $directory . DIRECTORY_SEPARATOR . $item;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }

        rmdir($directory);
    }
}

class TestableBackupService extends BackupService
{
    public ?string $importedSql = null;
    public string $backupDirectory;

    public function ensureDirectory(): string
    {
        if (! is_dir($this->backupDirectory)) {
            mkdir($this->backupDirectory, 0700, true);
        }

        return $this->backupDirectory;
    }

    public function mysqlDefaultsFile(array $config): ?string
    {
        return $this->createMysqlDefaultsFile($config);
    }

    public function mysqlImportCommand(string $mysql, array $config, string $sqlPath, string $defaultsFile): string
    {
        return $this->buildMysqlImportCommand($mysql, $config, $sqlPath, $defaultsFile);
    }

    protected function importSqlFile(string $sqlPath): bool
    {
        $this->importedSql = file_get_contents($sqlPath);

        return true;
    }
}
