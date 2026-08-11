<?php

namespace App\Http\Controllers;

use App\Models\ModulAjarDocument;
use App\Models\ModulAjarDocumentVersion;
use App\Models\RencanaPembelajaran;
use App\Models\TugasGuru;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Services\SettingsManager;

class CollaboraController extends Controller
{
    private function getCollaboraServerUrl(): string
    {
        $settings = new SettingsManager();

        // Prefer the value stored in SettingsManager (UI) so runtime updates via
        // settings.json take effect without changing env/config.
        $fromSettings = $settings->get('collabora.url');
        if (!empty($fromSettings)) {
            return (string) $fromSettings;
        }

        return (string) (
            env('COLLABORA_URL')
            ?: config('services.collabora.url')
            ?: 'https://collabora.sman1-pontang.sch.id'
        );
    }

    private function getTemplatePath(): string
    {
        return storage_path('app/templates/template_modul_ajar.docx');
    }

    private function getTempDirectory(): string
    {
        return public_path('uploads/rencana_pembelajaran/docx/temp');
    }

    private function getTempFilePath(string $tempKey): string
    {
        $tempKey = preg_replace('/[^A-Za-z0-9_\-]/', '', $tempKey);

        if (str_starts_with($tempKey, 'editor_')) {
            return public_path('uploads/editor-modul/temp/' . $tempKey . '.docx');
        }

        return $this->getTempDirectory() . DIRECTORY_SEPARATOR . $tempKey . '.docx';
    }

    private function getWopiSrc(string $tempKey): string
    {
        $settings = new SettingsManager();

        $fromSettings = $settings->get('collabora.wopi_host');
        if (!empty($fromSettings)) {
            $host = (string) $fromSettings;
        } else {
            $host = (string) (
                env('COLLABORA_WOPI_HOST')
                ?: config('services.collabora.wopi_host')
                ?: url('/')
            );
        }

        return rtrim($host, '/') . '/collabora/wopi/files/' . $tempKey;
    }

    private function getLockCacheKey(string $tempKey): string
    {
        return 'wopi_lock_' . $tempKey;
    }

    private function getRequestLock(Request $request): string
    {
        return trim((string) $request->header('X-WOPI-Lock', ''));
    }

    private function shouldRequireWopiToken(string $tempKey): bool
    {
        return str_starts_with($tempKey, 'modulajar_');
    }

    private function getWopiTokenCacheKey(string $token): string
    {
        return 'wopi_token_' . sha1($token);
    }

    public function createWopiAccessToken(
        string $tempKey,
        int $moduleId,
        int $userId,
        string $permission = 'edit',
        int $ttlMinutes = 120
    ): string {
        $permission = $permission === 'preview' ? 'preview' : 'edit';
        $token = Str::random(48);
        $expiresAt = now()->addMinutes(max(1, $ttlMinutes));

        Cache::put(
            $this->getWopiTokenCacheKey($token),
            [
                'temp_key' => $tempKey,
                'module_id' => $moduleId,
                'user_id' => $userId,
                'permission' => $permission,
                'expires_at' => $expiresAt->toIso8601String(),
            ],
            $expiresAt
        );

        Cache::put(
            'wopi_temp_context_' . $tempKey,
            [
                'module_id' => $moduleId,
                'user_id' => $userId,
                'permission' => $permission,
            ],
            $expiresAt
        );

        return $token;
    }

    private function resolveWopiAuth(Request $request, string $tempKey): array
    {
        $token = trim((string) $request->query('access_token', ''));

        if ($token === '') {
            if ($this->shouldRequireWopiToken($tempKey)) {
                return [
                    'ok' => false,
                    'response' => response()->json([
                        'error' => 'WOPI token is required',
                    ], 401),
                ];
            }

            return [
                'ok' => true,
                'payload' => [
                    'module_id' => null,
                    'user_id' => auth()->id(),
                    'permission' => 'edit',
                ],
            ];
        }

        $payload = Cache::get($this->getWopiTokenCacheKey($token));
        if (!is_array($payload)) {
            return [
                'ok' => false,
                'response' => response()->json([
                    'error' => 'Invalid WOPI token',
                ], 401),
            ];
        }

        if (($payload['temp_key'] ?? '') !== $tempKey) {
            return [
                'ok' => false,
                'response' => response()->json([
                    'error' => 'WOPI token file mismatch',
                ], 403),
            ];
        }

        $expiresAt = isset($payload['expires_at']) ? strtotime((string) $payload['expires_at']) : false;
        if ($expiresAt === false || $expiresAt < time()) {
            return [
                'ok' => false,
                'response' => response()->json([
                    'error' => 'WOPI token expired',
                ], 401),
            ];
        }

        return [
            'ok' => true,
            'payload' => $payload,
        ];
    }

