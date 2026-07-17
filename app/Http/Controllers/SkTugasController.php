<?php

namespace App\Http\Controllers;

use App\Models\SkTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SkTugasController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('Admin')) {
            $items = SkTugas::orderBy('created_at', 'desc')->get();
        } else {
            $guruId = $user->guru_id;
            $items = SkTugas::visibleToGuru()
                ->where(function ($query) use ($guruId) {
                    $query->whereNull('guru_id')->orWhere('guru_id', $guruId);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('sk_tugas.index', compact('items'));
    }

    public function create()
    {
        return view('sk_tugas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $path = $request->file('file')->store('sk_tugas', 'public');

        SkTugas::create([
            'judul' => $request->input('judul'),
            'file' => $path,
            'guru_id' => null,
        ]);

        return redirect()->route('sk_tugas.index')->with('success', 'SK Tugas berhasil diunggah.');
    }

    public function preview(SkTugas $sk_tugas)
    {
        $user = auth()->user();

        // Allow admins, or allow if the SK is public (guru_id is null) or assigned to the logged-in guru
        if (!$user->hasRole('Admin') && ! (is_null($sk_tugas->guru_id) || $user->guru_id == $sk_tugas->guru_id)) {
            abort(403);
        }

        $path = $sk_tugas->file;

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($extension !== 'pdf') {
            abort(404);
        }

        return response()->file(Storage::disk('public')->path($path), ['Content-Type' => 'application/pdf']);
    }

    public function destroy(SkTugas $sk_tugas)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        if ($sk_tugas->file && Storage::disk('public')->exists($sk_tugas->file)) {
            Storage::disk('public')->delete($sk_tugas->file);
        }

        $sk_tugas->delete();

        return redirect()->route('sk_tugas.index')->with('success', 'SK Tugas berhasil dihapus.');
    }

    public function toggleVisibility(SkTugas $sk_tugas)
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403);
        }

        $sk_tugas->update([
            'is_visible_to_guru' => !$sk_tugas->is_visible_to_guru,
        ]);

        return redirect()->route('sk_tugas.index')->with('success', 'Status tampilan SK Tugas berhasil diperbarui.');
    }

    public function download(SkTugas $sk_tugas)
    {
        $user = auth()->user();

        // Match same authorization logic as preview: admins, or public SK, or assigned guru
        if (!$user->hasRole('Admin') && ! (is_null($sk_tugas->guru_id) || $user->guru_id == $sk_tugas->guru_id)) {
            abort(403);
        }

        return Storage::disk('public')->download($sk_tugas->file, $sk_tugas->judul . '.' . pathinfo($sk_tugas->file, PATHINFO_EXTENSION));
    }
}
