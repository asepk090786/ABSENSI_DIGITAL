<?php

namespace Tests\Unit;

use App\Services\SettingsManager;
use Tests\TestCase;

class SettingsManagerTest extends TestCase
{
    public function test_it_prefers_the_latest_written_settings_across_known_paths(): void
    {
        $tempRoot = sys_get_temp_dir() . '/absensi_settings_manager_' . uniqid('', true);
        if (! is_dir($tempRoot)) {
            mkdir($tempRoot, 0777, true);
        }

        $primaryPath = $tempRoot . '/settings.json';
        $fallbackPath = sys_get_temp_dir() . '/absensi_settings.json';

        file_put_contents($primaryPath, json_encode([
            'attendance' => [
                'verification_timeout_seconds' => 300,
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        file_put_contents($fallbackPath, json_encode([
            'attendance' => [
                'verification_timeout_seconds' => 900,
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $manager = new class($primaryPath) extends SettingsManager {
            public function __construct(string $path)
            {
                $this->path = $path;
            }
        };

        $this->assertSame(900, $manager->get('attendance.verification_timeout_seconds'));
    }
}
