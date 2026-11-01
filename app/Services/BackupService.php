<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\DB;

class BackupService
{
    protected $disk = 'local';
    protected $dir = 'backups';
    protected ?string $resolvedDirectory = null;

    protected const MAX_PART_SIZE = 50 * 1024 * 1024;

    protected const SAFE_PART_SIZE = 45 * 1024 * 1024;

    protected function getOsFamily(): string
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return 'windows';
        }

        if (PHP_OS_FAMILY === 'Darwin') {
            return 'mac';
        }

        return 'linux';
    }

    protected function resolveMysqldumpPath(): ?string
    {
        $configured = env('DB_DUMP_PATH');
        $candidates = [];

        if (! empty($configured)) {
            $candidates[] = $configured;
        }

        $osFamily = $this->getOsFamily();
        if ($osFamily === 'windows') {
            $candidates[] = 'C:\\Program Files\\MySQL\\MySQL Server\\bin\\mysqldump.exe';
            $candidates[] = 'C:\\wamp64\\bin\\mysql\\mysql8.0.32\\bin\\mysqldump.exe';
            $candidates[] = 'mysqldump.exe';
        } else {
            $candidates[] = '/usr/bin/mysqldump';
            $candidates[] = '/usr/local/bin/mysqldump';
            $candidates[] = 'mysqldump';
        }

        foreach ($candidates as $candidate) {
            if (empty($candidate)) {
                continue;
            }

            if (PHP_OS_FAMILY === 'Windows') {
                if (file_exists($candidate) && is_executable($candidate)) {
                    return $candidate;
                }
            } else {
                if (is_executable($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    protected function createDirectory(string $path): bool
    {
        if (is_dir($path) && is_writable($path)) {
            return true;
        }

        $parent = dirname($path);
        if (! is_dir($parent)) {
            $parentCreated = $this->createDirectory($parent);
            if (! $parentCreated) {
                return false;
            }
        }

        if (! is_dir($parent) || ! is_writable($parent)) {
            return false;
        }

        if (@mkdir($path, 0755, true)) {
            @chmod($path, 0755);
            return is_dir($path) && is_writable($path);
        }

        return is_dir($path) && is_writable($path);
    }

    public function ensureDirectory()
    {
        if ($this->resolvedDirectory && is_dir($this->resolvedDirectory) && is_writable($this->resolvedDirectory)) {
            return $this->resolvedDirectory;
        }

        $candidates = [
            storage_path('app/' . $this->dir),
            public_path('uploads/backup_files'),
            sys_get_temp_dir() . '/absensi_backups',
        ];

        foreach ($candidates as $candidate) {
            if ($this->createDirectory($candidate)) {
                $this->resolvedDirectory = $candidate;
                return $this->resolvedDirectory;
            }
        }

        throw new \RuntimeException('Tidak dapat membuat direktori backup. Periksa izin folder aplikasi.');
    }

    protected function writePartSql(string $backupDir, int $partNumber, string $contents): string
    {
        $partName = sprintf('SIMADIS_PART_%03d.sql', $partNumber);
        $partPath = $backupDir . '/' . $partName;
        file_put_contents($partPath, $contents);

        return $partName;
    }

    protected function sha256File(string $path): string
    {
        if (! is_file($path)) {
            return 'missing';
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return 'unreadable';
        }

        $hash = hash_init('sha256');
        while (! feof($handle)) {
            $chunk = fread($handle, 1024 * 1024);
            if ($chunk !== false) {
                hash_update($hash, $chunk);
            }
        }
        fclose($handle);

        return hash_final($hash);
    }

    protected function buildManifestForSql(string $backupDir, array $partFiles): array
    {
        $manifest = [
            'backup_id' => basename($backupDir),
            'application' => 'SIMADIS',
            'created_at' => gmdate('c'),
            'backup_type' => 'database',
            'total_parts' => count($partFiles),
            'part_size_limit' => self::MAX_PART_SIZE,
            'safe_part_size_limit' => self::SAFE_PART_SIZE,
            'parts' => [],
        ];

        foreach ($partFiles as $fileName) {
            $partPath = $backupDir . '/' . $fileName;
            $manifest['parts'][] = [
                'name' => $fileName,
                'size' => @filesize($partPath) ?: 0,
                'checksum' => $this->sha256File($partPath),
            ];
        }

        return $manifest;
    }

    protected function buildMergedSqlFromBackupDirectory(string $backupDir): string
    {
        $manifestPath = $backupDir . '/manifest.json';
        if (is_file($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true);
            if (is_array($manifest) && ! empty($manifest['parts'] ?? [])) {
                $sql = '';
                foreach ($manifest['parts'] as $partMeta) {
                    $partName = $partMeta['name'] ?? null;
                    if (! $partName) {
                        continue;
                    }

                    $partPath = $backupDir . '/' . $partName;
                    if (! is_file($partPath)) {
                        continue;
                    }

                    $checksum = $partMeta['checksum'] ?? null;
                    if ($checksum && $this->sha256File($partPath) !== $checksum) {
                        throw new \RuntimeException('Checksum backup SQL gagal validasi: ' . $partName);
                    }

                    $sql .= file_get_contents($partPath);
                }

                return $sql;
            }
        }

        $partFiles = glob($backupDir . '/SIMADIS_PART_*.sql') ?: [];
        sort($partFiles, SORT_NATURAL);
        $merged = '';
        foreach ($partFiles as $partFile) {
            $merged .= file_get_contents($partFile);
        }

        return $merged;
    }

    public function createBackup($format = 'sql')
    {
        $baseDir = $this->ensureDirectory();
        $connection = config('database.default');
        $timestamp = date('Ymd_His');
        $backupId = 'SIMADIS_BACKUP_' . $timestamp;
        $backupDir = $baseDir . '/' . $backupId;

        if (! $this->createDirectory($backupDir)) {
            throw new \RuntimeException('Tidak dapat membuat folder backup database.');
        }

        $dbConfig = config("database.connections.{$connection}");

        if (($dbConfig['driver'] ?? null) === 'sqlite') {
            $sqliteFile = 'SIMADIS_DATABASE.sqlite';
            $sqlitePath = $backupDir . '/' . $sqliteFile;
            $sourcePath = $this->resolveSqlitePath($dbConfig['database'] ?? '');

            if (! $this->createSqliteBackup($sourcePath, $sqlitePath)) {
                $this->deleteDirectory($backupDir);
                throw new \RuntimeException('Tidak dapat membuat backup database SQLite yang valid.');
            }

            $manifest = [
                'backup_id' => basename($backupDir),
                'application' => 'SIMADIS',
                'created_at' => gmdate('c'),
                'backup_type' => 'database',
                'database_driver' => 'sqlite',
                'sqlite_file' => $sqliteFile,
                'sqlite_checksum' => $this->sha256File($sqlitePath),
                'parts' => [[
                    'name' => $sqliteFile,
                    'size' => filesize($sqlitePath),
                    'checksum' => $this->sha256File($sqlitePath),
                ]],
            ];
            file_put_contents($backupDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            $filename = "backup_{$timestamp}.sql";
            $fullPath = $baseDir . '/' . $filename;
            $dumpCommand = null;
            if (in_array($dbConfig['driver'] ?? '', ['mysql', 'mysqli'])) {
                $user = escapeshellarg($dbConfig['username'] ?? 'root');
                $pass = isset($dbConfig['password']) ? '-p' . escapeshellarg($dbConfig['password']) : '';
                $host = escapeshellarg($dbConfig['host'] ?? '127.0.0.1');
                $port = isset($dbConfig['port']) ? '--port=' . escapeshellarg($dbConfig['port']) : '';
                $db = escapeshellarg($dbConfig['database'] ?? '');

                $mysqldump = $this->resolveMysqldumpPath();

                if ($mysqldump === null) {
                    file_put_contents($fullPath, "-- mysqldump executable not found\n");
                } else {
                    $mysqldump = escapeshellarg($mysqldump);
                    $dumpCommand = $mysqldump . " --single-transaction --quick --routines -h $host $port -u $user $pass $db > " . escapeshellarg($fullPath);
                }
            }

            if ($dumpCommand) {
                @exec($dumpCommand . ' 2>&1', $output, $returnVar);
                if ($returnVar !== 0) {
                    $msg = "-- failed to run mysqldump\n";
                    $msg .= "-- command: " . $dumpCommand . "\n";
                    $msg .= "-- output: " . implode("\n", array_slice($output, 0, 20)) . "\n";
                    file_put_contents($fullPath, $msg);
                }
            } else {
                file_put_contents($fullPath, "-- unsupported driver for automated dump\n");
            }

            $sqlContents = is_file($fullPath) ? file_get_contents($fullPath) : '';
            @unlink($fullPath);

            $partFiles = [];
            if ($sqlContents === '') {
                $partFiles[] = $this->writePartSql($backupDir, 1, "-- empty backup\n");
            } else {
                $splitSize = strlen($sqlContents);
                $parts = [];
                $offset = 0;
                $partNumber = 1;

                while ($offset < $splitSize) {
                    $current = substr($sqlContents, $offset, self::SAFE_PART_SIZE);
                    if ($current === false || $current === '') {
                        break;
                    }

                    $parts[] = $this->writePartSql($backupDir, $partNumber, $current);
                    $partNumber++;
                    $offset += self::SAFE_PART_SIZE;
                }

                $partFiles = $parts;
            }

            $manifest = $this->buildManifestForSql($backupDir, $partFiles);
            file_put_contents($backupDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        if ($format === 'zip') {
            $zipName = $baseDir . '/' . $backupId . '.zip';
            $zip = new ZipArchive();
            if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $files = glob($backupDir . '/*');
                foreach ($files ?: [] as $file) {
                    if (is_file($file)) {
                        $zip->addFile($file, basename($file));
                    }
                }
                $zip->close();
            }

            $this->deleteDirectory($backupDir);
            return basename($zipName);
        }

        return $backupId;
    }

    public function listBackups()
    {
        $baseDir = $this->ensureDirectory();
        $items = [];

        $directories = glob($baseDir . '/SIMADIS_BACKUP_*', GLOB_ONLYDIR) ?: [];
        foreach ($directories as $dir) {
            $items[] = [
                'name' => basename($dir),
                'path' => $dir,
                'size' => $this->calculateDirectorySize($dir),
                'modified' => filemtime($dir),
            ];
        }

        $files = glob($baseDir . '/*') ?: [];
        foreach ($files as $file) {
            if (is_dir($file)) {
                continue;
            }

            $items[] = [
                'name' => basename($file),
                'path' => $file,
                'size' => @filesize($file) ?: 0,
                'modified' => filemtime($file),
            ];
        }

        usort($items, fn ($a, $b) => ($b['modified'] ?? 0) <=> ($a['modified'] ?? 0));

        return $items;
    }

    protected function calculateDirectorySize(string $dir): int
    {
        $total = 0;
        $items = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($items as $item) {
            if ($item->isFile()) {
                $total += $item->getSize();
            }
        }

        return $total;
    }

    public function downloadPath($name)
    {
        $path = $this->ensureDirectory() . '/' . $name;
        if (is_dir($path)) {
            $manifestPath = $path . '/manifest.json';
            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                if (is_array($manifest['parts'] ?? null)) {
                    foreach ($manifest['parts'] as $partMeta) {
                        $partPath = $path . '/' . ($partMeta['name'] ?? '');
                        if (file_exists($partPath)) {
                            return $partPath;
                        }
                    }
                }
            }

            $parts = glob($path . '/SIMADIS_PART_*.sql') ?: [];
            if (! empty($parts)) {
                sort($parts, SORT_NATURAL);
                return $parts[0];
            }
        }

        return file_exists($path) ? $path : null;
    }

    public function delete($name)
    {
        $path = $this->ensureDirectory() . '/' . $name;
        if (is_dir($path)) {
            $this->deleteDirectory($path);
            return true;
        }

        if (file_exists($path)) {
            return unlink($path);
        }

        return false;
    }

    public function import(string $filePath): bool
    {
        if (! file_exists($filePath)) {
            return false;
        }

        if (is_dir($filePath)) {
            return $this->restoreBackupDirectory($filePath);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        if ($extension === 'json') {
            $dir = dirname($filePath);
            return $this->restoreBackupDirectory($dir);
        }
        if (in_array($extension, ['sqlite', 'db'], true) || $this->hasSqliteHeader($filePath)) {
            return $this->importSqliteFile($filePath);
        }

        if ($extension === 'zip') {
            $extractDir = sys_get_temp_dir() . '/backup_import_' . uniqid();
            if (! mkdir($extractDir, 0755, true) && ! is_dir($extractDir)) {
                return false;
            }

            $zip = new ZipArchive();
            if ($zip->open($filePath) !== true) {
                return false;
            }

            $zip->extractTo($extractDir);
            $zip->close();

            $manifestPath = $extractDir . '/manifest.json';
            if (is_file($manifestPath)) {
                $result = $this->restoreBackupDirectory($extractDir);
                $this->deleteDirectory($extractDir);
                return $result;
            }

            $sqlFiles = glob($extractDir . '/*.sql');
            if (! empty($sqlFiles)) {
                usort($sqlFiles, fn ($a, $b) => filemtime($b) <=> filemtime($a));
                $result = $this->hasSqliteHeader($sqlFiles[0])
                    ? $this->importSqliteFile($sqlFiles[0])
                    : $this->importSqlFile($sqlFiles[0]);
                $this->deleteDirectory($extractDir);
                return $result;
            }

            $sqliteFiles = array_merge(
                glob($extractDir . '/*.sqlite') ?: [],
                glob($extractDir . '/*.db') ?: []
            );
            if (! empty($sqliteFiles)) {
                usort($sqliteFiles, fn ($a, $b) => filemtime($b) <=> filemtime($a));
                $result = $this->importSqliteFile($sqliteFiles[0]);
                $this->deleteDirectory($extractDir);
                return $result;
            }

            $this->deleteDirectory($extractDir);
            return false;
        }

        return $this->importSqlFile($filePath);
    }

    protected function restoreBackupDirectory(string $backupDir): bool
    {
        $manifestPath = $backupDir . '/manifest.json';
        if (! is_file($manifestPath)) {
            $sqlFiles = glob($backupDir . '/*.sql');
            if (empty($sqlFiles)) {
                return false;
            }

            usort($sqlFiles, fn ($a, $b) => filemtime($b) <=> filemtime($a));
            return $this->importSqlFile($sqlFiles[0]);
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (is_array($manifest) && ($manifest['database_driver'] ?? null) === 'sqlite') {
            $sqliteFile = $manifest['sqlite_file'] ?? null;
            if (! is_string($sqliteFile) || basename($sqliteFile) !== $sqliteFile) {
                return false;
            }

            $sqlitePath = $backupDir . '/' . $sqliteFile;
            $checksum = $manifest['sqlite_checksum'] ?? null;
            if (! is_file($sqlitePath) || ! is_string($checksum) || $this->sha256File($sqlitePath) !== $checksum) {
                return false;
            }

            return $this->importSqliteFile($sqlitePath);
        }

        if (! is_array($manifest) || empty($manifest['parts'] ?? [])) {
            return false;
        }

        foreach ($manifest['parts'] as $partMeta) {
            $partName = $partMeta['name'] ?? null;
            if (! $partName) {
                return false;
            }

            $partPath = $backupDir . '/' . $partName;
            if (! is_file($partPath)) {
                return false;
            }

            $checksum = $partMeta['checksum'] ?? null;
            if ($checksum && $this->sha256File($partPath) !== $checksum) {
                return false;
            }
        }

        $mergedSql = $this->buildMergedSqlFromBackupDirectory($backupDir);
        $tempSql = sys_get_temp_dir() . '/simadis_backup_restore_' . uniqid('', true) . '.sql';
        file_put_contents($tempSql, $mergedSql);

        $result = $this->hasSqliteHeader($tempSql)
            ? $this->importSqliteFile($tempSql)
            : $this->importSqlFile($tempSql);
        @unlink($tempSql);

        return $result;
    }

    protected function resolveSqlitePath(string $path): string
    {
        if ($path === '' || $path === ':memory:' || str_starts_with($path, 'file:')) {
            return '';
        }

        if (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('/^[A-Za-z]:[\\\\\/]/', $path)) {
            return $path;
        }

        return database_path($path);
    }

    protected function createSqliteBackup(string $sourcePath, string $destinationPath): bool
    {
        if (! class_exists(\SQLite3::class) || $sourcePath === '' || ! is_file($sourcePath)) {
            return false;
        }

        $source = new \SQLite3($sourcePath, SQLITE3_OPEN_READONLY);
        $destination = new \SQLite3($destinationPath);

        try {
            return $source->backup($destination) && $this->isValidSqliteDatabase($destination);
        } finally {
            $source->close();
            $destination->close();
        }
    }

    protected function importSqliteFile(string $sourcePath): bool
    {
        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");
        if (($dbConfig['driver'] ?? null) !== 'sqlite' || ! class_exists(\SQLite3::class)) {
            return false;
        }

        $databasePath = $dbConfig['database'] ?? '';
        if ($databasePath === '' || $databasePath === ':memory:' || str_starts_with($databasePath, 'file:')) {
            return false;
        }

        if (! $this->isValidSqliteFile($sourcePath)) {
            return false;
        }

        $targetPath = $this->resolveSqlitePath($databasePath);
        $targetDirectory = dirname($targetPath);
        if (! is_dir($targetDirectory) || ! is_writable($targetDirectory) || is_link($targetPath)) {
            return false;
        }

        $stagingPath = $targetPath . '.restore-' . uniqid('', true);
        if (! copy($sourcePath, $stagingPath) || ! $this->isValidSqliteFile($stagingPath)) {
            @unlink($stagingPath);
            return false;
        }

        DB::purge($connection);
        @unlink($targetPath . '-wal');
        @unlink($targetPath . '-shm');

        if (! rename($stagingPath, $targetPath)) {
            @unlink($stagingPath);
            return false;
        }

        return true;
    }

    protected function isValidSqliteFile(string $path): bool
    {
        if (! $this->hasSqliteHeader($path) || ! class_exists(\SQLite3::class)) {
            return false;
        }

        $database = new \SQLite3($path, SQLITE3_OPEN_READONLY);
        try {
            return $this->isValidSqliteDatabase($database);
        } finally {
            $database->close();
        }
    }

    protected function isValidSqliteDatabase(\SQLite3 $database): bool
    {
        return $database->querySingle('PRAGMA integrity_check') === 'ok';
    }

    protected function hasSqliteHeader(string $path): bool
    {
        if (! is_file($path)) {
            return false;
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            return false;
        }

        $header = fread($handle, 16);
        fclose($handle);

        return $header === "SQLite format 3\0";
    }

    protected function importSqlFile(string $sqlPath): bool
    {
        if (! is_file($sqlPath)) {
            return false;
        }

        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");
        $mysql = $this->resolveMysqlClientPath();

        if (! $mysql) {
            return false;
        }

        $defaultsFile = $this->createMysqlDefaultsFile($dbConfig);
        if (! $defaultsFile) {
            return false;
        }

        try {
            $command = $this->buildMysqlImportCommand($mysql, $dbConfig, $sqlPath, $defaultsFile);

            exec($command . ' 2>&1', $output, $returnVar);

            return $returnVar === 0;
        } finally {
            @unlink($defaultsFile);
        }
    }

    protected function createMysqlDefaultsFile(array $dbConfig): ?string
    {
        $defaultsFile = tempnam(sys_get_temp_dir(), 'simadis_mysql_');
        if ($defaultsFile === false) {
            return null;
        }

        $options = [
            'host' => $dbConfig['host'] ?? '127.0.0.1',
            'user' => $dbConfig['username'] ?? 'root',
        ];
        if (isset($dbConfig['port'])) {
            $options['port'] = (string) $dbConfig['port'];
        }
        if (isset($dbConfig['password']) && $dbConfig['password'] !== '') {
            $options['password'] = $dbConfig['password'];
        }

        $contents = "[client]\n";
        foreach ($options as $name => $value) {
            $escapedValue = str_replace(
                ["\\", '"', "\n", "\r"],
                ["\\\\", '\\"', '\\n', '\\r'],
                (string) $value
            );
            $contents .= $name . '="' . $escapedValue . '"' . "\n";
        }

        if (file_put_contents($defaultsFile, $contents) !== strlen($contents) || ! chmod($defaultsFile, 0600)) {
            @unlink($defaultsFile);

            return null;
        }

        return $defaultsFile;
    }

    protected function buildMysqlImportCommand(string $mysql, array $dbConfig, string $sqlPath, string $defaultsFile): string
    {
        return implode(' ', [
            escapeshellarg($mysql),
            escapeshellarg('--defaults-extra-file=' . $defaultsFile),
            escapeshellarg($dbConfig['database'] ?? ''),
            '<',
            escapeshellarg($sqlPath),
        ]);
    }

    protected function resolveMysqlClientPath(): ?string
    {
        $configured = env('DB_CLIENT_PATH');
        $candidates = [];

        if (! empty($configured)) {
            $candidates[] = $configured;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $candidates[] = 'C:\\Program Files\\MySQL\\MySQL Server\\bin\\mysql.exe';
            $candidates[] = 'mysql.exe';
        } else {
            $candidates[] = '/usr/bin/mysql';
            $candidates[] = '/usr/local/bin/mysql';
            $candidates[] = 'mysql';
        }

        foreach ($candidates as $candidate) {
            if (empty($candidate)) {
                continue;
            }

            if (PHP_OS_FAMILY === 'Windows') {
                if (file_exists($candidate) && is_executable($candidate)) {
                    return $candidate;
                }
            } else {
                if (is_executable($candidate)) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    protected function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = array_diff(scandir($dir), ['.', '..']);
        foreach ($items as $item) {
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
