<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WakilKepalaSekolahController extends Controller
{
    public function index()
    {
        $wakil = Guru::whereHas('user', function($query) {
            $query->whereHas('role', function($q) {
                $q->where('role_name', 'Wakil Kepala Sekolah');
            });
        })->orderBy('created_at', 'desc')->get();
        
        return view('wakil_kepala_sekolah.index', compact('wakil'));
    }

    public function create()
    {
        $guru = Guru::all();
        return view('wakil_kepala_sekolah.create', compact('guru'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id' => 'nullable|exists:guru,id',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:guru',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('guru', 'public');
        }

        Guru::create($validated);

        return redirect()->route('wakil_kepala_sekolah.index')->with('success', 'Data Wakil Kepala Sekolah berhasil ditambahkan.');
    }

    public function show($id)
    {
        $wakil = Guru::findOrFail($id);
        return view('wakil_kepala_sekolah.show', compact('wakil'));
    }

    public function edit($id)
    {
        $wakil = Guru::findOrFail($id);
        $guru = Guru::all();
        return view('wakil_kepala_sekolah.edit', compact('wakil', 'guru'));
    }

    public function update(Request $request, $id)
    {
        $wakil = Guru::findOrFail($id);

        $validated = $request->validate([
            'guru_id' => 'nullable|exists:guru,id',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:guru,nip,' . $id,
            'status' => 'required|in:Aktif,Tidak Aktif',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($wakil->foto) {
                Storage::disk('public')->delete($wakil->foto);
            }
            $validated['foto'] = $request->file('foto')->store('guru', 'public');
        }

        $wakil->update($validated);

        return redirect()->route('wakil_kepala_sekolah.index')->with('success', 'Data Wakil Kepala Sekolah berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $wakil = Guru::findOrFail($id);
        
        if ($wakil->foto) {
            Storage::disk('public')->delete($wakil->foto);
        }

        $wakil->delete();

        return redirect()->route('wakil_kepala_sekolah.index')->with('success', 'Data Wakil Kepala Sekolah berhasil dihapus.');
    }
}
