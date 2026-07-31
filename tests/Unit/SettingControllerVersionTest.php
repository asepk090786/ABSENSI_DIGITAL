<?php

namespace Tests\Unit;

use App\Http\Controllers\SettingController;
use Tests\TestCase;

class SettingControllerVersionTest extends TestCase
{
    public function test_remote_date_based_tag_is_not_treated_as_newer_semver_version(): void
    {
        $controller = new SettingController();
        $method = new \ReflectionMethod($controller, 'isVersionNewer');
        $method->setAccessible(true);

        $localVersion = ['major' => 1, 'minor' => 0, 'patch' => 27];
        $remoteVersion = ['major' => 2026, 'minor' => 2, 'patch' => 18];

        $this->assertFalse($method->invoke($controller, $localVersion, $remoteVersion));
    }

    public function test_remote_branch_name_falls_back_to_main_when_remote_head_is_unavailable(): void
    {
        $repoPath = sys_get_temp_dir() . '/absensi-branch-test-' . uniqid('', true);
        mkdir($repoPath, 0755, true);

        $process = new \Symfony\Component\Process\Process(['git', 'init', '--initial-branch=main'], $repoPath);
        $process->run();

        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());

        $controller = new SettingController();
        $method = new \ReflectionMethod($controller, 'getRemoteBranchName');
        $method->setAccessible(true);

        $this->assertSame('main', $method->invoke($controller, $repoPath));
    }
}
