<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use App\Models\TugasGuru;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\CapaianPembelajaran;
use App\Models\Sekolah;
use App\Models\KepalaSekolah;
use App\Models\RencanaPembelajaran;
use App\Models\ModulAjarDocument;
use App\Models\ModulAjarDocumentVersion;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
// show/download removed to restore original WYSIWYG flow

class RencanaPembelajaranController extends Controller
{
    /**
     * Fields that hold rich HTML content from the editor
     */
    private array $richFields = [
        'achievement','objectives','methods','media','resources','practice','environment','digital','experience','reflection','assessment',
            'dimensi_lulusan',
    ];

    /**
     * Decode & sanitize HTML coming from session or form
     */
    private function cleanHtml(string $html = null): string
    {
        if (empty($html)) return '';
        // decode HTML entities
        $decoded = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // basic sanitization: remove dangerous tags but keep common formatting
        $allowed = '<p><br><strong><b><em><i><ul><ol><li><blockquote><code><img>';
        $clean = strip_tags($decoded, $allowed);
        // normalize &nbsp;
        $clean = str_replace("\u00A0", ' ', $clean);
        // sanitize img tags: keep only safe attributes
        $clean = preg_replace_callback('/<img\s+[^>]*>/i', function ($matches) {
            $tag = $matches[0];
            $tag = preg_replace('/\s?on\w+\s*=\s*("|\').*?\1/i', '', $tag);
            $tag = preg_replace('/\s?href\s*=\s*("|\').*?\1/i', '', $tag);
            return $tag;
        }, $clean);
        return trim($clean);
    }
    private function resolveCurrentGuruId(): ?int
    {
        $user = Auth::user();
        if ($user && $user->guru_id) {
            return (int) $user->guru_id;
        }

        if ($user && $user->guru) {
            return (int) $user->guru->id;
        }

        return null;
    }

    private function buildModulePayloadFromModel(RencanaPembelajaran $model): array
    {
        $meta = [];
        if (!empty($model->html_content)) {
            $decoded = json_decode($model->html_content, true);
            if (is_array($decoded)) {
                $meta = $decoded;
            }
        }

        $title = $model->judul ?? $meta['title'] ?? null;
        $subject = $meta['subject'] ?? optional($model->mataPelajaran)->nama_mapel;
        $class = $meta['class'] ?? optional($model->kelas)->nama_kelas;
        $duration = $model->alokasi_waktu ?? $meta['duration'] ?? null;
        $status = $model->status ?? $meta['status'] ?? 'draft';

        return [
            'id' => $model->id,
            'title' => $title,
            'subject' => $subject,
            'mata_pelajaran_id' => $model->mata_pelajaran_id,
            'class' => $class,
            'kelas_id' => $model->kelas_id,
            'duration' => $duration,
            'status' => $status,
            'dimensi_lulusan' => $model->dimensi_lulusan ?? $meta['dimensi_lulusan'] ?? null,
            'achievement' => $model->capaian_pembelajaran ?? $meta['achievement'] ?? null,
            'objectives' => $model->tujuan ?? $meta['objectives'] ?? null,
            'methods' => $model->metode ?? $meta['methods'] ?? null,
            'media' => $model->media ?? $meta['media'] ?? null,
            'resources' => $model->sumber ?? $meta['resources'] ?? null,
            'assessment' => $model->penilaian ?? $meta['assessment'] ?? null,
            'practice' => $model->praktik_pedagogis ?? $meta['practice'] ?? null,
            'environment' => $model->lingkungan_pembelajaran ?? $meta['environment'] ?? null,
            'digital' => $model->pemanfaatan_digital ?? $meta['digital'] ?? null,
            'experience' => $model->pengalaman_pembelajaran ?? $meta['experience'] ?? null,
            'reflection' => $model->refleksi_pembelajaran ?? $meta['reflection'] ?? null,
            'docx_path' => $model->original_docx_path ?: null,
            'created_at' => $model->created_at?->toDateTimeString(),
            'source' => 'database',
            'guru_id' => $model->guru_id,
        ];
    }

    private function loadModulesForCurrentUser(array $sessionModules = []): array
    {
        $modules = [];
        $guruId = $this->resolveCurrentGuruId();

        if ($guruId) {
            $records = RencanaPembelajaran::where('guru_id', $guruId)
                ->orderByDesc('created_at')
                ->get();

            foreach ($records as $record) {
                $modules[$record->id] = $this->buildModulePayloadFromModel($record);
            }
        }

        foreach ($sessionModules as $key => $module) {
            $modules[$key] = array_merge($module, $modules[$key] ?? []);
        }

        return $modules;
    }

    private function findOwnedModuleOrFail(int $id): RencanaPembelajaran
    {
        $module = RencanaPembelajaran::with(['document', 'documentVersions'])
            ->findOrFail($id);

        $user = Auth::user();
        $guruId = $this->resolveCurrentGuruId();

        if ($user && !$user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
            if (!$guruId || (int) $module->guru_id !== (int) $guruId) {
                abort(403, 'Anda tidak memiliki akses ke modul ajar ini.');
            }
        }

        return $module;
    }

    private function isDocxValid(string $path): bool
    {
        $zip = new \ZipArchive();
        $opened = $zip->open($path);
        if ($opened !== true) {
            return false;
        }

        $hasDocumentXml = $zip->locateName('word/document.xml') !== false;
        $zip->close();

        return $hasDocumentXml;
    }

