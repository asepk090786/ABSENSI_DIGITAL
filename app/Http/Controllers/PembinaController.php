<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PembinaController extends Controller
{
    public function index()
    {
        $pembina = Guru::whereHas('user', function($query) {
            $query->whereHas('role', function($q) {
                $q->where('role_name', 'Pembina');
            });
        })->orderBy('created_at', 'desc')->get();
        
        return view('pembina.index', compact('pembina'));
    }

    public function create()
    {
        $guru = Guru::all();
        return view('pembina.create', compact('guru'));
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

        return redirect()->route('pembina.index')->with('success', 'Data Pembina berhasil ditambahkan.');
    }

    public function show($id)
    {
        $pembina = Guru::findOrFail($id);
        return view('pembina.show', compact('pembina'));
    }

    public function edit($id)
    {
        $pembina = Guru::findOrFail($id);
        $guru = Guru::all();
        return view('pembina.edit', compact('pembina', 'guru'));
    }

    public function update(Request $request, $id)
    {
        $pembina = Guru::findOrFail($id);

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
            if ($pembina->foto) {
                Storage::disk('public')->delete($pembina->foto);
            }
            $validated['foto'] = $request->file('foto')->store('guru', 'public');
        }

        $pembina->update($validated);

        return redirect()->route('pembina.index')->with('success', 'Data Pembina berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pembina = Guru::findOrFail($id);
        
        if ($pembina->foto) {
            Storage::disk('public')->delete($pembina->foto);
        }

        $pembina->delete();

        return redirect()->route('pembina.index')->with('success', 'Data Pembina berhasil dihapus.');
    }
}
