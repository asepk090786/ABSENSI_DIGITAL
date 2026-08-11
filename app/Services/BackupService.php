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

    public function createBackup($format = 'sql')
    {
        $baseDir = $this->ensureDirectory();
        $connection = config('database.default');
        $timestamp = date('Ymd_His');
        $filename = "backup_{$timestamp}.sql";
        $fullPath = $baseDir . '/' . $filename;

        $dbConfig = config("database.connections.{$connection}");

        if ($connection === 'sqlite') {
            $dbPath = database_path('database.sqlite');
            if (file_exists($dbPath)) {
                copy($dbPath, $fullPath);
            } else {
                // copy configured path
                $src = $dbConfig['database'] ?? database_path('database.sqlite');
                if (file_exists($src)) copy($src, $fullPath);
            }
        } else {
            // Try to use mysqldump for MySQL/MariaDB
            $dumpCommand = null;
            if (in_array($dbConfig['driver'] ?? '', ['mysql','mysqli'])) {
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
                    $msg .= "-- output: " . implode("\n", array_slice($output,0,20)) . "\n";
                    file_put_contents($fullPath, $msg);
                }
            } else {
                file_put_contents($fullPath, "-- unsupported driver for automated dump\n");
            }
        }

        if ($format === 'zip') {
            $zipName = $baseDir . '/backup_' . $timestamp . '.zip';
            $zip = new ZipArchive();
            if ($zip->open($zipName, ZipArchive::CREATE) === true) {
                $zip->addFile($fullPath, basename($fullPath));
                $zip->close();
                // remove sql file
                @unlink($fullPath);
                return basename($zipName);
            }
            return basename($fullPath);
        }

        return basename($fullPath);
    }

    public function listBackups()
    {
        $baseDir = $this->ensureDirectory();
        $files = glob($baseDir . '/*') ?: [];
        rsort($files);
        return array_map(function($f){
            return [
                'name' => basename($f),
                'path' => $f,
                'size' => filesize($f),
                'modified' => filemtime($f),
            ];
        }, $files);
    }

    public function downloadPath($name)
    {
        $path = $this->ensureDirectory() . '/' . $name;
        return file_exists($path) ? $path : null;
    }

    public function delete($name)
    {
        $path = $this->ensureDirectory() . '/' . $name;
        if (file_exists($path)) return unlink($path);
        return false;
    }

    public function import(string $filePath): bool
    {
        if (! file_exists($filePath)) {
            return false;
        }

        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");
        $host = escapeshellarg($dbConfig['host'] ?? '127.0.0.1');
        $port = isset($dbConfig['port']) ? ' -P' . escapeshellarg((string) $dbConfig['port']) : '';
        $user = escapeshellarg($dbConfig['username'] ?? 'root');
        $pass = isset($dbConfig['password']) ? '-p' . escapeshellarg($dbConfig['password']) : '';
        $db = escapeshellarg($dbConfig['database'] ?? '');
        $mysql = $this->resolveMysqlClientPath();

        if (! $mysql) {
            return false;
        }

        $sqlPath = $filePath;
        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'zip') {
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

            $sqlFiles = glob($extractDir . '/*.sql');
            if (! $sqlFiles) {
                return false;
            }

            usort($sqlFiles, function ($a, $b) {
                return filemtime($b) <=> filemtime($a);
            });

            $sqlPath = $sqlFiles[0];
        }

        $command = sprintf(
            '%s -h %s%s -u %s %s %s < %s',
            escapeshellcmd($mysql),
            trim($host, "'"),
            $port,
            trim($user, "'"),
            $pass ? ' -p' . trim($pass, "'") : '',
            trim($db, "'"),
            escapeshellarg($sqlPath)
        );

        exec($command . ' 2>&1', $output, $returnVar);

        if (strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) === 'zip' && is_dir($extractDir)) {
            $this->deleteDirectory($extractDir);
        }

        return $returnVar === 0;
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
