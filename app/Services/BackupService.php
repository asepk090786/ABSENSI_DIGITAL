<?php
namespace App\Services;

use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\DB;

class BackupService
{
    protected $disk = 'local';
    protected $dir = 'backups';

    public function ensureDirectory()
    {
        $path = storage_path('app/' . $this->dir);
        if (! is_dir($path)) mkdir($path, 0755, true);
    }

    public function createBackup($format = 'sql')
    {
        $this->ensureDirectory();
        $connection = config('database.default');
        $timestamp = date('Ymd_His');
        $filename = "backup_{$timestamp}.sql";
        $fullPath = storage_path('app/' . $this->dir . '/' . $filename);

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

                // Allow custom mysqldump path via env DB_DUMP_PATH
                $mysqldump = env('DB_DUMP_PATH') ?: 'mysqldump';
                $mysqldump = escapeshellarg($mysqldump);

                $dumpCommand = $mysqldump . " --single-transaction --quick --routines -h $host $port -u $user $pass $db > " . escapeshellarg($fullPath);
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
            $zipName = storage_path('app/' . $this->dir . '/backup_' . $timestamp . '.zip');
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
        $this->ensureDirectory();
        $files = glob(storage_path('app/' . $this->dir . '/*')) ?: [];
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
        $path = storage_path('app/' . $this->dir . '/' . $name);
        return file_exists($path) ? $path : null;
    }

    public function delete($name)
    {
        $path = storage_path('app/' . $this->dir . '/' . $name);
        if (file_exists($path)) return unlink($path);
        return false;
    }
}
