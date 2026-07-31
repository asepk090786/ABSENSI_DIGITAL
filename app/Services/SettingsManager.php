<?php
namespace App\Services;

class SettingsManager
{
    protected $path;

    public function __construct()
    {
        $this->path = dirname(__DIR__, 2) . '/storage/app/settings.json';
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

        $dir = dirname($this->path);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        if (! is_writable($dir)) {
            $fallbackDir = storage_path('app');
            if (! is_dir($fallbackDir)) {
                @mkdir($fallbackDir, 0755, true);
            }
            $this->path = $fallbackDir . '/settings.json';
            $dir = dirname($this->path);
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
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
}
