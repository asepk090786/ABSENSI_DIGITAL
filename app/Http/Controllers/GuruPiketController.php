<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuruPiketController extends Controller
{
    public function index()
    {
        $gurupiket = Guru::whereHas('user', function($query) {
            $query->whereHas('role', function($q) {
                $q->where('role_name', 'Guru Piket');
            });
        })->orderBy('created_at', 'desc')->get();
        
        return view('guru_piket.index', compact('gurupiket'));
    }

    public function create()
    {
        $guru = Guru::all();
        return view('guru_piket.create', compact('guru'));
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

        return redirect()->route('guru_piket.index')->with('success', 'Data Guru Piket berhasil ditambahkan.');
    }

    public function show($id)
    {
        $gurupiket = Guru::findOrFail($id);
        return view('guru_piket.show', compact('gurupiket'));
    }

    public function edit($id)
    {
        $gurupiket = Guru::findOrFail($id);
        $guru = Guru::all();
        return view('guru_piket.edit', compact('gurupiket', 'guru'));
    }

    public function update(Request $request, $id)
    {
        $gurupiket = Guru::findOrFail($id);

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
            if ($gurupiket->foto) {
                Storage::disk('public')->delete($gurupiket->foto);
            }
            $validated['foto'] = $request->file('foto')->store('guru', 'public');
        }

        $gurupiket->update($validated);

        return redirect()->route('guru_piket.index')->with('success', 'Data Guru Piket berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $gurupiket = Guru::findOrFail($id);
        
        if ($gurupiket->foto) {
            Storage::disk('public')->delete($gurupiket->foto);
        }

        $gurupiket->delete();

        return redirect()->route('guru_piket.index')->with('success', 'Data Guru Piket berhasil dihapus.');
    }
}
