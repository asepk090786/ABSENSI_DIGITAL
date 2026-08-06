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

    public function all()
    {
        if (! file_exists($this->path)) return [];
        $json = file_get_contents($this->path);
        return json_decode($json, true) ?: [];
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

        $this->path = $this->resolveWritablePath($this->path);
        $dir = dirname($this->path);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $json = json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($json === false) {
            $json = '{}';
        }

        $written = @file_put_contents($this->path, $json);
        if ($written === false) {
            throw new \RuntimeException('Unable to write settings file: ' . $this->path);
        }
    }

    protected function resolveWritablePath(string $preferredPath): string
    {
        $candidates = [
            $preferredPath,
            storage_path('app/settings.json'),
            public_path('uploads/settings.json'),
            sys_get_temp_dir() . '/absensi_settings.json',
        ];

        foreach ($candidates as $candidate) {
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
