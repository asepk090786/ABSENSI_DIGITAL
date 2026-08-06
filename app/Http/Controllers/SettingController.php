<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\Sekolah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\BackupService;
use App\Services\ProfilePhotoBackupService;
use App\Services\SettingsManager;
use App\Services\AppVersionService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Process\Process;

class SettingController extends Controller
{
    public function index()
    {
        $tahuns = TahunAjaran::all();
        $active_tahun = TahunAjaran::where('is_active', 1)->first();
        $active_semester = $active_tahun ? Semester::where('tahun_ajaran_id', $active_tahun->id)->where('is_active', 1)->first() : null;
        $sekolah = Sekolah::first();

        return view('setting.index', compact('tahuns', 'active_tahun', 'active_semester', 'sekolah'));
    }

    public function absensi()
    {
        $settings = new SettingsManager();

        return view('setting.absensi', [
            'settings' => [
                'allow_edit_past_for_guru' => (bool) $settings->get('attendance.allow_edit_past_for_guru', false),
                'allow_edit_past_for_siswa_officer' => (bool) $settings->get('attendance.allow_edit_past_for_siswa_officer', false),
                'verification_timeout_seconds' => (int) $settings->get('attendance.verification_timeout_seconds', 300),
            ],
        ]);
    }

    public function updateAbsensi(Request $request)
    {
        $validated = $request->validate([
            'allow_edit_past_for_guru' => 'nullable|boolean',
            'allow_edit_past_for_siswa_officer' => 'nullable|boolean',
            'verification_timeout_seconds' => 'nullable|integer|min:10|max:3600',
        ]);

        $settings = new SettingsManager();
        $settings->set('attendance.allow_edit_past_for_guru', (bool) ($validated['allow_edit_past_for_guru'] ?? false));
        $settings->set('attendance.allow_edit_past_for_siswa_officer', (bool) ($validated['allow_edit_past_for_siswa_officer'] ?? false));
        $settings->set('attendance.verification_timeout_seconds', (int) ($validated['verification_timeout_seconds'] ?? 300));

        return redirect()->route('setting.absensi')->with('success', 'Pengaturan absensi disimpan.');
    }

    public function tahunAjaran()
    {
        $tahuns = TahunAjaran::all();
        return view('setting.tahun_ajaran', compact('tahuns'));
    }

    public function createTahunAjaran()
    {
        return view('setting.tahun_ajaran_create');
    }

    public function showTahunAjaran(TahunAjaran $tahunAjaran)
    {
        $semesters = $tahunAjaran->semesters()->get();
        return view('setting.tahun_ajaran_show', compact('tahunAjaran','semesters'));
    }

    public function editTahunAjaran(TahunAjaran $tahunAjaran)
    {
        return view('setting.tahun_ajaran_edit', compact('tahunAjaran'));
    }

    public function storeTahunAjaran(Request $request)
    {
        $data = $request->validate([
            'nama_tahun' => 'required|string|unique:tahun_ajaran,nama_tahun',
        ]);

        TahunAjaran::create($data);
        return redirect()->route('setting.tahun_ajaran')->with('success', 'Tahun ajaran ditambahkan');
    }

    public function updateTahunAjaran(Request $request, TahunAjaran $tahunAjaran)
    {
        $data = $request->validate([
            'nama_tahun' => 'required|string|unique:tahun_ajaran,nama_tahun,' . $tahunAjaran->id,
        ]);

        $tahunAjaran->update($data);
        return redirect()->route('setting.tahun_ajaran')->with('success', 'Tahun ajaran diperbarui');
    }

    public function activateTahunAjaran(TahunAjaran $tahunAjaran)
    {
        DB::table('tahun_ajaran')->update(['is_active' => 0]);
        $tahunAjaran->update(['is_active' => 1]);

        // Ensure semester state stays consistent when switching active year.
        DB::table('semester')
            ->where('tahun_ajaran_id', '!=', $tahunAjaran->id)
            ->update(['is_active' => 0]);

        return back()->with('success', 'Tahun ajaran ' . $tahunAjaran->nama_tahun . ' diaktifkan');
    }

    public function deactivateTahunAjaran(TahunAjaran $tahunAjaran)
    {
        $tahunAjaran->update(['is_active' => 0]);
        return back()->with('success', 'Tahun ajaran dinonaktifkan');
    }

