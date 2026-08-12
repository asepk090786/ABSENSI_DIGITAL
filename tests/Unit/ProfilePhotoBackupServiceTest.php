<?php

namespace Tests\Unit;

use App\Services\BackupService;
use App\Services\ProfilePhotoBackupService;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ProfilePhotoBackupServiceTest extends TestCase
{
    public function test_extracts_user_id_from_common_photo_backup_filenames(): void
    {
        $service = new ProfilePhotoBackupService();
        $method = new ReflectionMethod($service, 'extractUserIdFromFilename');
        $method->setAccessible(true);

        $this->assertSame(123, $method->invoke($service, 'user_123_photo.jpg'));
        $this->assertSame(456, $method->invoke($service, 'foto_456.png'));
        $this->assertSame(789, $method->invoke($service, 'avatar-789.jpeg'));
        $this->assertSame(321, $method->invoke($service, 'photo_321.webp'));
    }

    public function test_builds_merged_sql_from_split_database_backup_parts(): void
    {
        $service = new BackupService();
        $tmpDir = sys_get_temp_dir() . '/simadis_split_backup_' . uniqid('', true);
        mkdir($tmpDir, 0755, true);

        file_put_contents($tmpDir . '/manifest.json', json_encode([
            'backup_type' => 'database',
            'parts' => [
                ['name' => 'SIMADIS_PART_001.sql', 'checksum' => hash('sha256', 'CREATE TABLE users (id INT);')],
                ['name' => 'SIMADIS_PART_002.sql', 'checksum' => hash('sha256', 'INSERT INTO users VALUES (1);')],
            ],
        ], JSON_PRETTY_PRINT));

        file_put_contents($tmpDir . '/SIMADIS_PART_001.sql', 'CREATE TABLE users (id INT);');
        file_put_contents($tmpDir . '/SIMADIS_PART_002.sql', 'INSERT INTO users VALUES (1);');

        $method = new ReflectionMethod($service, 'buildMergedSqlFromBackupDirectory');
        $method->setAccessible(true);

        $merged = $method->invoke($service, $tmpDir);

        $this->assertStringContainsString('CREATE TABLE users (id INT);', $merged);
        $this->assertStringContainsString('INSERT INTO users VALUES (1);', $merged);

        $this->deleteDirectory($tmpDir);
    }

    protected function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        foreach (array_diff(scandir($path), ['.', '..']) as $item) {
            $full = $path . DIRECTORY_SEPARATOR . $item;
            if (is_dir($full)) {
                $this->deleteDirectory($full);
            } else {
                unlink($full);
            }
        }

        rmdir($path);
    }
}
