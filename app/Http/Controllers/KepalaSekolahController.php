<?php

namespace App\Http\Controllers;

use App\Models\KepalaSekolah;
use App\Models\Guru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KepalaSekolahController extends Controller
{
    public function index()
    {
        $kepalaSekolah = KepalaSekolah::with('guru')->orderBy('tanggal_mulai_jabatan', 'desc')->get();
        return view('kepala_sekolah.index', compact('kepalaSekolah'));
    }

    public function create()
    {
        $guru = Guru::all();
        return view('kepala_sekolah.create', compact('guru'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id' => 'nullable|exists:guru,id',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:kepala_sekolah',
            'pangkat_golongan' => 'nullable|string|max:100',
            'tanggal_mulai_jabatan' => 'required|date',
            'tanggal_selesai_jabatan' => 'nullable|date|after:tanggal_mulai_jabatan',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('kepala_sekolah', 'public');
        }

        KepalaSekolah::create($validated);

        return redirect()->route('kepala_sekolah.index')->with('success', 'Data kepala sekolah berhasil ditambahkan.');
    }

    public function show(KepalaSekolah $kepalaSekolah)
    {
        $kepalaSekolah->load('guru');
        return view('kepala_sekolah.show', compact('kepalaSekolah'));
    }

    public function edit(KepalaSekolah $kepalaSekolah)
    {
        $guru = Guru::all();
        return view('kepala_sekolah.edit', compact('kepalaSekolah', 'guru'));
    }

    public function update(Request $request, KepalaSekolah $kepalaSekolah)
    {
        $validated = $request->validate([
            'guru_id' => 'nullable|exists:guru,id',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:kepala_sekolah,nip,' . $kepalaSekolah->id,
            'pangkat_golongan' => 'nullable|string|max:100',
            'tanggal_mulai_jabatan' => 'required|date',
            'tanggal_selesai_jabatan' => 'nullable|date|after:tanggal_mulai_jabatan',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($kepalaSekolah->foto) {
                Storage::disk('public')->delete($kepalaSekolah->foto);
            }
            $validated['foto'] = $request->file('foto')->store('kepala_sekolah', 'public');
        }

        $kepalaSekolah->update($validated);

        return redirect()->route('kepala_sekolah.index')->with('success', 'Data kepala sekolah berhasil diperbarui.');
    }

    public function destroy(KepalaSekolah $kepalaSekolah)
    {
        if ($kepalaSekolah->foto) {
            Storage::disk('public')->delete($kepalaSekolah->foto);
        }
        
        $kepalaSekolah->delete();

        return redirect()->route('kepala_sekolah.index')->with('success', 'Data kepala sekolah berhasil dihapus.');
    }
}
