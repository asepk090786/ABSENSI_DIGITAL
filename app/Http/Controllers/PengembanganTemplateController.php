<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PengembanganTemplateController extends Controller
{
    public function index()
    {
        $items = DB::table('pengembangan_sertifikat_templates')->orderBy('nama')->get();
        return view('pengembangan.templates.index', compact('items'));
    }

    public function create()
    {
        return view('pengembangan.templates.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nama' => 'nullable|string',
            'template_html' => 'nullable|string',
            'output_format' => 'nullable|string|in:pdf,docx,xlsx,jpeg',
            'page_size' => 'nullable|string|in:A4,Letter',
            'page_orientation' => 'nullable|string|in:portrait,landscape',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10120',
            'placeholder_positions' => 'nullable|string',
            'editor_mode' => 'nullable|string|in:image,html',
            'font_file' => 'nullable|file|mimes:ttf,otf|max:5120',
            'pos' => 'nullable|array',
            'include_verification_code' => 'sometimes|boolean',
            'include_verification_qr' => 'sometimes|boolean',
        ]);

        $backgroundPath = null;
        if ($r->hasFile('background_image')) {
            $backgroundPath = $r->file('background_image')->store('certificate_templates', 'public');
        }

        // Build positions JSON from pos array
        $positionsJson = null;
        if ($r->filled('placeholder_positions')) {
            $positionsJson = $r->input('placeholder_positions');
        } elseif ($r->has('pos')) {
            $positionsJson = json_encode($r->input('pos'));
        }

        $positionsArr = json_decode($positionsJson ?: '{}', true) ?? [];
        $includeVerificationCode = $r->boolean('include_verification_code');
        $includeVerificationQr = $r->boolean('include_verification_qr');

        if ($includeVerificationCode) {
            $verificationTextConfig = $positionsArr['verification_text'] ?? [];
            $positionsArr['verification_text'] = array_merge([
                'x_ratio' => 0.5,
                'y_ratio' => 0.8,
                'font_size' => 16,
                'color' => '#000000',
                'align' => 'center',
            ], $verificationTextConfig);
        } else {
            unset($positionsArr['verification_text']);
        }

        if ($includeVerificationQr) {
            $verificationQrConfig = $positionsArr['verification_qr'] ?? [];
            $positionsArr['verification_qr'] = array_merge([
                'x_ratio' => 0.85,
                'y_ratio' => 0.8,
                'font_size' => 16,
                'color' => '#000000',
                'align' => 'center',
                'qr_size' => 180,
            ], $verificationQrConfig);
        } else {
            unset($positionsArr['verification_qr']);
        }

        $positionsJson = !empty($positionsArr) ? json_encode($positionsArr) : null;

        // Handle font file upload
        $fontPath = null;
        if ($r->hasFile('font_file')) {
            $fontPath = $r->file('font_file')->store('certificate_fonts', 'public');
        }

        $insertData = [
            'nama' => $data['nama'] ?? null,
            'template_html' => $data['template_html'] ?? null,
            'output_format' => $data['output_format'] ?? 'pdf',
            'page_size' => $data['page_size'] ?? 'A4',
            'page_orientation' => $data['page_orientation'] ?? 'portrait',
            'background_image' => $backgroundPath,
            'placeholder_positions' => $positionsJson,
            'editor_mode' => $data['editor_mode'] ?? 'image',
            'font_file' => $fontPath,
            'include_verification_code' => $includeVerificationCode ? 1 : 0,
            'include_verification_qr' => $includeVerificationQr ? 1 : 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // If preview mode, return JSON with preview URL
        if ($r->has('preview') && $r->input('preview') == '1') {
            $tpl = (object) array_merge($insertData, ['id' => 0]);
            // Generate preview using CertificateService
            try {
                $certService = app(\App\Services\CertificateService::class);
                $previewUrl = $certService->previewFromFile($r->file('background_image'), $positionsJson);
                return response()->json(['preview_url' => $previewUrl]);
            } catch (\Throwable $e) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        DB::table('pengembangan_sertifikat_templates')->insert($insertData);
        return redirect()->route('pengembangan.templates.index')->with('success','Template dibuat');
    }

    public function edit($id)
    {
        $item = DB::table('pengembangan_sertifikat_templates')->where('id',$id)->first();
        if (!$item) abort(404);
        return view('pengembangan.templates.edit', compact('item'));
    }

    public function update(Request $r, $id)
    {
        $item = DB::table('pengembangan_sertifikat_templates')->where('id', $id)->first();
        if (! $item) abort(404);

        if ($r->hasFile('background_image')) {
            $file = $r->file('background_image');
            Log::info('Background upload attempt', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'error' => $file->getError(),
            ]);
        }

        $data = $r->validate([
            'nama' => 'nullable|string',
            'template_html' => 'nullable|string',
            'output_format' => 'nullable|string|in:pdf,docx,xlsx,jpeg',
            'page_size' => 'nullable|string|in:A4,Letter',
            'page_orientation' => 'nullable|string|in:portrait,landscape',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10120',
            'placeholder_positions' => 'nullable|string',
            'editor_mode' => 'nullable|string|in:image,html',
            'font_file' => 'nullable|file|mimes:ttf,otf|max:5120',
            'pos' => 'nullable|array',
            'include_verification_code' => 'sometimes|boolean',
            'include_verification_qr' => 'sometimes|boolean',
        ]);

        $backgroundPath = null;
        if ($r->hasFile('background_image')) {
            $backgroundPath = $r->file('background_image')->store('certificate_templates', 'public');
            if (isset($item->background_image) && $item->background_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($item->background_image);
            }
        }

        // Build positions JSON from pos array
        $positionsJson = null;
        if ($r->filled('placeholder_positions')) {
            $positionsJson = $r->input('placeholder_positions');
        } elseif ($r->has('pos')) {
            $positionsJson = json_encode($r->input('pos'));
        }

        $positionsArr = json_decode($positionsJson ?? $item->placeholder_positions ?? '{}', true) ?? [];
        $includeVerificationCode = $r->boolean('include_verification_code');
        $includeVerificationQr = $r->boolean('include_verification_qr');

        if ($includeVerificationCode) {
            $verificationTextConfig = $positionsArr['verification_text'] ?? [];
            $positionsArr['verification_text'] = array_merge([
                'x_ratio' => 0.5,
                'y_ratio' => 0.8,
                'font_size' => 16,
                'color' => '#000000',
                'align' => 'center',
            ], $verificationTextConfig);
        } else {
            unset($positionsArr['verification_text']);
        }

        if ($includeVerificationQr) {
            $verificationQrConfig = $positionsArr['verification_qr'] ?? [];
            $positionsArr['verification_qr'] = array_merge([
                'x_ratio' => 0.85,
                'y_ratio' => 0.8,
                'font_size' => 16,
                'color' => '#000000',
                'align' => 'center',
                'qr_size' => 180,
            ], $verificationQrConfig);
        } else {
            unset($positionsArr['verification_qr']);
        }

        $positionsJson = !empty($positionsArr) ? json_encode($positionsArr) : null;

        // Handle font file upload
        $fontPath = $item->font_file ?? null;
        if ($r->hasFile('font_file')) {
            $fontPath = $r->file('font_file')->store('certificate_fonts', 'public');
            if (isset($item->font_file) && $item->font_file) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($item->font_file);
            }
        }

        $updateData = [
            'nama' => $data['nama'] ?? null,
            'template_html' => $data['template_html'] ?? null,
            'output_format' => $data['output_format'] ?? 'pdf',
            'page_size' => $data['page_size'] ?? 'A4',
            'page_orientation' => $data['page_orientation'] ?? 'portrait',
            'placeholder_positions' => $positionsJson,
            'editor_mode' => $data['editor_mode'] ?? 'image',
            'font_file' => $fontPath,
            'include_verification_code' => $includeVerificationCode ? 1 : 0,
            'include_verification_qr' => $includeVerificationQr ? 1 : 0,
            'updated_at' => now(),
        ];
        if ($backgroundPath) {
            $updateData['background_image'] = $backgroundPath;
        }

        DB::table('pengembangan_sertifikat_templates')->where('id',$id)->update($updateData);
        return redirect()->route('pengembangan.templates.index')->with('success','Template diperbarui');
    }

    public function destroy($id)
    {
        DB::table('pengembangan_sertifikat_templates')->where('id',$id)->delete();
        return redirect()->route('pengembangan.templates.index')->with('success','Template dihapus');
    }
}