    public function destroyTahunAjaran(TahunAjaran $tahunAjaran)
    {
        try {
            $tahunAjaran->delete();
            return redirect()->route('setting.tahun_ajaran')->with('success', 'Tahun ajaran dihapus');
        } catch (\Throwable $e) {
            return back()->withErrors('Tidak dapat menghapus: masih ada data terkait.');
        }
    }

    public function semester()
    {
        $active_tahun = TahunAjaran::where('is_active', 1)->first();
        $semesters = $active_tahun ? Semester::where('tahun_ajaran_id', $active_tahun->id)->get() : [];

        return view('setting.semester', compact('semesters', 'active_tahun'));
    }

    public function createSemester()
    {
        $active_tahun = TahunAjaran::where('is_active', 1)->first();

        if (! $active_tahun) {
            return back()->withErrors('Pilih tahun ajaran aktif dulu');
        }

        return view('setting.semester_create', compact('active_tahun'));
    }

    public function storeSemester(Request $request)
    {
        $data = $request->validate([
            'tahun_ajaran_id' => 'required|integer|exists:tahun_ajaran,id',
            'nama_semester' => 'required|string|in:Semester 1 (Ganjil),Semester 2 (Genap)',
        ]);

        $exists = Semester::where('tahun_ajaran_id', $data['tahun_ajaran_id'])
            ->where('nama_semester', $data['nama_semester'])
            ->exists();
        if ($exists) {
            return back()->withErrors('Semester sudah ada untuk tahun ajaran ini');
        }

        Semester::create($data);
        return redirect()->route('setting.semester')->with('success', 'Semester ditambahkan');
    }

    public function activateSemester(Semester $semester)
    {
        DB::table('semester')->where('tahun_ajaran_id', $semester->tahun_ajaran_id)->update(['is_active' => 0]);
        $semester->update(['is_active' => 1]);

        return back()->with('success', 'Semester ' . $semester->nama_semester . ' diaktifkan');
    }

    public function showSemester(Semester $semester)
    {
        return view('setting.semester_show', compact('semester'));
    }

    public function editSemester(Semester $semester)
    {
        $active_tahun = TahunAjaran::find($semester->tahun_ajaran_id);
        return view('setting.semester_edit', compact('semester','active_tahun'));
    }

    public function updateSemester(Request $request, Semester $semester)
    {
        $data = $request->validate([
            'nama_semester' => 'required|string|in:Semester 1 (Ganjil),Semester 2 (Genap)',
        ]);

        $exists = Semester::where('tahun_ajaran_id', $semester->tahun_ajaran_id)
            ->where('nama_semester', $data['nama_semester'])
            ->where('id', '!=', $semester->id)
            ->exists();
        if ($exists) {
            return back()->withErrors('Semester sudah ada untuk tahun ajaran ini');
        }

        $semester->update($data);
        return redirect()->route('setting.semester')->with('success','Semester diperbarui');
    }

    public function deactivateSemester(Semester $semester)
    {
        $semester->update(['is_active' => 0]);
        return back()->with('success','Semester dinonaktifkan');
    }

    public function destroySemester(Semester $semester)
    {
        try {
            $semester->delete();
            return redirect()->route('setting.semester')->with('success','Semester dihapus');
        } catch (\Throwable $e) {
            return back()->withErrors('Tidak dapat menghapus: masih ada data terkait.');
        }
    }

