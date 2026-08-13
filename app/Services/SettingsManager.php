<?php
namespace App\Services;

class SettingsManager
{
    protected $path;

    public function __construct()
    {
        $this->path = $this->resolveDefaultPath();
    }

    protected function resolveDefaultPath(): string
    {
        if (function_exists('storage_path')) {
            try {
                return storage_path('app/settings.json');
            } catch (\Throwable $e) {
                // Fall back to a filesystem path when the Laravel app container is not bootstrapped.
            }
        }

        $basePath = dirname(__DIR__, 2) . '/storage';
        if (is_dir($basePath)) {
            return $basePath . '/app/settings.json';
        }

        return sys_get_temp_dir() . '/absensi_settings.json';
    }

    protected function candidatePaths(): array
    {
        $candidates = [
            $this->path,
            function_exists('storage_path') ? storage_path('app/settings.json') : null,
            function_exists('public_path') ? public_path('uploads/settings.json') : null,
            sys_get_temp_dir() . '/absensi_settings.json',
        ];

        return array_values(array_unique(array_filter(array_map('strval', $candidates), fn ($candidate) => $candidate !== '')));
    }

    public function all()
    {
        $latestData = [];
        $latestPath = null;
        $latestTimestamp = null;

        foreach ($this->candidatePaths() as $candidate) {
            if (! file_exists($candidate)) {
                continue;
            }

            $mtime = filemtime($candidate);
            $json = @file_get_contents($candidate);
            if ($json === false) {
                continue;
            }

            $data = json_decode($json, true);
            if (! is_array($data)) {
                continue;
            }

            if ($latestTimestamp === null || $mtime >= $latestTimestamp) {
                $latestData = $data;
                $latestPath = $candidate;
                $latestTimestamp = $mtime;
            }
        }

        if ($latestPath) {
            $this->path = $latestPath;
        }

        return $latestData;
    }

    public function get($key, $default = null)
    {
        $all = $this->all();
        return data_get($all, $key, $default);
    }

    public function set($key, $value)
    {
        $all = $this->all();
        data_set($all, $key, $value);

        $json = json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $json = '{}';
        }

        $written = false;
        $targetPath = null;

        foreach ($this->candidatePaths() as $candidate) {
            $dir = dirname($candidate);
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            if (! is_dir($dir) || ! is_writable($dir)) {
                continue;
            }

            $result = @file_put_contents($candidate, $json);
            if ($result !== false) {
                $targetPath = $candidate;
                $written = true;
            }
        }

        if ($targetPath) {
            $this->path = $targetPath;
        }

        if (! $written) {
            throw new \RuntimeException('Unable to write settings file. No writable settings path available.');
        }
    }

    protected function resolveWritablePath(string $preferredPath): string
    {
        $candidates = [
            $preferredPath,
            function_exists('storage_path') ? storage_path('app/settings.json') : null,
            function_exists('public_path') ? public_path('uploads/settings.json') : null,
            sys_get_temp_dir() . '/absensi_settings.json',
        ];

        foreach (array_filter(array_map('strval', $candidates), fn ($candidate) => $candidate !== '') as $candidate) {
            $dir = dirname($candidate);
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            if (is_dir($dir) && is_writable($dir)) {
                return $candidate;
            }
        }

        return $preferredPath;
    }
}
