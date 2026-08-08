<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\TugasGuru;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use App\Models\CapaianPembelajaran;
use App\Models\Sekolah;
use App\Models\KepalaSekolah;
use App\Models\RencanaPembelajaran;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\Font;
use App\Services\OnlyOfficeCallbackService;
use App\Services\SettingsManager;
use Illuminate\Http\Response;
// show/download removed to restore original WYSIWYG flow

class RencanaPembelajaranController extends Controller
{
    /**
     * Fields that hold rich HTML content from the editor
     */
    private array $richFields = [
        'achievement','objectives','methods','media','resources','practice','environment','digital','experience','reflection','assessment',
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
        $subject = $meta['subject'] ?? null;
        $class = $meta['class'] ?? null;
        $duration = $model->alokasi_waktu ?? $meta['duration'] ?? null;
        $status = $model->status ?? $meta['status'] ?? 'draft';

        return [
            'id' => $model->id,
            'title' => $title,
            'subject' => $subject,
            'class' => $class,
            'duration' => $duration,
            'status' => $status,
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
            'docx_path' => $model->original_docx_path ? 'uploads/rencana_pembelajaran/docx/' . basename($model->original_docx_path) : null,
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

        $editorTempKey = uniqid('modul_ajar_', true);
        $editorTemplateUrl = route('rencana_pembelajaran.document_temp', ['tempKey' => $editorTempKey]);
        $editorCallbackUrl = route('rencana_pembelajaran.onlyoffice_temp_callback', ['tempKey' => $editorTempKey]);
        $editorDocumentKey = sha1($editorTemplateUrl . $editorTempKey . time());
        $editorOnlyOfficeJwtToken = $this->generateOnlyOfficeJwtToken([
            'url' => $editorCallbackUrl,
            'document_key' => $editorDocumentKey,
        ]);

        return view('rencana_pembelajaran.index', [
            'modules' => $modules,
            'sekolah' => $sekolah,
            'guruName' => $guruName,
            'guruNip' => $guruNip,
            'kepalaName' => $kepalaName,
            'kepalaNip' => $kepalaNip,
            'editorTempKey' => $editorTempKey,
            'editorTemplateUrl' => $editorTemplateUrl,
            'editorCallbackUrl' => $editorCallbackUrl,
            'editorDocumentKey' => $editorDocumentKey,
            'editorOnlyOfficeJwtToken' => $editorOnlyOfficeJwtToken,
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
        ]);
    }

    public function edit($id)
    {
        $modules = Session::get('modul_ajar_items', []);
        $module = $modules[$id] ?? null;

        if (! $module) {
            $record = RencanaPembelajaran::find($id);
            if ($record) {
                $module = $this->buildModulePayloadFromModel($record);
            }
        }

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

        return view('rencana_pembelajaran.form', [
            'mode' => 'edit',
            'moduleId' => $id,
            'module' => $module,
            'mataPelajaranList' => $mataPelajaranList,
            'kelasList' => $kelasList,
            'faseOptions' => $faseOptions,
            'selectedFase' => $module['fase'] ?? null,
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
        ];

        $data = [];
        foreach ($allFields as $field) {
            $value = $request->input($field);
            if ($value !== null && $value !== '') {
                $data[$field] = $value;
            }
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
                'judul' => $payload['title'] ?? $record->judul,
                'capaian_pembelajaran' => $payload['achievement'] ?? null,
                'tujuan' => $payload['objectives'] ?? null,
                'metode' => $payload['methods'] ?? null,
                'media' => $payload['media'] ?? null,
                'sumber' => $payload['resources'] ?? null,
                'penilaian' => $payload['assessment'] ?? null,
                'alokasi_waktu' => $payload['duration'] ?? null,
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
                'judul' => $payload['title'] ?? 'Modul Ajar',
                'capaian_pembelajaran' => $payload['achievement'] ?? null,
                'tujuan' => $payload['objectives'] ?? null,
                'metode' => $payload['methods'] ?? null,
                'media' => $payload['media'] ?? null,
                'sumber' => $payload['resources'] ?? null,
                'penilaian' => $payload['assessment'] ?? null,
                'alokasi_waktu' => $payload['duration'] ?? null,
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

        return redirect()->route('rencana_pembelajaran.index')->with('success', 'Modul ajar berhasil disimpan ke akun guru dan database.');
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

    private function buildCollaboraEditorUrl(string $collaboraServerUrl, string $fileUrl, string $fileName): string
    {
        if (empty($collaboraServerUrl)) {
            return '';
        }

        if (str_starts_with($collaboraServerUrl, 'http://')) {
            $collaboraServerUrl = preg_replace('#^http://#', 'https://', $collaboraServerUrl);
        }

        $browserPath = '/browser/2229109277/cool.html';
        return $collaboraServerUrl . $browserPath . '?WOPISrc=' . urlencode($fileUrl) . '&lang=id&mode=edit';
    }

    public function downloadTemplate()
    {
        $path = storage_path('app/templates/template_modul_ajar.docx');

        if (!file_exists($path)) {
            return response()->back()->with('error', 'Template tidak ditemukan.');
        }

        return response()->download($path, 'template_modul_ajar.docx');
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

    public function editor()
    {
        [$editorMataPelajaranList, $editorKelasList] = $this->loadMataPelajaranAndKelas();
        $editorFaseOptions = $this->buildFaseOptions();

        $editorTempKey = uniqid('modul_ajar_', true);

        $collaboraServerUrl = rtrim(config('services.collabora.server_url', env('COLLABORA_SERVER_URL', '')), '/');
        $collaboraWopiSrc = route('collabora.check_file_info', ['tempKey' => $editorTempKey]);
        $collaboraEditorUrl = $this->buildCollaboraEditorUrl($collaboraServerUrl, $collaboraWopiSrc, 'modul-ajar.docx');

        return view('rencana_pembelajaran.editor', [
            'editorTempKey' => $editorTempKey,
            'editorMataPelajaranList' => $editorMataPelajaranList,
            'editorKelasList' => $editorKelasList,
            'editorFaseOptions' => $editorFaseOptions,
            'collaboraServerUrl' => $collaboraServerUrl,
            'collaboraEditorUrl' => $collaboraEditorUrl,
        ]);
    }

    public function documentTemp(Request $request, string $tempKey)
    {
        $tempKey = preg_replace('/[^A-Za-z0-9_\-]/', '', $tempKey);
        $tempDir = public_path('uploads/rencana_pembelajaran/docx/temp');
        $tempFile = $tempDir . '/' . $tempKey . '.docx';

        if ($request->isMethod('put') || $request->isMethod('post')) {
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $content = $request->getContent();
            file_put_contents($tempFile, $content);
            return response('OK', 200);
        }

        if (!file_exists($tempFile)) {
            $path = storage_path('app/templates/template_modul_ajar.docx');
            if (!file_exists($path)) {
                abort(404, 'Template dokumen tidak ditemukan.');
            }
            return response()->file($path, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Content-Disposition' => 'inline; filename="template_modul_ajar.docx"',
            ]);
        }

        return response()->file($tempFile, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'inline; filename="modul-ajar.docx"',
        ]);
    }

    public function onlyOfficeTempCallback(Request $request, string $tempKey, OnlyOfficeCallbackService $service)
    {
        $tempKey = preg_replace('/[^A-Za-z0-9_\-]/', '', $tempKey);
        $tempPath = 'rencana_pembelajaran/docx/temp/' . $tempKey . '.docx';
        $result = $service->handleTempCallback($request, $tempPath);
        return response()->json($result);
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

    public function tryExportPdfViaOnlyOffice(RencanaPembelajaran $rencanaPembelajaran, string $serverUrl, ?string $jwtToken = null)
    {
        $documentUrl = URL::temporarySignedRoute('rencana_pembelajaran.document', ['id' => $rencanaPembelajaran->id], now()->addMinutes(30));

        $payload = [
            'async' => false,
            'url' => $documentUrl,
            'outputtype' => 'pdf',
            'title' => $rencanaPembelajaran->judul ?? 'Rencana_Pembelajaran',
        ];

        $response = Http::withHeaders(array_filter([
            'Authorization' => $jwtToken ? 'Bearer ' . $jwtToken : null,
        ]))->post($serverUrl . '/ConvertService.ashx', $payload);

        if ($response->successful() && !empty($response->json('fileUrl'))) {
            $pdfResponse = Http::get($response->json('fileUrl'));
            if ($pdfResponse->successful()) {
                $filename = 'Rencana_Pembelajaran_' . Str::slug($rencanaPembelajaran->judul ?? 'rencana-pembelajaran') . '.pdf';
                return response($pdfResponse->body(), 200)
                    ->header('Content-Type', $pdfResponse->header('Content-Type') ?: 'application/pdf')
                    ->header('Content-Disposition', 'inline; filename="' . $filename . '"');
            }
        }

        return response()->json(['error' => 'Unable to convert document'], 500);
    }

    private function generateOnlyOfficeJwtToken(array $payload = []): ?string
    {
        $secret = $this->getOnlyOfficeSecret();
        if (empty($secret)) {
            return null;
        }

        $header = ['alg' => 'HS256', 'typ' => 'JWT'];
        $payload = array_merge([
            'iat' => time(),
            'exp' => time() + 3600,
            'iss' => config('app.url'),
            'user' => [
                'id' => Auth::id() ?? 'guest',
                'name' => Auth::user()?->name ?? 'Guest',
            ],
        ], $payload);

        $segments = [
            $this->jwtBase64UrlEncode(json_encode($header, JSON_UNESCAPED_SLASHES)),
            $this->jwtBase64UrlEncode(json_encode($payload, JSON_UNESCAPED_SLASHES)),
        ];

        $signature = hash_hmac('sha256', implode('.', $segments), $secret, true);
        $segments[] = $this->jwtBase64UrlEncode($signature);

        return implode('.', $segments);
    }

    private function jwtBase64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function getOnlyOfficeSecret(): ?string
    {
        $settings = new SettingsManager();
        $secret = $settings->get('onlyoffice.server_secret', null);
        if (! empty($secret)) {
            return $secret;
        }

        return config('services.onlyoffice.onlyoffice_secret', env('ONLYOFFICE_SECRET'));
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
}