    public function buildWopiUrl(string $tempKey, string $mode = 'edit', ?string $token = null): string
    {
        $editorMode = $mode === 'view' ? 'view' : 'edit';

        $url = rtrim($this->getCollaboraServerUrl(), '/')
            . '/browser/2229109277/cool.html?WOPISrc=' . urlencode($this->getWopiSrc($tempKey))
            . '&lang=id&mode=' . $editorMode;

        if ($token) {
            $url .= '&access_token=' . urlencode($token)
                . '&access_token_ttl=' . (time() + 7200);
        }

        return $url;
    }

    public function index()
    {
        $tempKey = session('collabora_temp_key');
        if (!$tempKey) {
            $tempKey = uniqid('doc_');
            session(['collabora_temp_key' => $tempKey]);
        }

        $wopiUrl = $this->buildWopiUrl($tempKey, 'edit');

        Log::info('Collabora editor initialized', [
            'tempKey' => $tempKey,
            'wopiUrl' => $wopiUrl,
        ]);

        return view('akademik.tool', [
            'wopiUrl' => $wopiUrl,
            'tempKey' => $tempKey,
        ]);
    }

    public function checkFileInfo(Request $request, string $tempKey)
    {
        $auth = $this->resolveWopiAuth($request, $tempKey);
        if (($auth['ok'] ?? false) !== true) {
            return $auth['response'];
        }
        $authPayload = $auth['payload'] ?? [];

        $tempPath = $this->getTempFilePath($tempKey);
        $filePath = file_exists($tempPath) ? $tempPath : $this->getTemplatePath();

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return response()->json(['Error' => 'File not found'], 404);
        }

        $fileSize = filesize($filePath);
        if ($fileSize === false || $fileSize <= 0) {
            return response()->json(['Error' => 'Empty document'], 500);
        }

        $userId = isset($authPayload['user_id'])
            ? (string) $authPayload['user_id']
            : (auth()->check() ? (string) auth()->id() : 'guest');

        $userName = auth()->check()
            ? (auth()->user()->name ?? 'User')
            : 'Guest';

        $version = md5($filePath . '|' . filemtime($filePath) . '|' . $fileSize);
        $canWrite = ($authPayload['permission'] ?? 'edit') === 'edit';

