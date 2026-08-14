<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class ProfilePhotoBackupService
{
    protected string $dir = 'backups/profile_photos';

    protected ?string $resolvedDirectory = null;

    protected const MAX_PART_SIZE = 50 * 1024 * 1024;

    protected const SAFE_PART_SIZE = 45 * 1024 * 1024;

    protected function ensureDirectory(): string
    {
        if ($this->resolvedDirectory && is_dir($this->resolvedDirectory) && is_writable($this->resolvedDirectory)) {
            return $this->resolvedDirectory;
        }

        $candidates = [
            storage_path('app/' . $this->dir),
            public_path('uploads/profile_backups'),
            sys_get_temp_dir() . '/absensi_profile_photo_backups',
        ];

        foreach ($candidates as $candidate) {
            if ($this->createDirectory($candidate)) {
                $this->resolvedDirectory = $candidate;
                return $this->resolvedDirectory;
            }
        }

        throw new \RuntimeException('Tidak dapat membuat direktori backup foto profil. Periksa izin folder penyimpanan aplikasi.');
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

    public function export(): string
    {
        $baseDir = $this->ensureDirectory();
        $backupId = 'SIMADIS_BACKUP_' . gmdate('Y-m-d_H-i-s');
        $backupDir = $baseDir . '/' . $backupId;

        if (! @mkdir($backupDir, 0755, true) && ! is_dir($backupDir)) {
            throw new \RuntimeException('Tidak dapat membuat folder backup foto profil.');
        }

        $entries = [];
        $users = User::whereNotNull('foto')->where('foto', '!=', '')->get();

        foreach ($users as $user) {
            $sourcePath = Storage::disk('public')->path($user->foto);
            if (! file_exists($sourcePath)) {
                continue;
            }

            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg';
            $safeName = 'user_' . ($user->id ?? '0') . '_' . Str::slug($user->name ?: $user->username ?: 'user') . '.' . $extension;
            $entries[] = [
                'source' => $sourcePath,
                'name' => $safeName,
                'user_id' => $user->id,
                'name_raw' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'foto' => $user->foto,
            ];
        }

        $manifest = [
            'backup_id' => $backupId,
            'application' => 'SIMADIS',
            'created_at' => gmdate('c'),
            'backup_type' => 'profile_photos',
            'total_size' => 0,
            'total_parts' => 0,
            'max_part_size' => self::MAX_PART_SIZE,
            'parts' => [],
        ];

        $partNumber = 1;
        $currentPartEntries = [];
        $currentPartSize = 0;

        foreach ($entries as $entry) {
            $entrySize = @filesize($entry['source']) ?: 0;
            if ($entrySize <= 0) {
                continue;
            }

            if (! empty($currentPartEntries) && ($currentPartSize + $entrySize) > self::SAFE_PART_SIZE) {
                $partName = $this->writePartArchive($backupDir, $partNumber, $currentPartEntries);
                $manifest['parts'][] = [
                    'name' => $partName,
                    'size' => $currentPartSize,
                    'checksum' => $this->sha256File($backupDir . '/' . $partName),
                ];
                $partNumber++;
                $currentPartEntries = [];
                $currentPartSize = 0;
            }

            $currentPartEntries[] = $entry;
            $currentPartSize += $entrySize;
        }

        if (! empty($currentPartEntries)) {
            $partName = $this->writePartArchive($backupDir, $partNumber, $currentPartEntries);
            $manifest['parts'][] = [
                'name' => $partName,
                'size' => $currentPartSize,
                'checksum' => $this->sha256File($backupDir . '/' . $partName),
            ];
        }

        $manifest['total_parts'] = count($manifest['parts']);
        $manifest['total_size'] = array_sum(array_map(fn ($part) => (int) ($part['size'] ?? 0), $manifest['parts']));

        file_put_contents($backupDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $backupId;
    }

    protected function writePartArchive(string $backupDir, int $partNumber, array $entries): string
    {
        $partName = 'SIMADIS_PART_' . sprintf('%03d', $partNumber) . '.zip';
        $partPath = $backupDir . '/' . $partName;

        $zip = new ZipArchive();
        if ($zip->open($partPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat part backup foto profil: ' . $partName);
        }

        foreach ($entries as $entry) {
            $zip->addFile($entry['source'], $entry['name']);
        }

        $zip->close();

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

    public function listBackups(): array
    {
        $baseDir = $this->ensureDirectory();
        $items = [];

        $directories = glob($baseDir . '/SIMADIS_BACKUP_*', GLOB_ONLYDIR) ?: [];
        foreach ($directories as $dir) {
            $manifestPath = $dir . '/manifest.json';
            $manifest = is_file($manifestPath) ? json_decode(file_get_contents($manifestPath), true) : null;
            $parts = glob($dir . '/SIMADIS_PART_*.zip') ?: [];
            $partCount = count($parts);
            $totalSize = 0;
            foreach ($parts as $part) {
                $totalSize += @filesize($part) ?: 0;
            }

            $items[] = [
                'name' => basename($dir),
                'path' => $dir,
                'size' => $totalSize,
                'modified' => filemtime($dir),
                'parts' => $partCount,
                'manifest' => $manifest,
            ];
        }

        $files = glob($baseDir . '/*.zip') ?: [];
        foreach ($files as $file) {
            if (str_contains(basename($file), 'SIMADIS_PART_')) {
                continue;
            }
            $items[] = [
                'name' => basename($file),
                'path' => $file,
                'size' => @filesize($file) ?: 0,
                'modified' => filemtime($file),
                'parts' => 1,
                'manifest' => null,
            ];
        }

        usort($items, fn ($a, $b) => ($b['modified'] ?? 0) <=> ($a['modified'] ?? 0));

        return $items;
    }

    public function downloadPath(string $name): ?string
    {
        $baseDir = $this->ensureDirectory();
        $path = $baseDir . '/' . $name;

        if (is_dir($path)) {
            $parts = glob($path . '/SIMADIS_PART_*.zip') ?: [];
            if (! empty($parts)) {
                sort($parts, SORT_NATURAL);
                return $parts[0];
            }

            $zipFiles = glob($path . '/*.zip') ?: [];
            if (! empty($zipFiles)) {
                sort($zipFiles, SORT_NATURAL);
                return $zipFiles[0];
            }

            $manifest = $path . '/manifest.json';
            if (file_exists($manifest)) {
                return $manifest;
            }

            return null;
        }

        if (file_exists($path)) {
            return $path;
        }

        return null;
    }

    public function delete(string $name): bool
    {
        $path = $this->ensureDirectory() . '/' . $name;
        if (is_dir($path)) {
            $this->deleteDirectory($path);
            return true;
        }

        return file_exists($path) ? unlink($path) : false;
    }

    public function import(string $archivePath): bool
    {
        if (! file_exists($archivePath)) {
            return false;
        }

        if (is_dir($archivePath)) {
            return $this->restoreBackupDirectory($archivePath);
        }

        $pathInfo = pathinfo($archivePath);
        $ext = strtolower($pathInfo['extension'] ?? '');

        if ($ext === 'json' && is_file($archivePath)) {
            $dir = dirname($archivePath);
            return $this->restoreBackupDirectory($dir);
        }

        if ($ext === 'zip') {
            $extractDir = sys_get_temp_dir() . '/backup_import_' . uniqid();
            if (! mkdir($extractDir, 0755, true) && ! is_dir($extractDir)) {
                return false;
            }

            $zip = new ZipArchive();
            if ($zip->open($archivePath) !== true) {
                $this->deleteDirectory($extractDir);
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

            $result = $this->importLegacyZipArchive($extractDir);
            $this->deleteDirectory($extractDir);
            return $result;
        }

        return false;
    }

    protected function restoreBackupDirectory(string $backupDir): bool
    {
        $manifestPath = $backupDir . '/manifest.json';
        if (! is_file($manifestPath)) {
            return $this->importLegacyZipArchive($backupDir);
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        if (! is_array($manifest) || empty($manifest['parts'] ?? [])) {
            return false;
        }

        $parts = $manifest['parts'];
        foreach ($parts as $partMeta) {
            $partName = $partMeta['name'] ?? null;
            if (! $partName) {
                return false;
            }

            $partPath = $backupDir . '/' . $partName;
            if (! is_file($partPath)) {
                return false;
            }

            $expectedChecksum = $partMeta['checksum'] ?? null;
            if ($expectedChecksum && $this->sha256File($partPath) !== $expectedChecksum) {
                return false;
            }
        }

        $importedCount = 0;
        foreach ($parts as $partMeta) {
            $partName = $partMeta['name'] ?? null;
            if (! $partName) {
                continue;
            }

            $partPath = $backupDir . '/' . $partName;
            if (! is_file($partPath)) {
                continue;
            }

            $zip = new ZipArchive();
            if ($zip->open($partPath) !== true) {
                $zip->close();
                continue;
            }

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $entryName = $zip->getNameIndex($i);
                if ($entryName === false || substr((string) $entryName, -1) === '/') {
                    continue;
                }

                $user = $this->findUserForEntry(['file' => $entryName]);
                if (! $user) {
                    continue;
                }

                $tmpTarget = storage_path('app/temp/profile_photo_restore_' . uniqid() . '_' . basename($entryName));
                if (! $this->copyZipEntryToDisk($partPath, $entryName, $tmpTarget)) {
                    continue;
                }

                $imageInfo = @getimagesize($tmpTarget);
                if ($imageInfo === false) {
                    @unlink($tmpTarget);
                    continue;
                }

                if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                    Storage::disk('public')->delete($user->foto);
                }

                $storedPath = 'user_photos/' . basename($entryName);
                $success = Storage::disk('public')->put($storedPath, file_get_contents($tmpTarget));
                @unlink($tmpTarget);

                if (! $success) {
                    continue;
                }

                $user->foto = $storedPath;
                $user->save();
                $importedCount++;
            }

            $zip->close();
        }

        return $importedCount > 0;
    }

    protected function importLegacyZipArchive(string $extractDir): bool
    {
        $entries = [];
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        $files = glob($extractDir . '/*');
        foreach ($files ?: [] as $file) {
            if (! is_file($file)) {
                continue;
            }

            $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
            if (! in_array($ext, $extensions, true)) {
                continue;
            }

            $entries[] = [
                'file' => basename($file),
                'user_id' => $this->extractUserIdFromFilename(basename($file)),
            ];
        }

        $importedCount = 0;
        foreach ($entries as $entry) {
            $user = $this->findUserForEntry($entry);
            if (! $user) {
                continue;
            }

            $sourcePath = $extractDir . '/' . $entry['file'];
            $imageInfo = @getimagesize($sourcePath);
            if ($imageInfo === false) {
                continue;
            }

            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }

            $storedPath = 'user_photos/' . basename($sourcePath);
            $success = Storage::disk('public')->put($storedPath, fopen($sourcePath, 'rb'));
            if (! $success) {
                continue;
            }

            $user->foto = $storedPath;
            $user->save();
            $importedCount++;
        }

        return $importedCount > 0;
    }

    protected function copyZipEntryToDisk(string $zipPath, string $entryName, string $targetPath): bool
    {
        $stream = fopen('zip://' . $zipPath . '#' . $entryName, 'rb');
        if ($stream === false) {
            return false;
        }

        $output = fopen($targetPath, 'wb');
        if ($output === false) {
            fclose($stream);
            return false;
        }

        $copied = stream_copy_to_stream($stream, $output);
        fclose($stream);
        fclose($output);

        return $copied !== false;
    }

    protected function resolveZipEntries(ZipArchive $zip): array
    {
        $entries = [];

        $manifestStream = $zip->getStream('manifest.json');
        if ($manifestStream) {
            $manifestContents = stream_get_contents($manifestStream);
            fclose($manifestStream);
            $manifest = json_decode($manifestContents, true);
            if (is_array($manifest)) {
                foreach ($manifest as $entry) {
                    if (! empty($entry['file'])) {
                        $entries[] = [
                            'file' => ltrim((string) $entry['file'], '/'),
                            'user_id' => $entry['user_id'] ?? null,
                            'username' => $entry['username'] ?? null,
                            'email' => $entry['email'] ?? null,
                        ];
                    }
                }
            }
        }

        if (empty($entries)) {
            $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $stat = $zip->statIndex($i);
                if (! $stat || substr($stat['name'], -1) === '/') {
                    continue;
                }

                $name = ltrim((string) $stat['name'], '/');
                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (! in_array($ext, $extensions, true)) {
                    continue;
                }

                $entries[] = [
                    'file' => $name,
                    'user_id' => $this->extractUserIdFromFilename($name),
                ];
            }
        }

        return array_values(array_filter($entries, fn (array $entry) => ! empty($entry['file'])));
    }

    protected function findUserForEntry(array $entry): ?User
    {
        if (! empty($entry['user_id'])) {
            $user = User::find($entry['user_id']);
            if ($user) {
                return $user;
            }
        }

        if (! empty($entry['username'])) {
            $user = User::where('username', $entry['username'])->first();
            if ($user) {
                return $user;
            }
        }

        if (! empty($entry['email'])) {
            $user = User::where('email', $entry['email'])->first();
            if ($user) {
                return $user;
            }
        }

        if (! empty($entry['file'])) {
            $userId = $this->extractUserIdFromFilename($entry['file']);
            if ($userId) {
                return User::find($userId);
            }
        }

        return null;
    }

    protected function extractUserIdFromFilename(string $filename): ?int
    {
        $basename = basename(str_replace('\\', '/', $filename));

        $patterns = [
            '/^user[_ -](\d+)(?:[_ -].*)?$/i',
            '/^(?:profile|photo|foto|avatar)[_ -](\d+)(?:[_ -].*)?$/i',
            '/^(?:.*?)[_ -](\d+)(?:\.[a-z0-9]+)?$/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $basename, $matches)) {
                $id = (int) $matches[1];
                if ($id > 0) {
                    return $id;
                }
            }
        }

        if (preg_match('/^(\d+)(?:\.[a-z0-9]+)?$/i', $basename, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    protected function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $fullPath = $path . '/' . $file;
            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
            } else {
                unlink($fullPath);
            }
        }

        rmdir($path);
    }
}
