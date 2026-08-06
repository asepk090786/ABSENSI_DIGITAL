<?php

namespace App\Services;

use Illuminate\Support\Str;

class AppVersionService
{
    protected string $statePath;
    protected string $historyPath;

    public function __construct(?string $statePath = null, ?string $historyPath = null)
    {
        $this->statePath = $statePath ?? storage_path('app/version_state.json');
        $this->historyPath = $historyPath ?? storage_path('app/version_history.json');
    }

    protected function candidatePaths(string $preferredPath): array
    {
        return array_values(array_unique([
            $preferredPath,
            storage_path('app/version_state.json'),
            storage_path('app/version_history.json'),
            public_path('uploads/version_data/version_state.json'),
            public_path('uploads/version_data/version_history.json'),
            sys_get_temp_dir() . '/absensi_version_state.json',
            sys_get_temp_dir() . '/absensi_version_history.json',
        ]));
    }

    public function getVersionInfo(): array
    {
        $state = $this->readState();
        $version = $this->formatVersion($state['major'] ?? 1, $state['minor'] ?? 0, $state['patch'] ?? 26);

        return [
            'version' => $version,
            'major' => (int) ($state['major'] ?? 1),
            'minor' => (int) ($state['minor'] ?? 0),
            'patch' => (int) ($state['patch'] ?? 26),
            'year' => (int) ($state['year'] ?? date('Y')),
            'history' => $this->getHistory(),
            'whats_new' => $this->getWhatsNew(),
        ];
    }

    public function bumpVersion(string $source = 'manual', ?string $notes = null, array $extraState = []): array
    {
        $state = $this->readState();
        $year = (int) date('Y');
        $major = $year >= 2026 ? ($year - 2025) : 1;
        $minor = 0;
        $patch = 1;

        if ((int) ($state['year'] ?? $year) === $year) {
            $patch = (int) ($state['patch'] ?? 26) + 1;
            $major = (int) ($state['major'] ?? $major);
            $minor = (int) ($state['minor'] ?? 0);
        } else {
            $major = $major;
            $minor = 0;
            $patch = 1;
        }

        $version = $this->formatVersion($major, $minor, $patch);

        $newState = [
            'year' => $year,
            'major' => $major,
            'minor' => $minor,
            'patch' => $patch,
            'updated_at' => now()->toDateTimeString(),
            'source' => $source,
            ...$extraState,
        ];

        $this->writeState($newState);

        $history = $this->getHistory();
        $notesText = $notes ?: $this->buildAutoReleaseNotes();

        $history[] = [
            'version' => $version,
            'date' => now()->toDateString(),
            'notes' => $notesText,
            'source' => $source,
        ];

        $this->writeHistory($history);

        return [
            'version' => $version,
            'major' => $major,
            'minor' => $minor,
            'patch' => $patch,
            'year' => $year,
            'history' => $history,
            'whats_new' => $this->getWhatsNew(),
        ];
    }

    public function syncFromGit(string $source = 'git_push', ?string $notes = null, ?string $repoPath = null): array
    {
        $repoPath = $repoPath ?: base_path();
        $branch = $this->getGitBranch($repoPath);
        $commit = $this->runGitCommand(['git', 'rev-parse', '--short', 'HEAD'], $repoPath);

        if (! $commit) {
            return $this->bumpVersion($source, $notes ?: 'Tidak ada commit Git yang dapat dibaca.');
        }

        $state = $this->readState();
        if (($state['git_commit'] ?? null) === $commit && ($state['git_branch'] ?? null) === $branch) {
            return $this->getVersionInfo();
        }

        $message = $this->runGitCommand(['git', 'log', '-1', '--pretty=%s'], $repoPath);
        $body = $this->runGitCommand(['git', 'log', '-1', '--pretty=%b'], $repoPath);
        $releaseNotes = $notes ?: trim($message ?: 'Perubahan aplikasi diterapkan');

        if ($branch) {
            $releaseNotes .= ' [branch: ' . $branch . ']';
        }

        if ($body) {
            $releaseNotes .= ' — ' . Str::limit(trim($body), 140);
        }

        return $this->bumpVersion($source, $releaseNotes, [
            'git_commit' => $commit,
            'git_branch' => $branch,
        ]);
    }

    public function getHistory(): array
    {
        foreach ($this->candidatePaths($this->historyPath) as $candidate) {
            if (file_exists($candidate)) {
                $json = @file_get_contents($candidate);
                if ($json !== false) {
                    $data = json_decode($json, true);
                    if (is_array($data)) {
                        $this->historyPath = $candidate;
                        return $data;
                    }
                }
            }
        }

        return [];
    }

    public function getWhatsNew(): array
    {
        $history = $this->getHistory();
        $latest = end($history);

        if (! is_array($latest)) {
            return [];
        }

        return [
            'version' => $latest['version'] ?? null,
            'date' => $latest['date'] ?? null,
            'notes' => $latest['notes'] ?? null,
            'source' => $latest['source'] ?? null,
        ];
    }

    protected function buildAutoReleaseNotes(): string
    {
        $repoPath = base_path();
        $branch = $this->getGitBranch($repoPath);
        $commit = $this->runGitCommand(['git', 'log', '-1', '--pretty=%s'], $repoPath);
        $body = $this->runGitCommand(['git', 'log', '-1', '--pretty=%b'], $repoPath);

        $notes = trim($commit ?: 'Pembaruan aplikasi');
        if ($branch) {
            $notes .= ' [branch: ' . $branch . ']';
        }
        if ($body) {
            $notes .= ' — ' . Str::limit(trim($body), 140);
        }

        return $notes;
    }

    protected function getGitBranch(string $repoPath): ?string
    {
        $branch = $this->runGitCommand(['git', 'rev-parse', '--abbrev-ref', 'HEAD'], $repoPath);
        return $branch ?: null;
    }

    protected function runGitCommand(array $command, string $repoPath): ?string
    {
        if (! is_dir($repoPath . '/.git')) {
            return null;
        }

        $process = new \Symfony\Component\Process\Process($command, $repoPath, null, null, 30);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        return trim($process->getOutput());
    }

    protected function readState(): array
    {
        foreach ($this->candidatePaths($this->statePath) as $candidate) {
            if (file_exists($candidate)) {
                $json = @file_get_contents($candidate);
                if ($json !== false) {
                    $data = json_decode($json, true);
                    if (is_array($data)) {
                        $this->statePath = $candidate;
                        return $data;
                    }
                }
            }
        }

        $default = [
            'year' => 2026,
            'major' => 1,
            'minor' => 0,
            'patch' => 26,
            'updated_at' => now()->toDateTimeString(),
            'source' => 'initial',
        ];
        $this->writeState($default);
        return $default;
    }

    protected function writeState(array $state): void
    {
        $this->writeJsonFile($this->statePath, $state);
    }

    protected function writeHistory(array $history): void
    {
        $this->writeJsonFile($this->historyPath, $history);
    }

    protected function writeJsonFile(string $preferredPath, array $payload): void
    {
        $candidates = $this->candidatePaths($preferredPath);
        foreach ($candidates as $candidate) {
            $dir = dirname($candidate);
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }

            if (is_dir($dir) && is_writable($dir)) {
                $result = @file_put_contents($candidate, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                if ($result !== false) {
                    if (str_contains($candidate, 'version_state')) {
                        $this->statePath = $candidate;
                    } else {
                        $this->historyPath = $candidate;
                    }
                    return;
                }
            }
        }
    }

    protected function formatVersion(int $major, int $minor, int $patch): string
    {
        return 'Ver.' . $major . '.' . $minor . '.' . $patch;
    }
}