    private function saveModuleDocumentVersion(
        RencanaPembelajaran $module,
        string $sourcePath,
        string $originalFilename,
        string $mimeType,
        int $userId
    ): ModulAjarDocument {
        if (!file_exists($sourcePath) || !is_readable($sourcePath)) {
            throw new \RuntimeException('File sumber dokumen tidak ditemukan atau tidak dapat dibaca.');
        }

        $fileSize = filesize($sourcePath);
        if ($fileSize === false || $fileSize <= 0) {
            throw new \RuntimeException('Ukuran file dokumen tidak valid.');
        }

        return DB::transaction(function () use ($module, $sourcePath, $originalFilename, $mimeType, $userId, $fileSize) {
            $document = ModulAjarDocument::where('modul_ajar_id', $module->id)
                ->lockForUpdate()
                ->first();

            $nextVersion = ($document?->version ?? 0) + 1;
            $safeBase = Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME));
            if ($safeBase === '') {
                $safeBase = 'modul-ajar';
            }

            $filename = $safeBase . '_v' . $nextVersion . '.docx';
            $relativePath = 'modul-ajar/' . $module->id . '/' . $filename;

            $binary = file_get_contents($sourcePath);
            if ($binary === false) {
                throw new \RuntimeException('Gagal membaca binary dokumen.');
            }

            $saved = Storage::disk('public')->put($relativePath, $binary);
            if (!$saved) {
                throw new \RuntimeException('Gagal menyimpan dokumen ke storage.');
            }

            $payload = [
                'original_filename' => $originalFilename,
                'filename' => $filename,
                'filepath' => $relativePath,
                'mime_type' => $mimeType,
                'file_size' => $fileSize,
                'version' => $nextVersion,
                'uploaded_by' => $userId,
            ];

            if ($document) {
                $document->update($payload);
                $document->refresh();
            } else {
                $document = ModulAjarDocument::create(array_merge($payload, [
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

            return $document;
        });
    }

    private function bootstrapDocumentFromLegacyPath(RencanaPembelajaran $module): void
    {
        if ($module->document) {
            return;
        }

        if (empty($module->original_docx_path)) {
            return;
        }

        $legacyPath = public_path($module->original_docx_path);
        if (!file_exists($legacyPath) || !is_readable($legacyPath)) {
            return;
        }

        try {
            $this->saveModuleDocumentVersion(
                $module,
                $legacyPath,
                basename($legacyPath),
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                (int) (Auth::id() ?? 0)
            );
            $module->load(['document', 'documentVersions']);
        } catch (\Throwable $e) {
            Log::warning('Gagal bootstrap dokumen legacy modul ajar', [
                'module_id' => $module->id,
                'legacy_path' => $legacyPath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function index()
    {
        $sessionModules = Session::get('modul_ajar_items', []);
        $modules = $this->loadModulesForCurrentUser($sessionModules);

        // convert/clean any existing escaped HTML in session modules so preview shows formatted content
        if (!empty($modules) && is_array($modules)) {
            $changed = false;
            foreach ($modules as $key => $m) {
                foreach ($this->richFields as $f) {
                    if (isset($m[$f]) && is_string($m[$f])) {
                        $cleaned = $this->cleanHtml($m[$f]);
                        if ($cleaned !== $m[$f]) {
                            $modules[$key][$f] = $cleaned;
                            $changed = true;
                        }
                    }
                }
            }
            if ($changed) {
                Session::put('modul_ajar_items', $modules);
            }
        }

        $sekolah = Sekolah::first();
        $user = Auth::user();
        $guruName = null;
        $guruNip = null;
        if ($user && $user->guru) {
            $guruName = $user->guru->nama ?? $user->name;
            $guruNip = $user->guru->nip ?? $user->nip ?? null;
        } else {
            $guruName = $user->name ?? null;
            $guruNip = $user->nip ?? null;
        }

        // prefer KepalaSekolah table entry, fallback to sekolah.nama_kepala_sekolah
        $kepala = KepalaSekolah::orderBy('tanggal_mulai_jabatan', 'desc')->first();
        $kepalaName = $kepala->nama ?? $sekolah->nama_kepala_sekolah ?? null;
        $kepalaNip = $kepala->nip ?? null;

        [$editorMataPelajaranList, $editorKelasList] = $this->loadMataPelajaranAndKelas();
        $editorFaseOptions = $this->buildFaseOptions();

        return view('rencana_pembelajaran.index', [
            'modules' => $modules,
            'sekolah' => $sekolah,
            'guruName' => $guruName,
            'guruNip' => $guruNip,
            'kepalaName' => $kepalaName,
            'kepalaNip' => $kepalaNip,
            'editorMataPelajaranList' => $editorMataPelajaranList,
            'editorKelasList' => $editorKelasList,
            'editorFaseOptions' => $editorFaseOptions,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        $mataPelajaranList = collect();
        $kelasList = collect();

        if ($user && $user->guru_id) {
            $tugas = TugasGuru::with(['mataPelajaran','kelas'])->where('guru_id', $user->guru_id)->get();
            $mataPelajaranList = $tugas->pluck('mataPelajaran')->filter()->unique('id')->values();
            $kelasList = $tugas->pluck('kelas')->filter()->unique('id')->values();
        } else {
            $mataPelajaranList = MataPelajaran::orderBy('nama_mapel')->get();
            $kelasList = Kelas::orderBy('nama_kelas')->get();
        }

        // build fase options from capaian pembelajaran (komponen penilaian)
        $fases = CapaianPembelajaran::whereNotNull('fase')->orderBy('fase')->pluck('fase')->unique()->values();
        if ($fases->isEmpty()) {
            $faseOptions = [
                'E' => 'Fase E: Kelas 10 SMA',
                'F' => 'Fase F: Kelas 11–12 SMA',
            ];
        } else {
            $faseOptions = [];
            foreach ($fases as $f) {
                $label = 'Fase ' . $f;
                if ($f === 'E') $label .= ': Kelas 10 SMA';
                if ($f === 'F') $label .= ': Kelas 11–12 SMA';
                $faseOptions[$f] = $label;
            }
        }

        return view('rencana_pembelajaran.form', [
            'mode' => 'create',
            'moduleId' => null,
            'mataPelajaranList' => $mataPelajaranList,
            'kelasList' => $kelasList,
            'faseOptions' => $faseOptions,
            'selectedFase' => null,
            'tempKey' => null,
            'docInfo' => null,
            'docVersions' => collect(),
        ]);
    }

    public function edit($id)
    {
        $record = $this->findOwnedModuleOrFail((int) $id);
        $this->bootstrapDocumentFromLegacyPath($record);
        $module = $this->buildModulePayloadFromModel($record);

        $user = Auth::user();
        $mataPelajaranList = collect();
        $kelasList = collect();

        if ($user && $user->guru_id) {
            $tugas = TugasGuru::with(['mataPelajaran','kelas'])->where('guru_id', $user->guru_id)->get();
            $mataPelajaranList = $tugas->pluck('mataPelajaran')->filter()->unique('id')->values();
            $kelasList = $tugas->pluck('kelas')->filter()->unique('id')->values();
        } else {
            $mataPelajaranList = MataPelajaran::orderBy('nama_mapel')->get();
            $kelasList = Kelas::orderBy('nama_kelas')->get();
        }

        // build fase options from capaian pembelajaran (komponen penilaian)
        $fases = CapaianPembelajaran::whereNotNull('fase')->orderBy('fase')->pluck('fase')->unique()->values();
        if ($fases->isEmpty()) {
            $faseOptions = [
                'E' => 'Fase E: Kelas 10 SMA',
                'F' => 'Fase F: Kelas 11–12 SMA',
            ];
        } else {
            $faseOptions = [];
            foreach ($fases as $f) {
                $label = 'Fase ' . $f;
                if ($f === 'E') $label .= ': Kelas 10 SMA';
                if ($f === 'F') $label .= ': Kelas 11–12 SMA';
                $faseOptions[$f] = $label;
            }
        }

        // if module exists, clean its rich fields before sending to form
        if ($module && is_array($module)) {
            foreach ($this->richFields as $f) {
                if (isset($module[$f]) && is_string($module[$f])) {
                    $module[$f] = $this->cleanHtml($module[$f]);
                }
            }
        }

        $document = $record->document;
        $docInfo = null;
        if ($document) {
            $docInfo = [
                'original_filename' => $document->original_filename,
                'filename' => $document->filename,
                'filepath' => $document->filepath,
                'mime_type' => $document->mime_type,
                'file_size' => (int) $document->file_size,
                'version' => (int) $document->version,
                'updated_at' => optional($document->updated_at)->toDateTimeString(),
            ];
        }

        $docVersions = $record->documentVersions
            ->sortByDesc('version')
            ->take(20)
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'version' => (int) $item->version,
                    'filename' => $item->filename,
                    'filepath' => $item->filepath,
                    'file_size' => (int) $item->file_size,
                    'created_by' => $item->created_by,
                    'created_at' => optional($item->created_at)->toDateTimeString(),
                ];
            })
            ->values();

        return view('rencana_pembelajaran.form', [
            'mode' => 'edit',
            'moduleId' => $id,
            'module' => $module,
            'mataPelajaranList' => $mataPelajaranList,
            'kelasList' => $kelasList,
            'faseOptions' => $faseOptions,
            'selectedFase' => $module['fase'] ?? null,
            'docInfo' => $docInfo,
            'docVersions' => $docVersions,
        ]);
    }

    // show() and download() intentionally removed to restore original editor flow

    public function store(Request $request)
    {
        $allFields = [
            'title',
            'subject',
            'class',
            'fase',
            'status',
            'duration',
            'achievement',
            'objectives',
            'methods',
            'media',
            'resources',
            'practice',
            'environment',
            'digital',
            'experience',
            'reflection',
            'assessment',
            'dimensi_lulusan',
        ];

        $data = [];
        foreach ($allFields as $field) {
            $value = $request->input($field);
            if ($value !== null && $value !== '') {
                $data[$field] = $value;
            }
        }

        $mataPelajaranId = $request->input('mata_pelajaran_id');
        $kelasId = $request->input('kelas_id');

        if (empty($mataPelajaranId) && !empty($data['subject'])) {
            $subjectModel = MataPelajaran::where('nama_mapel', $data['subject'])->first();
            if ($subjectModel) {
                $mataPelajaranId = $subjectModel->id;
            }
        }

        if (empty($kelasId) && !empty($data['class'])) {
            $kelasModel = Kelas::where('nama_kelas', $data['class'])->first();
            if ($kelasModel) {
                $kelasId = $kelasModel->id;
            }
        }

        if (empty($mataPelajaranId) || empty($kelasId)) {
            return redirect()->back()->withInput()->with('error', 'Mata pelajaran dan kelas harus dipilih dari daftar.');
        }

        foreach ($this->richFields as $f) {
            if (isset($data[$f]) && is_string($data[$f])) {
                $data[$f] = $this->cleanHtml($data[$f]);
            }
        }

        $modules = Session::get('modul_ajar_items', []);
        $moduleId = $request->input('module_id');
        $guruId = $this->resolveCurrentGuruId();

        $record = null;
        if ($moduleId) {
            $record = RencanaPembelajaran::find($moduleId);
        }

        $tempKey = $request->input('temp_key');
        $relativePath = null;
        if ($tempKey) {
            $tempKey = preg_replace('/[^A-Za-z0-9_\-]/', '', $tempKey);
            $savedTempPath = public_path('uploads/rencana_pembelajaran/docx/temp/' . $tempKey . '.docx');
            if (file_exists($savedTempPath)) {
                $fileName = ($record?->id ? $record->id : uniqid('modul-ajar-')) . '.docx';
                $relativePath = $this->buildRoleAwareDocxPath($fileName);
                $publicPath = public_path($relativePath);
                if (!file_exists(dirname($publicPath))) {
                    mkdir(dirname($publicPath), 0755, true);
                }
                copy($savedTempPath, $publicPath);
            }
        }

        $payload = array_merge($data, [
            'subject' => $request->input('subject'),
            'class' => $request->input('class'),
            'fase' => $request->input('fase'),
            'status' => $request->input('status') ?: ($data['status'] ?? 'draft'),
            'duration' => $request->input('duration'),
        ]);

        if ($record) {
            $record->update([
                'guru_id' => $guruId ?? $record->guru_id,
                'mata_pelajaran_id' => $mataPelajaranId ?? $record->mata_pelajaran_id,
                'kelas_id' => $kelasId ?? $record->kelas_id,
                'judul' => $payload['title'] ?? $record->judul,
                'capaian_pembelajaran' => $payload['achievement'] ?? null,
                'tujuan' => $payload['objectives'] ?? null,
                'metode' => $payload['methods'] ?? null,
                'media' => $payload['media'] ?? null,
                'sumber' => $payload['resources'] ?? null,
                'penilaian' => $payload['assessment'] ?? null,
                'alokasi_waktu' => $payload['duration'] ?? null,
                'dimensi_lulusan' => $payload['dimensi_lulusan'] ?? null,
                'praktik_pedagogis' => $payload['practice'] ?? null,
                'lingkungan_pembelajaran' => $payload['environment'] ?? null,
                'pemanfaatan_digital' => $payload['digital'] ?? null,
                'pengalaman_pembelajaran' => $payload['experience'] ?? null,
                'refleksi_pembelajaran' => $payload['reflection'] ?? null,
                'status' => $payload['status'] ?? 'draft',
                'html_content' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'original_docx_path' => $relativePath ?? $record->original_docx_path,
            ]);
            $moduleId = $record->id;
        } else {
            $record = RencanaPembelajaran::create([
                'guru_id' => $guruId,
                'mata_pelajaran_id' => $mataPelajaranId,
                'kelas_id' => $kelasId,
                'judul' => $payload['title'] ?? 'Modul Ajar',
                'capaian_pembelajaran' => $payload['achievement'] ?? null,
                'tujuan' => $payload['objectives'] ?? null,
                'metode' => $payload['methods'] ?? null,
                'media' => $payload['media'] ?? null,
                'sumber' => $payload['resources'] ?? null,
                'penilaian' => $payload['assessment'] ?? null,
                'alokasi_waktu' => $payload['duration'] ?? null,
                'dimensi_lulusan' => $payload['dimensi_lulusan'] ?? null,
                'praktik_pedagogis' => $payload['practice'] ?? null,
                'lingkungan_pembelajaran' => $payload['environment'] ?? null,
                'pemanfaatan_digital' => $payload['digital'] ?? null,
                'pengalaman_pembelajaran' => $payload['experience'] ?? null,
                'refleksi_pembelajaran' => $payload['reflection'] ?? null,
                'status' => $payload['status'] ?? 'draft',
                'html_content' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'original_docx_path' => $relativePath,
            ]);
            $moduleId = $record->id;
        }

        $modules[$moduleId] = array_merge($modules[$moduleId] ?? [], [
            'id' => $moduleId,
            'title' => $payload['title'] ?? 'Modul Ajar',
            'subject' => $payload['subject'] ?? null,
            'class' => $payload['class'] ?? null,
            'duration' => $payload['duration'] ?? null,
            'status' => $payload['status'] ?? 'draft',
            'docx_path' => $relativePath ? $relativePath : ($modules[$moduleId]['docx_path'] ?? null),
            'created_at' => now()->toDateTimeString(),
            'source' => 'database',
            'guru_id' => $guruId,
        ]);

        Session::put('modul_ajar_items', $modules);

        try {
            $this->generateAndSaveDocxFromHtmlContent($record, $payload);
        } catch (\Throwable $e) {
            Log::warning('Gagal generate DOCX setelah store modul ajar', [
                'module_id' => $record->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()->route('rencana_pembelajaran.index')->with('success', 'Modul ajar berhasil disimpan ke akun guru dan database.');
    }

    private function generateAndSaveDocxFromHtmlContent(RencanaPembelajaran $record, array $payload): void
    {
        $htmlContent = $payload['html_content'] ?? $record->html_content ?? null;
        if (!is_string($htmlContent) || trim($htmlContent) === '') {
            return;
        }

        $decoded = json_decode($htmlContent, true);
        if (is_array($decoded)) {
            $bodyHtml = $decoded['content'] ?? '';
            if (empty(trim($bodyHtml))) {
                $bodyHtml = $this->buildHtmlPreviewBodyFromPayload($decoded);
            }
        } else {
            $bodyHtml = $htmlContent;
        }

        if (empty(trim($bodyHtml))) {
            return;
        }

        $tmpPath = tempnam(sys_get_temp_dir(), 'modul_ajar_');
        if ($tmpPath === false) {
            throw new \RuntimeException('Gagal membuat dokumen sementara.');
        }

        $tmpDocxPath = $tmpPath . '.docx';
        try {
            $this->renderHtmlPayloadToDocxFile($bodyHtml, $tmpDocxPath);
            $fileName = Str::slug($record->judul ?: 'modul-ajar') . '_' . $record->id . '_' . now()->format('Ymd_His') . '.docx';
            $requestMeta = [
                'original_filename' => $fileName,
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ];
            $this->saveModuleDocumentVersion(
                $record,
                $tmpDocxPath,
                $fileName,
                $requestMeta['mime_type'],
                (int) (Auth::id() ?? 0)
            );
        } finally {
            if (file_exists($tmpDocxPath)) {
                @unlink($tmpDocxPath);
            }
            if (file_exists($tmpPath)) {
                @unlink($tmpPath);
            }
        }
    }

    public function update(Request $request, int $id)
    {
        $record = $this->findOwnedModuleOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'kelas_id' => 'required|exists:kelas,id',
            'subject' => 'nullable|string|max:255',
            'class' => 'nullable|string|max:255',
            'fase' => 'nullable|string|max:25',
            'status' => 'required|in:draft,published',
            'duration' => 'nullable|string|max:255',
            'dimensi_lulusan' => 'nullable|string',
            'achievement' => 'nullable|string',
            'objectives' => 'nullable|string',
            'methods' => 'nullable|string',
            'media' => 'nullable|string',
            'resources' => 'nullable|string',
            'practice' => 'nullable|string',
            'environment' => 'nullable|string',
            'digital' => 'nullable|string',
            'experience' => 'nullable|string',
            'reflection' => 'nullable|string',
            'assessment' => 'nullable|string',
        ]);

        foreach ($this->richFields as $field) {
            if (isset($validated[$field]) && is_string($validated[$field])) {
                $validated[$field] = $this->cleanHtml($validated[$field]);
            }
        }

        $payload = [
            'title' => $validated['title'],
            'subject' => $validated['subject'] ?? null,
            'class' => $validated['class'] ?? null,
            'fase' => $validated['fase'] ?? null,
            'status' => $validated['status'],
            'duration' => $validated['duration'] ?? null,
            'achievement' => $validated['achievement'] ?? null,
            'objectives' => $validated['objectives'] ?? null,
            'methods' => $validated['methods'] ?? null,
            'media' => $validated['media'] ?? null,
            'resources' => $validated['resources'] ?? null,
            'practice' => $validated['practice'] ?? null,
            'environment' => $validated['environment'] ?? null,
            'digital' => $validated['digital'] ?? null,
            'experience' => $validated['experience'] ?? null,
            'reflection' => $validated['reflection'] ?? null,
            'assessment' => $validated['assessment'] ?? null,
            'dimensi_lulusan' => $validated['dimensi_lulusan'] ?? null,
        ];

        $record->update([
            'mata_pelajaran_id' => (int) $validated['mata_pelajaran_id'],
            'kelas_id' => (int) $validated['kelas_id'],
            'judul' => $validated['title'],
            'capaian_pembelajaran' => $payload['achievement'],
            'tujuan' => $payload['objectives'],
            'metode' => $payload['methods'],
            'media' => $payload['media'],
            'sumber' => $payload['resources'],
            'penilaian' => $payload['assessment'],
            'alokasi_waktu' => $payload['duration'],
            'dimensi_lulusan' => $payload['dimensi_lulusan'],
            'praktik_pedagogis' => $payload['practice'],
            'lingkungan_pembelajaran' => $payload['environment'],
            'pemanfaatan_digital' => $payload['digital'],
            'pengalaman_pembelajaran' => $payload['experience'],
            'refleksi_pembelajaran' => $payload['reflection'],
            'status' => $payload['status'],
            'html_content' => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        $record->refresh();
        $record->html_content = json_encode($payload, JSON_UNESCAPED_UNICODE);

        try {
            $this->generateAndSaveDocxFromHtmlContent($record, $payload);
        } catch (\Throwable $e) {
            Log::warning('Gagal render DOCX pada simpan modul ajar edit', [
                'module_id' => $record->id,
                'error' => $e->getMessage(),
            ]);
        }

        return redirect()
            ->route('rencana_pembelajaran.edit', $record->id)
            ->with('success', 'Data modul ajar berhasil diperbarui.');
    }

    public function uploadDocument(Request $request, int $id)
    {
        $module = $this->findOwnedModuleOrFail($id);

        $request->validate([
            'document' => 'required|file|max:10240|mimes:doc,docx',
        ]);

        $file = $request->file('document');
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mimeType = (string) $file->getClientMimeType();

        if (!in_array($extension, ['doc', 'docx'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Format file tidak didukung.',
            ], 422);
        }

        if ($extension === 'docx' && !$this->isDocxValid($file->getRealPath())) {
            return response()->json([
                'success' => false,
                'message' => 'File DOCX terdeteksi rusak/corrupt.',
            ], 422);
        }

        $tmpPath = $file->getRealPath();
        if (!$tmpPath) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file upload.',
            ], 500);
        }

        try {
            $document = $this->saveModuleDocumentVersion(
                $module,
                $tmpPath,
                (string) $file->getClientOriginalName(),
                $mimeType,
                (int) Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil diupload',
                'document' => [
                    'original_filename' => $document->original_filename,
                    'filename' => $document->filename,
                    'filepath' => $document->filepath,
                    'mime_type' => $document->mime_type,
                    'file_size' => (int) $document->file_size,
                    'version' => (int) $document->version,
                    'updated_at' => optional($document->updated_at)->toDateTimeString(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Upload dokumen modul ajar gagal', [
                'module_id' => $module->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload dokumen gagal.',
            ], 500);
        }
    }

    public function storeFromUpload(Request $request)
    {
        $guruId = $this->resolveCurrentGuruId();
        if (!$guruId) {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses membuat modul ajar.');
        }

        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'kelas_id' => 'required|exists:kelas,id',
            'title' => 'nullable|string|max:255',
            'document' => 'required|file|max:10240|mimes:doc,docx',
        ]);

        $file = $request->file('document');
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $mimeType = (string) $file->getClientMimeType();

        if ($extension === 'docx' && !$this->isDocxValid($file->getRealPath())) {
            return redirect()->back()->withErrors(['document' => 'File DOCX terdeteksi rusak atau corrupt.']);
        }

        $module = RencanaPembelajaran::create([
            'guru_id' => $guruId,
            'mata_pelajaran_id' => (int) $request->input('mata_pelajaran_id'),
            'kelas_id' => (int) $request->input('kelas_id'),
            'judul' => $request->input('title') ?: 'Modul Ajar Baru',
            'status' => 'draft',
            'html_content' => null,
            'original_docx_path' => null,
        ]);

        try {
            $this->saveModuleDocumentVersion(
                $module,
                $file->getRealPath(),
                (string) $file->getClientOriginalName(),
                $mimeType,
                (int) Auth::id()
            );
        } catch (\Throwable $e) {
            Log::error('Gagal membuat modul ajar dari upload', [
                'error' => $e->getMessage(),
                'guru_id' => $guruId,
            ]);
            $module->delete();
            return redirect()->back()->with('error', 'Gagal membuat modul ajar dari upload.');
        }

        return redirect()->route('akademik.editor_modul.edit', $module->id)
            ->with('success', 'Modul ajar baru berhasil dibuat dari file upload.');
    }

    public function preview(Request $request, int $id)
    {
        $module = $this->findOwnedModuleOrFail($id);
        $this->bootstrapDocumentFromLegacyPath($module);
        $mode = $request->query('mode', 'preview') === 'edit' ? 'edit' : 'preview';
        $permission = $mode === 'edit' ? 'edit' : 'preview';

        $document = $module->document;
        if (!$document || empty($document->filepath)) {
            return response()->json([
                'success' => false,
                'message' => 'Belum ada dokumen Modul Ajar',
            ], 404);
        }

        $sourcePath = Storage::disk('public')->path($document->filepath);
        if (!file_exists($sourcePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File dokumen tidak ditemukan di storage.',
            ], 404);
        }

        $tempDir = public_path('uploads/rencana_pembelajaran/docx/temp');
        if (!is_dir($tempDir) && !mkdir($tempDir, 0755, true) && !is_dir($tempDir)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyiapkan direktori temporary.',
            ], 500);
        }

        $tempKey = 'modulajar_' . $module->id . '_' . time() . '_' . Str::random(6);
        $tempPath = $tempDir . '/' . $tempKey . '.docx';
        if (!copy($sourcePath, $tempPath)) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyiapkan file preview/edit.',
            ], 500);
        }

        $collabora = app(\App\Http\Controllers\CollaboraController::class);
        $token = $collabora->createWopiAccessToken(
            $tempKey,
            $module->id,
            (int) Auth::id(),
            $permission,
            120
        );

        $wopiUrl = $collabora->buildWopiUrl(
            $tempKey,
            $mode === 'edit' ? 'edit' : 'view',
            $token
        );

        return response()->json([
            'success' => true,
            'mode' => $mode,
            'tempKey' => $tempKey,
            'wopiUrl' => $wopiUrl,
        ]);
    }

    public function versions(int $id)
    {
        $module = $this->findOwnedModuleOrFail($id);

        $items = ModulAjarDocumentVersion::where('modul_ajar_id', $module->id)
            ->orderByDesc('version')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'version' => (int) $item->version,
                    'filename' => $item->filename,
                    'filepath' => $item->filepath,
                    'file_size' => (int) $item->file_size,
                    'created_by' => $item->created_by,
                    'created_at' => optional($item->created_at)->toDateTimeString(),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'versions' => $items,
        ]);
    }

    public function restoreVersion(Request $request, int $id, int $version)
    {
        $module = $this->findOwnedModuleOrFail($id);

        $versionRow = ModulAjarDocumentVersion::where('modul_ajar_id', $module->id)
            ->where('version', $version)
            ->first();

        if (!$versionRow) {
            return response()->json([
                'success' => false,
                'message' => 'Versi dokumen tidak ditemukan.',
            ], 404);
        }

        $sourcePath = Storage::disk('public')->path($versionRow->filepath);
        if (!file_exists($sourcePath)) {
            return response()->json([
                'success' => false,
                'message' => 'File versi dokumen tidak ditemukan.',
            ], 404);
        }

        try {
            $document = $this->saveModuleDocumentVersion(
                $module,
                $sourcePath,
                $versionRow->filename,
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                (int) Auth::id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Versi berhasil di-restore.',
                'document' => [
                    'original_filename' => $document->original_filename,
                    'version' => (int) $document->version,
                    'filename' => $document->filename,
                    'file_size' => (int) $document->file_size,
                    'updated_at' => optional($document->updated_at)->toDateTimeString(),
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('Restore versi dokumen modul ajar gagal', [
                'module_id' => $module->id,
                'target_version' => $version,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Restore versi gagal.',
            ], 500);
        }
    }

    private function resolveRoleAwareStorageSegment(): string
    {
        $user = Auth::user();
        if (! $user) {
            return 'guest';
        }

        $roleName = $user->role?->role_name ?? null;
        if ($roleName) {
            $roleName = strtolower(str_replace(' ', '-', $roleName));
        } else {
            $roleName = 'user';
        }

        return $roleName . '-' . $user->id;
    }

    private function buildRoleAwareDocxPath(string $fileName): string
    {
        return 'uploads/rencana_pembelajaran/docx/' . $this->resolveRoleAwareStorageSegment() . '/' . $fileName;
    }

    public function downloadTemplate()
    {
        $path = storage_path('app/templates/template_modul_ajar.docx');

        if (!file_exists($path)) {
            return response()->back()->with('error', 'Template tidak ditemukan.');
        }

        return response()->download($path, 'template_modul_ajar.docx');
    }

    public function previewDocx(Request $request)
    {
        $request->validate([
            'judul' => 'nullable|string|max:255',
            'mata_pelajaran_id' => 'nullable|exists:mata_pelajaran,id',
            'kelas_id' => 'nullable|exists:kelas,id',
            'fase' => 'nullable|string|max:10',
            'alokasi_waktu' => 'nullable|string|max:255',
            'status' => 'nullable|in:draft,published',
        ]);

        $user = Auth::user();
        $guru = optional($user->guru);
        $sekolah = \App\Models\Sekolah::first();
        $activeSemester = \App\Models\Semester::where('is_active', 1)->first();
        $activeTahunAjaran = \App\Models\TahunAjaran::where('is_active', 1)->first();

        $mataPelajaran = \App\Models\MataPelajaran::find($request->mata_pelajaran_id);
        $kelas = \App\Models\Kelas::find($request->kelas_id);

        $templatePath = storage_path('app/templates/template_modul_ajar.docx');
        if (!file_exists($templatePath)) {
            return response()->json(['error' => 'Template tidak ditemukan'], 404);
        }

        $tempDir = public_path('uploads/rencana_pembelajaran/docx/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempKey = 'preview_' . ($user->id ?? 'guest') . '_' . time();
        $tempPath = $tempDir . '/' . $tempKey . '.docx';

        $replacements = [
            'nama_sekolah' => $sekolah->nama_sekolah ?? '',
            'nama_guru' => $guru->nama ?? $user->name ?? '',
            'nip' => $guru->nip ?? $user->nip ?? '',
            'mata_pelajaran' => $mataPelajaran->nama_mapel ?? '',
            'kelas' => $kelas->nama_kelas ?? '',
            'fase' => $request->fase ?? '',
            'semester' => $activeSemester->nama_semester ?? '',
            'tahun_ajaran' => $activeTahunAjaran->nama_tahun ?? '',
            'judul' => $request->judul ?? '',
            'status' => $request->status ?? 'draft',
            'alokasi_waktu' => $request->alokasi_waktu ?? '',
        ];

        try {
            \Novay\Word\Word::template($templatePath)
                ->setValues($replacements)
                ->save($tempPath);

            return response()->json([
                'tempKey' => $tempKey,
                'url' => url('uploads/rencana_pembelajaran/docx/temp/' . $tempKey . '.docx'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal generate preview DOCX', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Gagal membuat preview'], 500);
        }
    }

    private function extractHtmlBodyContent(string $html): string
    {
        if (empty(trim($html))) {
            return '';
        }

        $body = $html;
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            $body = $matches[1];
        }

        $body = preg_replace('/<!DOCTYPE[^>]*>/is', '', $body);
        $body = preg_replace('/<html\b[^>]*>/is', '', $body);
        $body = preg_replace('/<\/html>/is', '', $body);
        $body = preg_replace('/<head\b[^>]*>.*?<\/head>/is', '', $body);
        $body = preg_replace('/<meta\s+[^>]*charset[^>]*>/is', '', $body);

        return trim($body);
    }

    private function buildHtmlPreviewBodyFromPayload(array $payload): string
    {
        $title = $payload['title'] ?? 'Modul Ajar';
        $subject = $payload['subject'] ?? '-';
        $class = $payload['class'] ?? '-';
        $status = $payload['status'] ?? 'draft';
        $duration = $payload['duration'] ?? '-';
        $dimensi = $payload['dimensi_lulusan'] ?? '';

        $html = '<h1 style="text-align:center;">' . e($title) . '</h1>';
        $html .= '<h2>Informasi Umum</h2>';
        $html .= '<table style="width:100%; border-collapse:collapse;">'
            . '<tr><th style="width:30%; border:1px solid #afbdce; background:#eef5fc; padding:8px;">Mata Pelajaran</th><td style="border:1px solid #afbdce; padding:8px;">' . e($subject) . '</td></tr>'
            . '<tr><th style="width:30%; border:1px solid #afbdce; background:#eef5fc; padding:8px;">Kelas / Fase</th><td style="border:1px solid #afbdce; padding:8px;">' . e($class) . '</td></tr>'
            . '<tr><th style="width:30%; border:1px solid #afbdce; background:#eef5fc; padding:8px;">Status</th><td style="border:1px solid #afbdce; padding:8px;">' . e($status === 'published' ? 'Publish (Digunakan untuk KBM)' : 'Draft (Belum digunakan untuk KBM)') . '</td></tr>'
            . '<tr><th style="width:30%; border:1px solid #afbdce; background:#eef5fc; padding:8px;">Alokasi Waktu</th><td style="border:1px solid #afbdce; padding:8px;">' . e($duration) . '</td></tr>'
            . '<tr><th style="width:30%; border:1px solid #afbdce; background:#eef5fc; padding:8px;">Dimensi Lulusan</th><td style="border:1px solid #afbdce; padding:8px;">' . nl2br(e($dimensi)) . '</td></tr>'
            . '</table>';

        $sections = [
            'achievement' => 'Capaian Pembelajaran',
            'objectives' => 'Tujuan Pembelajaran',
            'practice' => 'Praktik Pedagogis',
            'environment' => 'Lingkungan Pembelajaran',
            'digital' => 'Pemanfaatan Digital',
            'experience' => 'Pengalaman Pembelajaran',
            'reflection' => 'Refleksi Pembelajaran',
            'assessment' => 'Asesmen',
        ];

        foreach ($sections as $key => $label) {
            $value = $payload[$key] ?? '';
            $html .= '<h2>' . $label . '</h2>';
            $html .= '<div>' . ($value ? $value : '<p>-</p>') . '</div>';
        }

        return $html;
    }

    private function renderHtmlPayloadToDocxFile(string $htmlContent, string $docxDestination): void
    {
        $htmlBody = $this->extractHtmlBodyContent($htmlContent);
        if (empty(trim($htmlBody))) {
            throw new \RuntimeException('Konten HTML preview kosong setelah normalisasi body');
        }

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlBody, false, false);
        $writer = new \PhpOffice\PhpWord\Writer\Word2007($phpWord);
        $writer->save($docxDestination);
    }

    private function persistGeneratedPreviewDocx(int $moduleId, string $generatedFileName, string $sourceDocxPath): ?string
    {
        $binary = file_get_contents($sourceDocxPath);
        if ($binary === false) {
            return null;
        }

        $relativeDir = 'modul-ajar/' . $moduleId . '/preview';
        $relativePath = $relativeDir . '/' . $generatedFileName;

        if (!Storage::disk('public')->put($relativePath, $binary)) {
            Log::warning('Gagal menyimpan preview DOCX hasil render ke storage Laravel', [
                'module_id' => $moduleId,
                'relative_path' => $relativePath,
            ]);
            return null;
        }

        return $relativePath;
    }

    public function savePreviewAsDocx($id)
    {
        $record = RencanaPembelajaran::findOrFail($id);

        $htmlContent = '';
        $payload = [];
        if ($record->html_content) {
            $decoded = json_decode($record->html_content, true);
            if (is_array($decoded)) {
                $payload = $decoded;
                $htmlContent = $decoded['content'] ?? '';
            }
        }

        if (empty($htmlContent) && !empty($payload)) {
            $htmlContent = $this->buildHtmlPreviewBodyFromPayload($payload);
        }

        $tempDir = public_path('uploads/editor-modul/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $tempKey = 'preview_' . $record->id . '_' . time();
        $tempPath = $tempDir . '/' . $tempKey . '.docx';

        try {
            if ($htmlContent) {
                $htmlBody = $this->extractHtmlBodyContent($htmlContent);
                if (!$htmlBody) {
                    throw new \RuntimeException('Konten HTML preview kosong setelah normalisasi body');
                }

                $phpWord = new \PhpOffice\PhpWord\PhpWord();
                $section = $phpWord->addSection();
                \PhpOffice\PhpWord\Shared\Html::addHtml($section, $htmlBody, false, false);
                $writer = new \PhpOffice\PhpWord\Writer\Word2007($phpWord);
                $writer->save($tempPath);

                $storageRelativePath = $this->persistGeneratedPreviewDocx(
                    $record->id,
                    $tempKey . '.docx',
                    $tempPath
                );

                if ($storageRelativePath) {
                    Log::info('Preview HTML berhasil disimpan sebagai DOCX ke storage Laravel', [
                        'module_id' => $record->id,
                        'relative_path' => $storageRelativePath,
                    ]);
                }
            } elseif ($record->original_docx_path && file_exists(public_path($record->original_docx_path))) {
                copy(public_path($record->original_docx_path), $tempPath);

                $storageRelativePath = $this->persistGeneratedPreviewDocx(
                    $record->id,
                    $tempKey . '.docx',
                    $tempPath
                );

                if ($storageRelativePath) {
                    Log::info('Preview DOCX fallback berhasil disimpan ke storage Laravel', [
                        'module_id' => $record->id,
                        'relative_path' => $storageRelativePath,
                    ]);
                }
            } else {
                return response()->json(['error' => 'Konten preview belum tersedia'], 404);
            }

            return response()->json([
                'success' => true,
                'tempKey' => $tempKey,
                'url' => url('uploads/editor-modul/temp/' . $tempKey . '.docx'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal menyimpan preview sebagai DOCX', [
                'modul_id' => $record->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Gagal menyimpan preview'], 500);
        }
    }

    public function syncFromCollabora($id)
    {
        $record = RencanaPembelajaran::findOrFail($id);

        $tempDir = public_path('uploads/editor-modul/temp');
        if (!is_dir($tempDir)) {
            return response()->json(['error' => 'Direktori temporary tidak ditemukan'], 404);
        }

        $editorPath = $tempDir . '/editor_edit_' . $record->id . '.docx';
        if (!file_exists($editorPath) || filesize($editorPath) <= 0) {
            return response()->json(['error' => 'File editor belum tersedia'], 404);
        }

        try {
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($editorPath, 'Word2007');
            $writer = new \PhpOffice\PhpWord\Writer\HTML($phpWord);

            ob_start();
            $writer->save('php://output');
            $html = ob_get_clean();

            $payload = [
                'content' => $html,
            ];

            if (!empty($html)) {
                $dom = new \DOMDocument();
                @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
                $xpath = new \DOMXPath($dom);

                $titleNode = $xpath->query('//h1')->item(0);
                if ($titleNode) {
                    $payload['title'] = $titleNode->textContent;
                }

                $subjectNode = $xpath->query('//td[contains(., "Mata Pelajaran")]/following-sibling::td')->item(0);
                if ($subjectNode) {
                    $payload['subject'] = $subjectNode->textContent;
                }

                $classNode = $xpath->query('//td[contains(., "Kelas")]/following-sibling::td')->item(0);
                if ($classNode) {
                    $payload['class'] = $classNode->textContent;
                }
            }

            $record->update([
                'html_content' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perubahan berhasil disimpan',
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal sync dari Collabora', [
                'modul_id' => $record->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Gagal menyinkronkan perubahan'], 500);
        }
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:docx|max:2048',
        ]);

        $file = $request->file('file');
        $path = $file->getRealPath();

        $phpWord = IOFactory::load($path, 'Word2007');

        $labels = [
            'judul modul ajar' => 'title',
            'mata pelajaran' => 'subject',
            'kelas / fase' => 'class',
            'status' => 'status',
            'alokasi waktu' => 'duration',
            'capaian pembelajaran' => 'achievement',
            'tujuan pembelajaran' => 'objectives',
            'metode pembelajaran' => 'methods',
            'media pembelajaran' => 'media',
            'sumber belajar' => 'resources',
            'praktik pedagogis' => 'practice',
            'lingkungan pembelajaran' => 'environment',
            'pemanfaatan digital' => 'digital',
            'pengalaman pembelajaran' => 'experience',
            'refleksi pembelajaran' => 'reflection',
            'asesmen' => 'assessment',
            'dimensi_lulusan',
        ];

        $data = [];
        $fieldHtml = [];
        $placeholders = [];
        $currentLabel = null;
        $currentValue = [];
        $currentHtmlParts = [];
        $currentField = null;

        $sectionTexts = [];
        $sectionHtml = [];
        $imageIndex = 0;
        $zipMediaUrls = [];

        $uploadDir = storage_path('app/public/modul-ajar-imports');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        $processElement = null;
        $processElement = function ($element) use (&$processElement, &$sectionText, &$sectionHtmlText, &$zipMediaUrls, &$imageIndex, $uploadDir) {
            if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                $sectionText .= $element->getText();
                return;
            }
            if ($element instanceof \PhpOffice\PhpWord\Element\TextBreak) {
                $sectionText .= "\n";
                return;
            }
            if ($element instanceof \PhpOffice\PhpWord\Element\Image) {
                try {
                    $source = null;
                    try {
                        $source = $element->getSource();
                    } catch (\Throwable $e) {
                        $source = null;
                    }
                    if (!$source || !file_exists($source)) {
                        $source = null;
                    }
                    if (!$source) {
                        $reflection = new \ReflectionClass($element);
                        $prop = $reflection->getProperty('source');
                        $prop->setAccessible(true);
                        $source = $prop->getValue($element);
                    }
                    if ($source && file_exists($source)) {
                        $ext = strtolower(pathinfo($source, PATHINFO_EXTENSION));
                        if (!in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
                            $ext = 'bin';
                        }
                        $newName = uniqid('img_') . '.' . $ext;
                        $savePath = $uploadDir . '/' . $newName;
                        copy($source, $savePath);
                        $url = asset('storage/modul-ajar-imports/' . $newName);
                        $imageIndex++;
                        $zipMediaUrls[] = $url;
                        $placeholder = '[IMAGE:' . $url . ']';
                        $sectionText .= $placeholder . "\n";
                        $sectionHtmlText .= '<img src="' . $url . '" style="max-width:100%;height:auto;" />';
                    }
                } catch (\Throwable $e) {
                    // ignore single image error
                }
                return;
            }
            if (method_exists($element, 'getElements')) {
                foreach ($element->getElements() as $child) {
                    $processElement($child);
                }
                if ($element instanceof \PhpOffice\PhpWord\Element\TextRun || $element instanceof \PhpOffice\PhpWord\Element\Paragraph) {
                    $sectionText .= "\n";
                }
                return;
            }
            if (method_exists($element, 'getText')) {
                $sectionText .= $element->getText();
                return;
            }
            if (method_exists($element, 'getTextComplex')) {
                $sectionText .= $element->getTextComplex();
                return;
            }
        };

        foreach ($phpWord->getSections() as $section) {
            $sectionText = '';
            $sectionHtmlText = '';

            foreach ($section->getElements() as $element) {
                $processElement($element);
            }

            $sectionTexts[] = $sectionText;
            $sectionHtml[] = $sectionHtmlText;
        }

        $zipMediaFiles = [];
        try {
            $zip = new ZipArchive();
            if ($zip->open($path) === true) {
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $name = $zip->getNameIndex($i);
                    if (str_starts_with($name, 'word/media/')) {
                        $zipMediaFiles[] = $name;
                    }
                }
                $zip->close();
            }
        } catch (\Throwable $e) {
            // ignore zip listing errors
        }

        if (!empty($zipMediaFiles)) {
            $uploadDir = storage_path('app/public/modul-ajar-imports');
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }

            foreach ($zipMediaFiles as $mediaFile) {
                $fileName = basename($mediaFile);
                $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                if (!in_array($ext, ['jpg','jpeg','png','gif','webp'], true)) {
                    $ext = 'bin';
                }
                $newName = uniqid('img_') . '.' . $ext;
                $zip = new ZipArchive();
                if ($zip->open($path) === true) {
                    $content = $zip->getFromIndex($zip->locateName($mediaFile));
                    if ($content !== false) {
                        $savePath = $uploadDir . '/' . $newName;
                        file_put_contents($savePath, $content);
                        $url = asset('storage/modul-ajar-imports/' . $newName);
                        $zipMediaUrls[] = $url;
                    }
                    $zip->close();
                }
            }
        }

        $fullText = implode("\n", $sectionTexts);
        $lines = preg_split('/\R/', $fullText);
        $lines = array_values(array_filter(array_map('trim', $lines)));

        $currentLabel = null;
        $currentValue = [];

        foreach ($lines as $line) {
            if (preg_match('/^===\s*(.+?)\s*===$/', $line, $m)) {
                if ($currentLabel !== null) {
                    $data[$currentLabel] = implode("\n", $currentValue);
                }
                $lower = mb_strtolower($m[1]);
                $currentLabel = $labels[$lower] ?? null;
                $currentValue = [];
                continue;
            }

            if ($currentLabel !== null) {
                $currentValue[] = $line;
            }
        }

        if ($currentLabel !== null) {
            $data[$currentLabel] = implode("\n", $currentValue);
        }

        foreach ($labels as $label => $field) {
            $start = false;
            $parts = [];
            foreach ($lines as $line) {
                if (preg_match('/^===\s*' . preg_quote($label, '/') . '\s*===$/i', $line)) {
                    $start = true;
                    continue;
                }
                if ($start) {
                    if (preg_match('/^===\s*(.+?)\s*===$/', $line)) {
                        break;
                    }
                    $parts[] = $line;
                }
            }
            if (!empty($parts)) {
                $fieldHtml[$field] = implode("\n", $parts);
            }
        }

        $allImages = [];
        foreach ($zipMediaUrls as $url) {
            $allImages[] = $url;
        }

        $imagesHtml = '';
        if (!empty($allImages)) {
            $imagesHtml = implode("\n", array_map(function ($url) {
                return '<img src="' . $url . '" style="max-width:100%;height:auto;" />';
            }, $allImages));
        }

        foreach ($data as $field => $value) {
            $data[$field] = preg_replace('/\[IMAGE:([^\]]+)\]/', '<img src="$1" style="max-width:100%;height:auto;" />', $value);
            if (preg_match_all('/\[IMAGE:([^\]]+)\]/', $value, $matches)) {
                foreach ($matches[1] as $url) {
                    $placeholders[] = [
                        'field' => $field,
                        'type' => 'image',
                        'url' => $url,
                    ];
                }
            }
            if (preg_match_all('/\[TABLE(?::([^\]]+))?\]/', $value, $matches)) {
                foreach ($matches[1] as $caption) {
                    $placeholders[] = [
                        'field' => $field,
                        'type' => 'table',
                        'caption' => $caption ?: 'Tabel',
                    ];
                }
            }
        }

        foreach ($zipMediaUrls as $url) {
            $placeholders[] = [
                'field' => null,
                'type' => 'image',
                'url' => $url,
            ];
        }

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Import berhasil',
            'images' => $allImages,
            'images_html' => $imagesHtml,
             'placeholders' => $placeholders,
        ], $data));
    }

    public function downloadSavedDocument(string $id)
    {
        $modules = Session::get('modul_ajar_items', []);
        $module = $modules[$id] ?? null;

        if (! $module) {
            $record = RencanaPembelajaran::find($id);
            if ($record) {
                $module = $this->buildModulePayloadFromModel($record);
            }
        }

        if (! $module || empty($module['docx_path'])) {
            return redirect()->back()->with('error', 'File dokumen tidak ditemukan.');
        }

        $filePath = public_path($module['docx_path']);
        if (! file_exists($filePath)) {
            return redirect()->back()->with('error', 'File dokumen modul tidak ditemukan.');
        }

        return response()->download($filePath, Str::slug($module['title'] ?? 'modul_ajar') . '.docx');
    }

    private function loadMataPelajaranAndKelas(): array
    {
        $user = Auth::user();
        $mataPelajaranList = collect();
        $kelasList = collect();

        if ($user && $user->guru_id) {
            $tugas = TugasGuru::with(['mataPelajaran','kelas'])->where('guru_id', $user->guru_id)->get();
            $mataPelajaranList = $tugas->pluck('mataPelajaran')->filter()->unique('id')->values();
            $kelasList = $tugas->pluck('kelas')->filter()->unique('id')->values();
        } else {
            $mataPelajaranList = MataPelajaran::orderBy('nama_mapel')->get();
            $kelasList = Kelas::orderBy('nama_kelas')->get();
        }

        return [$mataPelajaranList, $kelasList];
    }

    private function buildFaseOptions(): array
    {
        $fases = CapaianPembelajaran::whereNotNull('fase')->orderBy('fase')->pluck('fase')->unique()->values();
        if ($fases->isEmpty()) {
            return [
                'E' => 'Fase E: Kelas 10 SMA',
                'F' => 'Fase F: Kelas 11–12 SMA',
            ];
        }
        $options = [];
        foreach ($fases as $f) {
            $label = 'Fase ' . $f;
            if ($f === 'E') {
                $label .= ': Kelas 10 SMA';
            }
            if ($f === 'F') {
                $label .= ': Kelas 11–12 SMA';
            }
            $options[$f] = $label;
        }
        return $options;
    }

    public function destroy($id)
    {
        $user = Auth::user();
        $record = RencanaPembelajaran::find($id);

        if ($record) {
            if ($user && $user->guru_id && $record->guru_id != $user->guru_id && !$user->hasAnyRole(['Admin', 'Kepala Sekolah'])) {
                abort(403);
            }

            if ($record->original_docx_path && file_exists(public_path($record->original_docx_path))) {
                @unlink(public_path($record->original_docx_path));
            }

            \App\Models\AgendaGuru::where('rencana_pembelajaran_id', $record->id)->delete();
            $record->delete();
        }

        $modules = Session::get('modul_ajar_items', []);
        foreach ($modules as $key => $module) {
            if (isset($module['id']) && (string) $module['id'] === (string) $id) {
                unset($modules[$key]);
            }
        }
        Session::put('modul_ajar_items', $modules);

        return redirect()->route('rencana_pembelajaran.index')->with('success', 'Modul ajar berhasil dihapus.');
    }
}
