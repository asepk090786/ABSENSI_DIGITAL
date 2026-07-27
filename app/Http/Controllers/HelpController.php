<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * HelpController now supports multiple help pages stored in storage/help_pages/*.html
 * and an index file storage/help_pages.json which contains metadata (slug, title, timestamps).
 */

class HelpController extends Controller
{
    public function index()
    {
        // Public index not used (public help removed). Redirect to admin index for authenticated users.
        return redirect()->route('home');
    }

    public function publicIndex()
    {
        $items = collect($this->readIndex())->map(function ($item) {
            return $this->ensureItemHasVideoLink($item);
        })->values()->all();

        return view('help.public_index', ['items' => $items]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'nullable|string|max:191',
            'email' => 'nullable|email|max:191',
            'subject' => 'nullable|string|max:191',
            'message' => 'required|string|max:5000',
            'video_link' => 'nullable|url|max:1000'
        ]);

        $entry = [
            'timestamp' => now()->toDateTimeString(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'payload' => $data,
        ];

        try {
            Storage::append('help_requests.jsonl', json_encode($entry));
        } catch (\Throwable $e) {
            // fallback to logging if storage append not available
            logger('help_request_failed', ['error' => $e->getMessage(), 'entry' => $entry]);
        }

        return back()->with('success', 'Terima kasih — permintaan bantuan Anda sudah kami terima.');
    }
    // --- Admin CRUD for help pages ---
    protected function readIndex()
    {
        try {
            if (Storage::exists('help_pages.json')) {
                $raw = Storage::get('help_pages.json');
                $arr = json_decode($raw, true);
                return is_array($arr) ? $arr : [];
            }
        } catch (\Throwable $e) {
            logger('help_read_index_failed', ['error' => $e->getMessage()]);
        }
        return [];
    }

    protected function writeIndex(array $items)
    {
        try {
            Storage::put('help_pages.json', json_encode(array_values($items), JSON_PRETTY_PRINT));
        } catch (\Throwable $e) {
            logger('help_write_index_failed', ['error' => $e->getMessage()]);
        }
    }

    protected function ensureItemHasVideoLink(array &$item): array
    {
        $item['video_link'] = $item['video_link'] ?? null;
        return $item;
    }

    public function adminIndex()
    {
        $items = collect($this->readIndex())->map(function ($item) {
            return $this->ensureItemHasVideoLink($item);
        })->all();

        return view('help.admin_index', ['items' => $items]);
    }

    public function create()
    {
        return view('help.admin_create');
    }

    public function storeAdmin(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:191',
            'content' => 'nullable|string',
            'video_link' => 'nullable|url|max:1000',
        ]);

        $items = $this->readIndex();
        $base = Str::slug($data['title']);
        $slug = $base;
        $i = 1;
        while (Storage::exists("help_pages/{$slug}.html") || (collect($items)->where('slug', $slug)->first() !== null)) {
            $slug = $base . '-' . $i; $i++;
        }

        $now = now()->toDateTimeString();
        $items[] = [
            'slug' => $slug,
            'title' => $data['title'],
            'created_at' => $now,
            'updated_at' => $now,
            'video_link' => $data['video_link'] ?? null,
        ];

        try {
            Storage::put("help_pages/{$slug}.html", $data['content'] ?? '');
            $this->writeIndex($items);
        } catch (\Throwable $e) {
            logger('help_store_failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal membuat halaman help.');
        }

        return redirect()->route('help.admin.index')->with('success', 'Halaman help dibuat.');
    }

    public function edit($slug)
    {
        $items = $this->readIndex();
        $found = collect($items)->firstWhere('slug', $slug);
        if (!$found) abort(404);

        $content = '';
        try { if (Storage::exists("help_pages/{$slug}.html")) $content = Storage::get("help_pages/{$slug}.html"); } catch (\Throwable $e) { logger('help_edit_load_failed', ['error'=>$e->getMessage()]); }

        return view('help.admin_edit', [
            'slug' => $slug,
            'title' => $found['title'],
            'content' => $content,
            'video_link' => $found['video_link'] ?? null,
        ]);
    }

    public function update(Request $request, $slug)
    {
        $data = $request->validate([
            'title' => 'required|string|max:191',
            'content' => 'nullable|string',
            'video_link' => 'nullable|url|max:1000',
        ]);

        $items = $this->readIndex();
        $index = collect($items)->search(function($it) use ($slug){ return ($it['slug'] ?? '') === $slug; });
        if ($index === false) abort(404);

        $items[$index]['title'] = $data['title'];
        $items[$index]['updated_at'] = now()->toDateTimeString();
        $items[$index]['video_link'] = $data['video_link'] ?? null;

        try {
            Storage::put("help_pages/{$slug}.html", $data['content'] ?? '');
            $this->writeIndex($items);
        } catch (\Throwable $e) {
            logger('help_update_failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menyimpan perubahan.');
        }

        return redirect()->route('help.admin.index')->with('success', 'Halaman Help berhasil diperbarui.');
    }

    public function show($slug)
    {
        $items = $this->readIndex();
        $found = collect($items)->firstWhere('slug', $slug);
        if (!$found) abort(404);

        $content = '';
        try {
            if (Storage::exists("help_pages/{$slug}.html")) {
                $content = Storage::get("help_pages/{$slug}.html");
            }
        } catch (\Throwable $e) {
            logger('help_show_load_failed', ['error' => $e->getMessage()]);
        }

        return view('help.show', [
            'pageTitle' => $found['title'],
            'content' => $content,
            'video_link' => $found['video_link'] ?? null,
        ]);
    }

    public function destroy($slug)
    {
        $items = $this->readIndex();
        $new = collect($items)->reject(function($it) use ($slug){ return ($it['slug'] ?? '') === $slug; })->values()->all();

        try {
            if (Storage::exists("help_pages/{$slug}.html")) Storage::delete("help_pages/{$slug}.html");
            $this->writeIndex($new);
        } catch (\Throwable $e) {
            logger('help_delete_failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Gagal menghapus halaman.');
        }

        return redirect()->route('help.admin.index')->with('success', 'Halaman Help dihapus.');
    }
}