        return response()->json([
            'BaseFileName' => basename($filePath),
            'Size' => $fileSize,
            'Version' => $version,
            'OwnerId' => (string) ($authPayload['module_id'] ?? $userId),
            'UserId' => $userId,
            'UserFriendlyName' => $userName,
            'UserCanWrite' => $canWrite,
            'UserCanNotWriteRelative' => true,
            'SupportsUpdate' => true,
            'SupportsRename' => false,
            'SupportsDelete' => false,
            'SupportsCreate' => false,
            'SupportsLocks' => true,
            'SupportsGetLock' => true,
            'SupportsExtendedLockLength' => true,
            'PostMessageOrigin' => url('/'),
            'TemplateSaveAs' => false,
            'MoveDisabled' => true,
            'DownloadAsPostMessage' => false,
            'BreadcrumbDocName' => 'Modul Ajar',
            'BreadcrumbFolderUrl' => url('/modul-ajar'),
        ])->header('X-WOPI-SessionId', $tempKey);
    }

    public function getFile(Request $request, string $tempKey)
    {
        $auth = $this->resolveWopiAuth($request, $tempKey);
        if (($auth['ok'] ?? false) !== true) {
            return $auth['response'];
        }

        $tempPath = $this->getTempFilePath($tempKey);
        $filePath = file_exists($tempPath) ? $tempPath : $this->getTemplatePath();

        if (!file_exists($filePath) || !is_readable($filePath)) {
            return response('File not found', 404);
        }

        $fileSize = filesize($filePath);
        if ($fileSize === false || $fileSize <= 0) {
            return response('Empty document', 500);
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
            'Content-Length' => $fileSize,
        ]);
    }

    public function putFile(Request $request, string $tempKey)
    {
        $auth = $this->resolveWopiAuth($request, $tempKey);
        if (($auth['ok'] ?? false) !== true) {
            return $auth['response'];
        }

        $override = strtoupper(trim((string) $request->header('X-WOPI-Override', '')));

        if ($override === 'LOCK') {
            return $this->handleLock($request, $tempKey);
        }
        if ($override === 'GET_LOCK') {
            return $this->handleGetLock($tempKey);
        }
        if ($override === 'REFRESH_LOCK') {
            return $this->handleRefreshLock($request, $tempKey);
        }
        if ($override === 'UNLOCK') {
            return $this->handleUnlock($request, $tempKey);
        }

        return $this->putFileContents($request, $tempKey);
    }

    public function putFileContents(Request $request, string $tempKey)
    {
        $auth = $this->resolveWopiAuth($request, $tempKey);
        if (($auth['ok'] ?? false) !== true) {
            return $auth['response'];
        }
        $authPayload = $auth['payload'] ?? [];

        if (($authPayload['permission'] ?? 'edit') !== 'edit') {
            return response()->json(['error' => 'Read-only token cannot update file'], 403);
        }

        $cacheKey = $this->getLockCacheKey($tempKey);
        $currentLock = Cache::get($cacheKey);
        $requestLock = $this->getRequestLock($request);

        if ($currentLock !== null) {
            if ($requestLock === '') {
                return response('Missing X-WOPI-Lock', 409)->header('X-WOPI-Lock', (string) $currentLock);
            }
            if (!hash_equals((string) $currentLock, (string) $requestLock)) {
                return response('Lock mismatch', 409)->header('X-WOPI-Lock', (string) $currentLock);
            }
        }

        $content = $request->getContent();
        $contentLength = strlen($content);
        if ($content === false || $contentLength === 0) {
            return response()->json(['Error' => 'Empty document content'], 400);
        }

        $tempDir = $this->getTempDirectory();
        if (!is_dir($tempDir) && !mkdir($tempDir, 0755, true) && !is_dir($tempDir)) {
            return response()->json(['Error' => 'Cannot create temp directory'], 500);
        }
        if (!is_writable($tempDir)) {
            return response()->json(['Error' => 'Temp directory is not writable'], 500);
        }

        $filePath = $this->getTempFilePath($tempKey);
        $written = file_put_contents($filePath, $content, LOCK_EX);
        if ($written === false) {
            return response()->json(['Error' => 'Failed to save document'], 500);
        }

        $fileSize = filesize($filePath);
        if ($fileSize === false || $fileSize <= 0) {
            return response()->json(['Error' => 'Saved document is empty'], 500);
        }

        try {
            $this->persistModuleDocumentFromTemp($filePath, (int) $fileSize, $authPayload);
        } catch (\Throwable $e) {
            Log::error('Gagal sinkron dokumen hasil WOPI ke modul ajar', [
                'tempKey' => $tempKey,
                'error' => $e->getMessage(),
            ]);
        }

        $itemVersion = md5($filePath . '|' . filemtime($filePath) . '|' . $fileSize);

        return response('', 200)->header('X-WOPI-ItemVersion', $itemVersion);
    }

    private function persistModuleDocumentFromTemp(string $filePath, int $fileSize, array $authPayload): void
    {
        $moduleId = (int) ($authPayload['module_id'] ?? 0);
        $userId = (int) ($authPayload['user_id'] ?? 0);
        if ($moduleId <= 0 || $userId <= 0) {
            return;
        }

        $module = RencanaPembelajaran::find($moduleId);
        if (!$module) {
            return;
        }

        DB::transaction(function () use ($module, $filePath, $fileSize, $userId) {
            $document = ModulAjarDocument::where('modul_ajar_id', $module->id)
                ->lockForUpdate()
                ->first();

            $nextVersion = ($document?->version ?? 0) + 1;
            $baseName = Str::slug($module->judul ?: 'modul-ajar');
            if ($baseName === '') {
                $baseName = 'modul-ajar';
            }

            $filename = $baseName . '_v' . $nextVersion . '.docx';
            $relativePath = 'modul-ajar/' . $module->id . '/' . $filename;

            $binary = file_get_contents($filePath);
            if ($binary === false) {
                throw new \RuntimeException('Gagal membaca temp file dari WOPI.');
            }

            if (!Storage::disk('public')->put($relativePath, $binary)) {
                throw new \RuntimeException('Gagal menyimpan file hasil WOPI ke storage.');
            }

            $payload = [
                'original_filename' => $module->judul ? Str::slug($module->judul) . '.docx' : 'modul_ajar.docx',
                'filename' => $filename,
                'filepath' => $relativePath,
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'file_size' => $fileSize,
                'version' => $nextVersion,
                'uploaded_by' => $userId,
            ];

            if ($document) {
                $document->update($payload);
            } else {
                ModulAjarDocument::create(array_merge($payload, [
                    'modul_ajar_id' => $module->id,
                ]));
            }

            ModulAjarDocumentVersion::create([
                'modul_ajar_id' => $module->id,
                'filename' => $filename,
                'filepath' => $relativePath,
                'version' => $nextVersion,
                'file_size' => $fileSize,
                'created_by' => $userId,
                'created_at' => now(),
            ]);

            $module->update([
                'original_docx_path' => 'storage/' . $relativePath,
            ]);
        });
    }

    private function handleLock(Request $request, string $tempKey)
    {
        $newLock = $this->getRequestLock($request);
        if ($newLock === '') {
            return response('Missing X-WOPI-Lock', 400);
        }

        $cacheKey = $this->getLockCacheKey($tempKey);
        $currentLock = Cache::get($cacheKey);

        if ($currentLock === null) {
            Cache::forever($cacheKey, $newLock);
            return response('', 200)->header('X-WOPI-Lock', $newLock);
        }

        if (hash_equals((string) $currentLock, (string) $newLock)) {
            return response('', 200)->header('X-WOPI-Lock', (string) $currentLock);
        }

        return response('Lock conflict', 409)->header('X-WOPI-Lock', (string) $currentLock);
    }

    private function handleGetLock(string $tempKey)
    {
        $currentLock = Cache::get($this->getLockCacheKey($tempKey));
        return response('', 200)->header('X-WOPI-Lock', (string) ($currentLock ?? ''));
    }

    private function handleRefreshLock(Request $request, string $tempKey)
    {
        $requestLock = $this->getRequestLock($request);
        if ($requestLock === '') {
            return response('Missing X-WOPI-Lock', 400);
        }

        $cacheKey = $this->getLockCacheKey($tempKey);
        $currentLock = Cache::get($cacheKey);

        if ($currentLock === null) {
            return response('Lock not found', 409);
        }

        if (!hash_equals((string) $currentLock, (string) $requestLock)) {
            return response('Lock conflict', 409)->header('X-WOPI-Lock', (string) $currentLock);
        }

        Cache::forever($cacheKey, $currentLock);
        return response('', 200)->header('X-WOPI-Lock', (string) $currentLock);
    }

    private function handleUnlock(Request $request, string $tempKey)
    {
        $requestLock = $this->getRequestLock($request);
        if ($requestLock === '') {
            return response('Missing X-WOPI-Lock', 400);
        }

        $cacheKey = $this->getLockCacheKey($tempKey);
        $currentLock = Cache::get($cacheKey);

        if ($currentLock === null) {
            return response('', 200);
        }

        if (!hash_equals((string) $currentLock, (string) $requestLock)) {
            return response('Lock conflict', 409)->header('X-WOPI-Lock', (string) $currentLock);
        }

        Cache::forget($cacheKey);
        return response('', 200);
    }

    public function lock(Request $request, string $tempKey)
    {
        $auth = $this->resolveWopiAuth($request, $tempKey);
        if (($auth['ok'] ?? false) !== true) {
            return $auth['response'];
        }

        return $this->handleLock($request, $tempKey);
    }

    public function refreshLock(Request $request, string $tempKey)
    {
        $auth = $this->resolveWopiAuth($request, $tempKey);
        if (($auth['ok'] ?? false) !== true) {
            return $auth['response'];
        }

        return $this->handleRefreshLock($request, $tempKey);
    }

    public function unlock(Request $request, string $tempKey)
    {
        $auth = $this->resolveWopiAuth($request, $tempKey);
        if (($auth['ok'] ?? false) !== true) {
            return $auth['response'];
        }

        return $this->handleUnlock($request, $tempKey);
    }

    public function modulIndex()
    {
        $user = auth()->user();
        $query = RencanaPembelajaran::with(['document', 'documentVersions'])
            ->orderByDesc('created_at');

        if ($user && !$user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Pengawas Pembina']) && $user->guru_id) {
            $query->where('guru_id', $user->guru_id);
        }

        $modules = $query->get();

        if ($user && $user->guru_id) {
            $tugas = TugasGuru::with(['mataPelajaran','kelas'])->where('guru_id', $user->guru_id)->get();
            $mataPelajaranList = $tugas->pluck('mataPelajaran')->filter()->unique('id')->values();
            $kelasList = $tugas->pluck('kelas')->filter()->unique('id')->values();
        } else {
            $mataPelajaranList = MataPelajaran::orderBy('nama_mapel')->get();
            $kelasList = Kelas::orderBy('nama_kelas')->get();
        }

        return view('editor_modul.index', compact('modules', 'mataPelajaranList', 'kelasList'));
    }

    public function modulEdit($id)
    {
        $module = RencanaPembelajaran::findOrFail($id);

        if ($module->isCreatedViaModulAjar()) {
            return redirect()->route('rencana_pembelajaran.edit', $module->id)
                ->with('info', 'Modul ini dibuat melalui halaman Modul Ajar. Silakan edit menggunakan editor Modul Ajar.');
        }

        $tempKey = 'editor_edit_' . $module->id;
        $tempDir = public_path('uploads/editor-modul/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $editorPath = $tempDir . '/' . $tempKey . '.docx';
        $previewFiles = glob($tempDir . '/preview_' . $module->id . '_*.docx');

        if (!file_exists($editorPath) || filesize($editorPath) <= 0) {
            $activeDocument = $module->document;
            if ($activeDocument && !empty($activeDocument->filepath)) {
                $sourcePath = Storage::disk('public')->path($activeDocument->filepath);
                if (file_exists($sourcePath) && is_readable($sourcePath)) {
                    copy($sourcePath, $editorPath);
                }
            }

            if (!file_exists($editorPath) || filesize($editorPath) <= 0) {
                if (!empty($previewFiles)) {
                    copy($previewFiles[0], $editorPath);
                } else {
                    $storedPreviewFiles = Storage::disk('public')->files('modul-ajar/' . $module->id . '/preview');
                    usort($storedPreviewFiles, function ($a, $b) {
                        return Storage::disk('public')->lastModified($b) <=> Storage::disk('public')->lastModified($a);
                    });

                    if (!empty($storedPreviewFiles)) {
                        $latestPreview = Storage::disk('public')->path($storedPreviewFiles[0]);
                        if (file_exists($latestPreview)) {
                            copy($latestPreview, $editorPath);
                        }
                    }
                }
            }

            if (!file_exists($editorPath) || filesize($editorPath) <= 0) {
                if ($module->original_docx_path && file_exists(public_path($module->original_docx_path))) {
                    copy(public_path($module->original_docx_path), $editorPath);
                } else {
                    $templatePath = $this->getTemplatePath();
                    if (file_exists($templatePath)) {
                        copy($templatePath, $editorPath);
                    } else {
                        $tempKey = null;
                    }
                }
            }
        }

        $wopiUrl = $tempKey ? $this->buildWopiUrl($tempKey) : null;

        return view('editor_modul.edit', compact('module', 'wopiUrl', 'tempKey'));
    }
}
