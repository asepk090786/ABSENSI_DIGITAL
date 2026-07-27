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
        ]);

        $backgroundPath = null;
        if ($r->hasFile('background_image')) {
            $backgroundPath = $r->file('background_image')->store('certificate_templates', 'public');
        }

        DB::table('pengembangan_sertifikat_templates')->insert([
            'nama' => $data['nama'] ?? null,
            'template_html' => $data['template_html'] ?? null,
            'output_format' => $data['output_format'] ?? 'pdf',
            'page_size' => $data['page_size'] ?? 'A4',
            'page_orientation' => $data['page_orientation'] ?? 'portrait',
            'background_image' => $backgroundPath,
            'placeholder_positions' => $data['placeholder_positions'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
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
        ]);

        $backgroundPath = null;
        if ($r->hasFile('background_image')) {
            $backgroundPath = $r->file('background_image')->store('certificate_templates', 'public');
            if (isset($item->background_image) && $item->background_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($item->background_image);
            }
        }

        $updateData = [
            'nama' => $data['nama'] ?? null,
            'template_html' => $data['template_html'] ?? null,
            'output_format' => $data['output_format'] ?? 'pdf',
            'page_size' => $data['page_size'] ?? 'A4',
            'page_orientation' => $data['page_orientation'] ?? 'portrait',
            'placeholder_positions' => $data['placeholder_positions'] ?? null,
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
