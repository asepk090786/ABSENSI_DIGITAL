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
        $timestamp = date('Ymd_His');
        $archiveName = 'profile_photos_' . $timestamp . '.zip';
        $archivePath = $baseDir . '/' . $archiveName;
        $tempDir = $baseDir . '/tmp_' . $timestamp;

        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $manifest = [];
        $users = User::whereNotNull('foto')->where('foto', '!=', '')->get();

        foreach ($users as $user) {
            $sourcePath = Storage::disk('public')->path($user->foto);
            if (! file_exists($sourcePath)) {
                continue;
            }

            $extension = pathinfo($sourcePath, PATHINFO_EXTENSION) ?: 'jpg';
            $safeName = 'user_' . ($user->id ?? '0') . '_' . Str::slug($user->name ?: $user->username ?: 'user') . '.' . $extension;
            $targetPath = $tempDir . '/' . $safeName;
            copy($sourcePath, $targetPath);

            $manifest[] = [
                'user_id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'foto' => $user->foto,
                'file' => $safeName,
            ];
        }

        file_put_contents($tempDir . '/manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $zip = new ZipArchive();
        if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
            $files = glob($tempDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $zip->addFile($file, basename($file));
                }
            }
            $zip->close();
        }

        $this->deleteDirectory($tempDir);

        return basename($archivePath);
    }

    public function listBackups(): array
    {
        $baseDir = $this->ensureDirectory();
        $files = glob($baseDir . '/*.zip') ?: [];
        rsort($files);

        return array_map(function ($file) {
            return [
                'name' => basename($file),
                'path' => $file,
                'size' => filesize($file),
                'modified' => filemtime($file),
            ];
        }, $files);
    }

    public function downloadPath(string $name): ?string
    {
        $path = $this->ensureDirectory() . '/' . $name;
        return file_exists($path) ? $path : null;
    }

    public function delete(string $name): bool
    {
        $path = $this->ensureDirectory() . '/' . $name;
        return file_exists($path) ? unlink($path) : false;
    }

    public function import(string $archivePath): bool
    {
        if (! file_exists($archivePath)) {
            return false;
        }

        $zip = new ZipArchive();
        if ($zip->open($archivePath) !== true) {
            return false;
        }

        $tempDir = $this->ensureDirectory() . '/import_' . time();
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $zip->extractTo($tempDir);
        $zip->close();

        $manifestPath = $tempDir . '/manifest.json';
        if (! file_exists($manifestPath)) {
            $this->deleteDirectory($tempDir);
            return false;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true) ?: [];

        foreach ($manifest as $entry) {
            $userId = $entry['user_id'] ?? null;
            $user = null;

            if ($userId) {
                $user = User::find($userId);
            }

            if (! $user && ! empty($entry['username'])) {
                $user = User::where('username', $entry['username'])->first();
            }

            if (! $user && ! empty($entry['email'])) {
                $user = User::where('email', $entry['email'])->first();
            }

            if (! $user) {
                continue;
            }

            $sourceFile = $tempDir . '/' . ($entry['file'] ?? '');
            if (! file_exists($sourceFile)) {
                continue;
            }

            if ($user->foto && Storage::disk('public')->exists($user->foto)) {
                Storage::disk('public')->delete($user->foto);
            }

            $storedPath = 'user_photos/' . basename($sourceFile);
            Storage::disk('public')->put($storedPath, file_get_contents($sourceFile));
            $user->foto = $storedPath;
            $user->save();
        }

        $this->deleteDirectory($tempDir);

        return true;
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
