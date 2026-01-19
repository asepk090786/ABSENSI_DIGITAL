<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuruBkController extends Controller
{
    public function index()
    {
        $gurubk = Guru::whereHas('user', function($query) {
            $query->whereHas('role', function($q) {
                $q->where('role_name', 'Guru BK');
            });
        })->orderBy('created_at', 'desc')->get();
        
        return view('guru_bk.index', compact('gurubk'));
    }

    public function create()
    {
        $guru = Guru::all();
        return view('guru_bk.create', compact('guru'));
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

        return redirect()->route('guru_bk.index')->with('success', 'Data Guru BK berhasil ditambahkan.');
    }

    public function show($id)
    {
        $gurubk = Guru::findOrFail($id);
        return view('guru_bk.show', compact('gurubk'));
    }

    public function edit($id)
    {
        $gurubk = Guru::findOrFail($id);
        $guru = Guru::all();
        return view('guru_bk.edit', compact('gurubk', 'guru'));
    }

    public function update(Request $request, $id)
    {
        $gurubk = Guru::findOrFail($id);

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
            if ($gurubk->foto) {
                Storage::disk('public')->delete($gurubk->foto);
            }
            $validated['foto'] = $request->file('foto')->store('guru', 'public');
        }

        $gurubk->update($validated);

        return redirect()->route('guru_bk.index')->with('success', 'Data Guru BK berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $gurubk = Guru::findOrFail($id);
        
        if ($gurubk->foto) {
            Storage::disk('public')->delete($gurubk->foto);
        }

        $gurubk->delete();

        return redirect()->route('guru_bk.index')->with('success', 'Data Guru BK berhasil dihapus.');
    }
}
