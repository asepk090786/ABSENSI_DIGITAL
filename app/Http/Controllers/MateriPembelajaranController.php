<?php

namespace App\Http\Controllers;

use App\Models\MateriPembelajaran;
use App\Models\RencanaPembelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MateriPembelajaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $guru = auth()->user()->guru;
        $rencanaPembelajaranId = $request->query('rencana_pembelajaran_id');

        if (!$rencanaPembelajaranId) {
            // Show all rencana pembelajaran for this guru
            $rencanaPembelajarans = RencanaPembelajaran::where('guru_id', $guru->id)
                ->with(['mataPelajaran', 'kelas'])
                ->orderBy('created_at', 'desc')
                ->get();

            return view('materi_pembelajaran.index_list', compact('rencanaPembelajarans'));
        }

        // Get specific rencana pembelajaran
        $rencanaPembelajaran = RencanaPembelajaran::where('id', $rencanaPembelajaranId)
            ->where('guru_id', $guru->id)
            ->first();

        if (!$rencanaPembelajaran) {
            abort(404);
        }

        $items = MateriPembelajaran::where('rencana_pembelajaran_id', $rencanaPembelajaranId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('materi_pembelajaran.index', compact('items', 'rencanaPembelajaran'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $guru = auth()->user()->guru;
        $rencanaPembelajaranId = $request->query('rencana_pembelajaran_id');

        $rencanaPembelajaran = RencanaPembelajaran::where('id', $rencanaPembelajaranId)
            ->where('guru_id', $guru->id)
            ->first();

        if (!$rencanaPembelajaran) {
            abort(404);
        }

        return view('materi_pembelajaran.create', compact('rencanaPembelajaran'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $guru = auth()->user()->guru;
        
        $request->validate([
            'rencana_pembelajaran_id' => 'required|exists:rencana_pembelajarans,id',
            'nama_kegiatan' => 'required|string|max:255',
            'materi_pembelajaran' => 'required|string',
            'link_pembelajaran_daring' => 'nullable|url',
            'bukti_pembelajaran' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'status' => 'required|in:draft,published',
        ]);

        // Verify rencana pembelajaran belongs to this guru
        $rencanaPembelajaran = RencanaPembelajaran::where('id', $request->rencana_pembelajaran_id)
            ->where('guru_id', $guru->id)
            ->first();

        if (!$rencanaPembelajaran) {
            return back()->withErrors('Rencana pembelajaran tidak ditemukan');
        }

        $buktiPath = null;
        if ($request->hasFile('bukti_pembelajaran')) {
            $buktiPath = $request->file('bukti_pembelajaran')->store('materi_pembelajaran', 'public');
        }

        MateriPembelajaran::create([
            'guru_id' => $guru->id,
            'rencana_pembelajaran_id' => $request->rencana_pembelajaran_id,
            'nama_kegiatan' => $request->nama_kegiatan,
            'materi_pembelajaran' => $request->materi_pembelajaran,
            'link_pembelajaran_daring' => $request->link_pembelajaran_daring,
            'bukti_pembelajaran' => $buktiPath,
            'status' => $request->status,
        ]);

        return redirect()
            ->route('materi_pembelajaran.index', ['rencana_pembelajaran_id' => $request->rencana_pembelajaran_id])
            ->with('success', 'Materi pembelajaran berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(MateriPembelajaran $materiPembelajaran)
    {
        $guru = auth()->user()->guru;
        
        if ($materiPembelajaran->guru_id !== $guru->id) {
            abort(403);
        }

        return view('materi_pembelajaran.show', compact('materiPembelajaran'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MateriPembelajaran $materiPembelajaran)
    {
        $guru = auth()->user()->guru;
        
        if ($materiPembelajaran->guru_id !== $guru->id) {
            abort(403);
        }

        $rencanaPembelajaran = $materiPembelajaran->rencanaPembelajaran;
        
        return view('materi_pembelajaran.edit', compact('materiPembelajaran', 'rencanaPembelajaran'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MateriPembelajaran $materiPembelajaran)
    {
        $guru = auth()->user()->guru;
        
        if ($materiPembelajaran->guru_id !== $guru->id) {
            abort(403);
        }

        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'materi_pembelajaran' => 'required|string',
            'link_pembelajaran_daring' => 'nullable|url',
            'bukti_pembelajaran' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'status' => 'required|in:draft,published',
        ]);

        $data = [
            'nama_kegiatan' => $request->nama_kegiatan,
            'materi_pembelajaran' => $request->materi_pembelajaran,
            'link_pembelajaran_daring' => $request->link_pembelajaran_daring,
            'status' => $request->status,
        ];

        if ($request->hasFile('bukti_pembelajaran')) {
            // Delete old file if exists
            if ($materiPembelajaran->bukti_pembelajaran) {
                Storage::disk('public')->delete($materiPembelajaran->bukti_pembelajaran);
            }
            $data['bukti_pembelajaran'] = $request->file('bukti_pembelajaran')->store('materi_pembelajaran', 'public');
        }

        $materiPembelajaran->update($data);

        return redirect()
            ->route('materi_pembelajaran.index', ['rencana_pembelajaran_id' => $materiPembelajaran->rencana_pembelajaran_id])
            ->with('success', 'Materi pembelajaran berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MateriPembelajaran $materiPembelajaran)
    {
        $guru = auth()->user()->guru;
        
        if ($materiPembelajaran->guru_id !== $guru->id) {
            abort(403);
        }

        $rencanaPembelajaranId = $materiPembelajaran->rencana_pembelajaran_id;

        // Delete file if exists
        if ($materiPembelajaran->bukti_pembelajaran) {
            Storage::disk('public')->delete($materiPembelajaran->bukti_pembelajaran);
        }

        $materiPembelajaran->delete();

        return redirect()
            ->route('materi_pembelajaran.index', ['rencana_pembelajaran_id' => $rencanaPembelajaranId])
            ->with('success', 'Materi pembelajaran berhasil dihapus');
    }
}