    public function updateJadwalVisibility(Request $request)
    {
        $validated = $request->validate([
            'tampilkan_jadwal_guru' => 'required|boolean',
            'tampilkan_jadwal_siswa' => 'required|boolean',
            'tampilkan_nama_wali_kelas_guru' => 'required|boolean',
            'tampilkan_nama_wali_kelas_siswa' => 'required|boolean',
            'jadwal_maintenance_message' => 'nullable|string',
            'wali_kelas_hidden_message' => 'nullable|string',
        ]);

        $sekolah = Sekolah::first();
        if (! $sekolah) {
            $sekolah = new Sekolah();
            $sekolah->nama_sekolah = config('app.name', 'SEKOLAH');
            $sekolah->alamat = '-';
            $sekolah->kota = '-';
            $sekolah->provinsi = '-';
        }

        $sekolah->tampilkan_jadwal_guru = $validated['tampilkan_jadwal_guru'];
        $sekolah->tampilkan_jadwal_siswa = $validated['tampilkan_jadwal_siswa'];
        $sekolah->tampilkan_nama_wali_kelas_guru = $validated['tampilkan_nama_wali_kelas_guru'];
        $sekolah->tampilkan_nama_wali_kelas_siswa = $validated['tampilkan_nama_wali_kelas_siswa'];
        $sekolah->tampilkan_jadwal = $validated['tampilkan_jadwal_guru'] || $validated['tampilkan_jadwal_siswa'];
        $sekolah->tampilkan_nama_wali_kelas = $validated['tampilkan_nama_wali_kelas_guru'] || $validated['tampilkan_nama_wali_kelas_siswa'];

        // Sanitasi sederhana untuk menghindari script injection
        $rawMessage = $validated['jadwal_maintenance_message'] ?? null;
        if ($rawMessage) {
            // Hapus tag <script> dan <iframe> beserta isinya
            $msg = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $rawMessage);
            $msg = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $msg);

            // Hapus atribut event handler seperti onclick, onerror, dsb.
            $msg = preg_replace('/\s+on[a-zA-Z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $msg);

            // Hapus href/src yang menggunakan javascript:
            $msg = preg_replace_callback('/(href|src)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', function($m) {
                $attr = $m[0];
                // ambil value
                $parts = explode('=', $attr, 2);
                if (count($parts) < 2) return '';
                $name = $parts[0];
                $val = trim($parts[1]);
                $valUnquoted = trim($val, " \t\n\r\0\x0B\'\"");
                if (preg_match('/^javascript:/i', $valUnquoted)) {
                    return '';
                }
                return ' ' . $name . '=' . $val;
            }, $msg);

            // Trim panjang konten untuk keamanan
            $msg = substr($msg, 0, 2000);
            $sekolah->jadwal_maintenance_message = $msg;
        } else {
            $sekolah->jadwal_maintenance_message = null;
        }

        $rawHiddenMessage = $validated['wali_kelas_hidden_message'] ?? null;
        if ($rawHiddenMessage) {
            $hiddenMsg = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $rawHiddenMessage);
            $hiddenMsg = preg_replace('#<iframe(.*?)>(.*?)</iframe>#is', '', $hiddenMsg);
            $hiddenMsg = preg_replace('/\s+on[a-zA-Z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $hiddenMsg);
            $hiddenMsg = preg_replace_callback('/(href|src)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', function($m) {
                $attr = $m[0];
                $parts = explode('=', $attr, 2);
                if (count($parts) < 2) return '';
                $name = $parts[0];
                $val = trim($parts[1]);
                $valUnquoted = trim($val, " \t\n\r\0\x0B\'\"");
                if (preg_match('/^javascript:/i', $valUnquoted)) {
                    return '';
                }
                return ' ' . $name . '=' . $val;
            }, $hiddenMsg);
            $hiddenMsg = substr($hiddenMsg, 0, 2000);
            $sekolah->wali_kelas_hidden_message = $hiddenMsg;
        } else {
            $sekolah->wali_kelas_hidden_message = null;
        }

        $sekolah->save();

