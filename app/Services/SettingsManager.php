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
        if (! is_dir(dirname($this->path))) {
            mkdir(dirname($this->path), 0755, true);
        }
        file_put_contents($this->path, json_encode($all, JSON_PRETTY_PRINT));
    }
}