        return back()->with('success', 'Pengaturan tampilan jadwal berhasil disimpan.');
    }

    public function header()
    {
        $sekolah = \App\Models\Sekolah::first();
        return view('setting.header', compact('sekolah'));
    }

    public function bumpVersionFromGit(Request $request)
    {
        $versionService = new AppVersionService();
        $result = $versionService->syncFromGit('manual_sync', $request->input('notes'));

        return back()->with('success', 'Versi SIMADIS dinaikkan menjadi ' . $result['version'] . ' dan keterangan update disimpan.');
    }

    // About: server specifications & installed library/plugin status
    public function about()
    {
        $server = $this->collectServerSpecs();
        $phpLibraries = $this->collectComposerLibraries();
        $jsLibraries = $this->collectNpmLibraries();
        $appInfo = $this->collectAppInfo();

        return view('setting.about', compact('server', 'phpLibraries', 'jsLibraries', 'appInfo'));
    }

    public function checkForUpdates()
    {
        $status = $this->checkRemoteUpdateStatus();

        return back()
            ->with($status['has_update'] ? 'success' : 'info', $status['message'])
            ->with('update_status', $status);
    }

    public function applyUpdate()
    {
        $process = new Process(['git', 'fetch', '--all', '--tags'], base_path(), null, null, 300);
        try {
            $process->run();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengambil data remote GitHub: ' . $e->getMessage());
        }

        $fetchOutput = trim($process->getOutput() ?: $process->getErrorOutput());
        if (! $process->isSuccessful()) {
            return back()->with('error', 'Gagal mengambil data remote GitHub: ' . Str::limit($fetchOutput ?: 'Tidak ada output dari Git.', 1000));
        }

        $branch = $this->getRemoteBranchName(base_path());
        $pullProcess = new Process(['git', 'pull', '--ff-only', 'origin', $branch], base_path(), null, null, 300);
        try {
            $pullProcess->run();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menjalankan update: ' . $e->getMessage());
        }

        $output = trim($pullProcess->getOutput() ?: $pullProcess->getErrorOutput());
        $success = $pullProcess->isSuccessful();
        $message = $success
            ? 'Update berhasil dijalankan. ' . ($output ? Str::limit($output, 1000) : 'Repository sudah sesuai dengan versi terbaru.')
            : 'Update gagal: ' . Str::limit($output ?: 'Tidak ada output dari Git.', 1000);

        if ($success) {
            $versionService = new AppVersionService();
            $versionService->syncFromGit('github_update', 'Update diterapkan dari GitHub');
        }

        return back()->with($success ? 'success' : 'error', $message);
    }

    // Runs the OS-appropriate install command to sync missing dependencies (composer/npm)
    public function installLibrary(Request $request, string $type)
    {
        abort_unless(in_array($type, ['composer', 'npm'], true), 404);

        $isWindows = PHP_OS_FAMILY === 'Windows';

        if ($type === 'composer') {
            $command = [$isWindows ? 'composer.bat' : 'composer', 'install', '--no-interaction', '--no-ansi', '--prefer-dist'];
        } else {
            $command = [$isWindows ? 'npm.cmd' : 'npm', 'install', '--no-audit', '--no-fund'];
        }

        $process = new Process($command, base_path(), null, null, 300);

        try {
            $process->run();
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menjalankan proses instalasi: ' . $e->getMessage());
        }

        if (!$process->isSuccessful()) {
            $output = trim($process->getErrorOutput() ?: $process->getOutput());
            return back()->with('error', 'Instalasi ' . strtoupper($type) . ' gagal: ' . Str::limit($output, 1000));
        }

        return back()->with('success', 'Dependencies ' . strtoupper($type) . ' berhasil diinstal.');
    }

    private function collectAppInfo(): array
    {
        $status = session('update_status') ?: $this->checkRemoteUpdateStatus();

        $versionService = new AppVersionService();
        $versionInfo = $versionService->getVersionInfo();

        return [
            'name' => 'SIMADIS',
            'description' => 'Sistem Informasi Manajemen Absensi Digital untuk sekolah, yang membantu pengelolaan absensi, jadwal, data siswa, guru, dan pelaporan secara terintegrasi.',
            'logo' => asset('images/icon_simadisnew.png'),
            'version' => $versionInfo['version'],
            'repository' => env('APP_REPOSITORY_URL', 'https://github.com/asepk090786/ABSENSI_DIGITAL'),
            'current_commit' => $status['current_commit'] ?? '-',
            'remote_commit' => $status['remote_commit'] ?? '-',
            'update_available' => (bool) ($status['has_update'] ?? false),
            'update_message' => $status['message'] ?? 'Belum ada informasi update.',
            'history' => $versionInfo['history'],
            'whats_new' => $versionInfo['whats_new'] ?? [],
        ];
    }

    private function checkRemoteUpdateStatus(): array
    {
        $repoPath = base_path();

        if (! is_dir($repoPath . '/.git')) {
            return [
                'has_update' => false,
                'current_commit' => '-',
                'remote_commit' => '-',
                'message' => 'Repository Git tidak ditemukan di direktori aplikasi.',
            ];
        }

        $currentCommit = $this->runGitCommand(['git', 'rev-parse', '--short', 'HEAD'], $repoPath);
        $branch = $this->getRemoteBranchName($repoPath);
        $remoteOutput = $this->runGitCommand(['git', 'ls-remote', '--heads', 'origin', $branch], $repoPath);
        $remoteCommit = null;

        if ($remoteOutput) {
            preg_match('/^([0-9a-f]+)/', trim($remoteOutput), $matches);
            $remoteCommit = $matches[1] ?? null;
            $remoteCommit = $remoteCommit ? substr($remoteCommit, 0, 7) : null;
        }

        $versionService = new AppVersionService();
        $localVersionInfo = $versionService->getVersionInfo();
        $remoteVersionInfo = $this->getRemoteVersionInfo($repoPath);

        if (! $currentCommit) {
            return [
                'has_update' => false,
                'current_commit' => '-',
                'remote_commit' => $remoteCommit ?? '-',
                'message' => 'Tidak dapat membaca commit lokal dari Git.',
            ];
        }

        if (! $remoteCommit) {
            return [
                'has_update' => false,
                'current_commit' => $currentCommit,
                'remote_commit' => '-',
                'message' => 'Tidak dapat memeriksa remote GitHub saat ini. Periksa koneksi atau remote repository.',
            ];
        }

        $hasUpdate = false;
        $message = 'Aplikasi sudah terupdate dengan versi yang tersedia di GitHub.';

        if ($remoteVersionInfo) {
            $hasUpdate = $this->isVersionNewer($localVersionInfo, $remoteVersionInfo);
            $message = $hasUpdate
                ? 'Versi terbaru tersedia di GitHub. Anda dapat melakukan update melalui tombol di bawah ini.'
                : 'Versi aplikasi Anda sudah sesuai dengan repository GitHub.';
        } elseif ($currentCommit !== $remoteCommit) {
            $isBehind = $this->runGitCommand(['git', 'log', '--oneline', $currentCommit . '..' . $remoteCommit], $repoPath);
            $hasUpdate = (bool) $isBehind;
            $message = $hasUpdate
                ? 'Versi terbaru tersedia di GitHub. Anda dapat melakukan update melalui tombol di bawah ini.'
                : 'Versi aplikasi Anda sudah sesuai dengan repository GitHub.';
        }

        if ($hasUpdate && $currentCommit !== $remoteCommit) {
            $versionService->syncFromGit('remote_update');
        }

        return [
            'has_update' => $hasUpdate,
            'current_commit' => $currentCommit,
            'remote_commit' => $remoteCommit,
            'message' => $message,
        ];
    }

    private function getRemoteVersionInfo(string $repoPath): ?array
    {
        $tagsOutput = $this->runGitCommand(['git', 'ls-remote', '--tags', 'origin'], $repoPath);
        if (! $tagsOutput) {
            return null;
        }

        $versions = [];
        $tagPattern = '/refs\/tags\/(?:v|Ver\.?|)?(\d+)\.(\d+)\.(\d+)(?:[.-].*)?$/im';

        preg_match_all($tagPattern, $tagsOutput, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $major = (int) $match[1];
            $minor = (int) $match[2];
            $patch = (int) $match[3];

            if ($major > 0 || $minor > 0 || $patch > 0) {
                $versions[] = [
                    'major' => $major,
                    'minor' => $minor,
                    'patch' => $patch,
                ];
            }
        }

        if (empty($versions)) {
            return null;
        }

        usort($versions, function ($a, $b) {
            if ($a['major'] !== $b['major']) {
                return $a['major'] <=> $b['major'];
            }
            if ($a['minor'] !== $b['minor']) {
                return $a['minor'] <=> $b['minor'];
            }
            return $a['patch'] <=> $b['patch'];
        });

        return end($versions);
    }

    private function isVersionNewer(array $localVersionInfo, array $remoteVersionInfo): bool
    {
        $local = [$localVersionInfo['major'] ?? 0, $localVersionInfo['minor'] ?? 0, $localVersionInfo['patch'] ?? 0];
        $remote = [$remoteVersionInfo['major'] ?? 0, $remoteVersionInfo['minor'] ?? 0, $remoteVersionInfo['patch'] ?? 0];

        $isSemverLike = static function (array $version): bool {
            return count(array_filter($version, fn ($value) => is_int($value) && $value >= 0)) === 3
                && $version[0] < 1000
                && $version[1] < 1000
                && $version[2] < 1000;
        };

        if (! $isSemverLike($local) || ! $isSemverLike($remote)) {
            return false;
        }

        for ($i = 0; $i < 3; $i++) {
            if ($local[$i] < $remote[$i]) {
                return true;
            }
            if ($local[$i] > $remote[$i]) {
                return false;
            }
        }

        return false;
    }

    private function getRemoteBranchName(string $repoPath): string
    {
        $defaultBranch = $this->runGitCommand(['git', 'symbolic-ref', '--short', 'refs/remotes/origin/HEAD'], $repoPath);
        if ($defaultBranch) {
            return preg_replace('/^origin\//', '', trim($defaultBranch)) ?: 'main';
        }

        foreach (['main', 'master'] as $branch) {
            $branchRef = 'refs/remotes/origin/' . $branch;
            $branchExists = $this->runGitCommand(['git', 'show-ref', '--verify', '--quiet', $branchRef], $repoPath);
            if ($branchExists !== null || $branchExists === '') {
                return $branch;
            }
        }

        return 'main';
    }

    private function runGitCommand(array $command, string $workingDirectory): ?string
    {
        $process = new Process($command, $workingDirectory, null, null, 300);
        $process->run();

        if (! $process->isSuccessful()) {
            return null;
        }

        return trim($process->getOutput());
    }

    private function collectServerSpecs(): array
    {
        $basePath = base_path();
        $freeSpace = @disk_free_space($basePath);
        $totalSpace = @disk_total_space($basePath);

        try {
            $dbVersion = DB::selectOne('select version() as v')->v ?? null;
        } catch (\Throwable $e) {
            $dbVersion = null;
        }

        return [
            'os' => php_uname(),
            'php_version' => PHP_VERSION,
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'CLI / Tidak diketahui',
            'laravel_version' => app()->version(),
            'database_driver' => config('database.default'),
            'database_version' => $dbVersion,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time') . 's',
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => config('app.timezone'),
            'server_time' => now()->format('Y-m-d H:i:s'),
            'disk_free' => $freeSpace ? $this->formatBytes($freeSpace) : 'N/A',
            'disk_total' => $totalSpace ? $this->formatBytes($totalSpace) : 'N/A',
        ];
    }

    private function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function collectComposerLibraries(): array
    {
        $composerJsonPath = base_path('composer.json');
        if (!file_exists($composerJsonPath)) {
            return [];
        }

        $composerJson = json_decode(file_get_contents($composerJsonPath), true) ?? [];
        $required = array_merge($composerJson['require'] ?? [], $composerJson['require-dev'] ?? []);
        unset($required['php']);

        $installedPath = base_path('vendor/composer/installed.json');
        $installedVersions = [];
        if (file_exists($installedPath)) {
            $installedData = json_decode(file_get_contents($installedPath), true) ?? [];
            $packages = $installedData['packages'] ?? $installedData;
            foreach ($packages as $pkg) {
                if (isset($pkg['name'])) {
                    $installedVersions[$pkg['name']] = ltrim($pkg['version'] ?? '-', 'v');
                }
            }
        }

        $libraries = [];
        foreach ($required as $name => $constraint) {
            $isInstalled = isset($installedVersions[$name]) && is_dir(base_path('vendor/' . $name));
            $libraries[] = [
                'name' => $name,
                'constraint' => $constraint,
                'installed_version' => $installedVersions[$name] ?? '-',
                'installed' => $isInstalled,
            ];
        }

        usort($libraries, fn($a, $b) => strcmp($a['name'], $b['name']));

        return $libraries;
    }

    private function collectNpmLibraries(): array
    {
        $packageJsonPath = base_path('package.json');
        if (!file_exists($packageJsonPath)) {
            return [];
        }

        $packageJson = json_decode(file_get_contents($packageJsonPath), true) ?? [];
        $required = array_merge($packageJson['dependencies'] ?? [], $packageJson['devDependencies'] ?? []);

        $lockVersions = [];
        $lockPath = base_path('package-lock.json');
        if (file_exists($lockPath)) {
            $lockData = json_decode(file_get_contents($lockPath), true) ?? [];
            foreach ($lockData['packages'] ?? [] as $pkgPath => $pkgInfo) {
                if ($pkgPath === '' || !str_starts_with($pkgPath, 'node_modules/')) {
                    continue;
                }
                $pkgName = substr($pkgPath, strlen('node_modules/'));
                $lockVersions[$pkgName] = $pkgInfo['version'] ?? '-';
            }
        }

        $libraries = [];
        foreach ($required as $name => $constraint) {
            $isInstalled = is_dir(base_path('node_modules/' . $name));
            $libraries[] = [
                'name' => $name,
                'constraint' => $constraint,
                'installed_version' => $lockVersions[$name] ?? '-',
                'installed' => $isInstalled,
            ];
        }

        usort($libraries, fn($a, $b) => strcmp($a['name'], $b['name']));

        return $libraries;
    }

    // Backup settings UI
    public function backupIndex()
    {
        $settings = new SettingsManager();
        $backupSettings = $settings->all();
        $svc = new BackupService();
        $photoSvc = new ProfilePhotoBackupService();
        $backups = $svc->listBackups();
        $photoBackups = $photoSvc->listBackups();
        return view('setting.backup', compact('backupSettings','backups','photoBackups'));
    }

    public function backupManual(Request $request)
    {
        $request->validate(['format' => 'nullable|in:sql,zip']);
        $format = $request->get('format', 'sql');
        $svc = new BackupService();
        $name = $svc->createBackup($format);

        // If the request asked for immediate download, return the file
        if ($request->boolean('download')) {
            $path = $svc->downloadPath($name);
            if ($path && file_exists($path)) {
                return response()->download($path, $name)->deleteFileAfterSend(false);
            }
            return back()->withErrors('Gagal membuat backup untuk diunduh');
        }

        return back()->with('success', 'Backup dibuat: ' . $name);
    }

    public function backupDownload($name)
    {
        $svc = new BackupService();
        $path = $svc->downloadPath($name);
        if (! $path) return back()->withErrors('File tidak ditemukan');
        return response()->download($path, $name);
    }

    public function backupDelete($name)
    {
        $svc = new BackupService();
        $ok = $svc->delete($name);
        if ($ok) return back()->with('success','Backup dihapus');
        return back()->withErrors('Gagal menghapus backup');
    }

    public function backupProfileExport()
    {
        $svc = new ProfilePhotoBackupService();
        $name = $svc->export();
        return back()->with('success', 'Backup foto profil dibuat: ' . $name);
    }

    public function backupProfileDownload($name)
    {
        $svc = new ProfilePhotoBackupService();
        $path = $svc->downloadPath($name);
        if (! $path) {
            return back()->withErrors('File backup foto profil tidak ditemukan');
        }

        return response()->download($path, $name);
    }

    public function backupProfileDelete($name)
    {
        $svc = new ProfilePhotoBackupService();
        $ok = $svc->delete($name);
        if ($ok) {
            return back()->with('success', 'Backup foto profil dihapus');
        }

        return back()->withErrors('Gagal menghapus backup foto profil');
    }

    public function backupProfileImport(Request $request)
    {
        $request->validate([
            'profile_photo_backup' => ['required', 'file', 'mimes:zip', 'max:20480'],
        ]);

        $path = $request->file('profile_photo_backup')->store('backups/profile_photos/imports');
        $fullPath = storage_path('app/' . $path);
        $svc = new ProfilePhotoBackupService();
        $ok = $svc->import($fullPath);

        if ($ok) {
            return back()->with('success', 'Import foto profil selesai');
        }

        return back()->withErrors('Gagal mengimpor backup foto profil');
    }

    public function backupUpdateSettings(Request $request)
    {
        $v = $request->validate([
            'enabled' => 'nullable|boolean',
            'time' => 'nullable|date_format:H:i',
            'format' => 'nullable|in:sql,zip',
        ]);
        $settings = new SettingsManager();
        $settings->set('backup.enabled', (bool) ($v['enabled'] ?? false));
        if (isset($v['time'])) $settings->set('backup.time', $v['time']);
        if (isset($v['format'])) $settings->set('backup.format', $v['format']);
        return back()->with('success','Pengaturan backup disimpan');
    }

    public function updateHeader(Request $request)
    {
        \Log::info('updateHeader called');
        \Log::info('Request data:', $request->all());
        \Log::info('Has logo_header_kiri file:', ['has' => $request->hasFile('logo_header_kiri')]);
        \Log::info('Has logo file:', ['has' => $request->hasFile('logo')]);
        
        $validated = $request->validate([
            'header_html' => 'nullable|string',
            'logo_header_kiri' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Header text lines (HTML from Summernote)
            'header_line1' => 'nullable|string',
            'header_line1_spacing' => 'nullable|numeric|min:0.1|max:5',
            'header_line2' => 'nullable|string',
            'header_line2_spacing' => 'nullable|numeric|min:0.1|max:5',
            'header_line3' => 'nullable|string',
            'header_line3_spacing' => 'nullable|numeric|min:0.1|max:5',
            'header_line4' => 'nullable|string',
            'header_line4_spacing' => 'nullable|numeric|min:0.1|max:5',
        ]);

        try {
            \Log::info('Validation passed');
            $sekolah = \App\Models\Sekolah::first();
            
            if (!$sekolah) {
                $sekolah = new \App\Models\Sekolah();
                // Set required fields untuk record baru
                $sekolah->nama_sekolah = config('app.name', 'SEKOLAH');
                $sekolah->kota = 'Banten';
                $sekolah->provinsi = 'Banten';
                $sekolah->alamat = '-';
            }

            $sekolah->header_html = $validated['header_html'] ?? null;

            // Save header text lines as HTML from Summernote
            $sekolah->header_line1 = $validated['header_line1'] ?? null;
            $sekolah->header_line1_spacing = $validated['header_line1_spacing'] ?? 1.0;
            $sekolah->header_line2 = $validated['header_line2'] ?? null;
            $sekolah->header_line2_spacing = $validated['header_line2_spacing'] ?? 1.0;
            $sekolah->header_line3 = $validated['header_line3'] ?? null;
            $sekolah->header_line3_spacing = $validated['header_line3_spacing'] ?? 1.0;
            $sekolah->header_line4 = $validated['header_line4'] ?? null;
            $sekolah->header_line4_spacing = $validated['header_line4_spacing'] ?? 1.0;

            // Handle logo_header_kiri
            if ($request->hasFile('logo_header_kiri')) {
                \Log::info('Processing logo_header_kiri upload');
                if ($sekolah->logo_header_kiri && \Illuminate\Support\Facades\Storage::disk('public')->exists($sekolah->logo_header_kiri)) {
                    \Log::info('Deleting old logo_header_kiri: ' . $sekolah->logo_header_kiri);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($sekolah->logo_header_kiri);
                }
                try {
                    $file = $request->file('logo_header_kiri');
                    $path = $file->store('logos', 'public');
                    \Log::info('Logo kiri stored at: ' . $path);
                    $sekolah->logo_header_kiri = $path;
                } catch (\Exception $ex) {
                    \Log::error('Error storing logo_header_kiri: ' . $ex->getMessage());
                    throw $ex;
                }
            }

            // Handle logo (school logo)
            if ($request->hasFile('logo')) {
                \Log::info('Processing logo upload');
                if ($sekolah->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($sekolah->logo)) {
                    \Log::info('Deleting old logo: ' . $sekolah->logo);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($sekolah->logo);
                }
                try {
                    $file = $request->file('logo');
                    $path = $file->store('logos', 'public');
                    \Log::info('Logo stored at: ' . $path);
                    $sekolah->logo = $path;
                } catch (\Exception $ex) {
                    \Log::error('Error storing logo: ' . $ex->getMessage());
                    throw $ex;
                }
            }

            \Log::info('Saving sekolah record', ['logo' => $sekolah->logo, 'logo_header_kiri' => $sekolah->logo_header_kiri]);
            $sekolah->save();
            \Log::info('Sekolah saved successfully');

            return redirect()->route('setting.header')
                ->with('success', 'Header berhasil disimpan');
        } catch (\Exception $e) {
            \Log::error('Error updating header: ' . $e->getMessage());
            return back()
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }


}