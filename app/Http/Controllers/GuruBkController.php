<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;

class GuruBkController extends Controller
{
    public function index()
    {
        // Ambil guru yang menjadi Guru BK dan load relasi guru yang dipilih
        $guruBkIds = Guru::whereHas('user', function($query) {
            $query->whereHas('role', function($q) {
                $q->where('role_name', 'Guru BK');
            });
        })->pluck('id');
        
        $gurubk = Guru::with('guru')
            ->whereIn('id', $guruBkIds)
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('guru_bk.index', compact('gurubk'));
    }

    public function create()
    {
        // Ambil guru yang belum menjadi Guru BK
        $guruBkIds = Guru::whereHas('user', function($query) {
            $query->whereHas('role', function($q) {
                $q->where('role_name', 'Guru BK');
            });
        })->pluck('id');
        
        $guru = Guru::whereNotIn('id', $guruBkIds)
            ->orderBy('nama')
            ->get();
        
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

        // Convert status to is_active
        $validated['is_active'] = $validated['status'] === 'Aktif' ? 1 : 0;
        unset($validated['status']);

        // Create guru
        $guru = Guru::create($validated);
        
        // Create user with role "Guru BK"
        $guruBkRole = Role::where('role_name', 'Guru BK')->first();
        if ($guruBkRole) {
            User::create([
                'name' => $validated['nama'],
                'username' => 'guru_bk_' . $guru->id,
                'email' => $validated['email'] ?? null,
                'password' => Hash::make('password123'),
                'role_id' => $guruBkRole->id,
                'guru_id' => $guru->id,
                'is_active' => $validated['is_active'],
            ]);
        }

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
        
        // Ambil guru yang belum menjadi Guru BK (kecuali guru yang sedang diedit)
        $guruBkIds = Guru::whereHas('user', function($query) {
            $query->whereHas('role', function($q) {
                $q->where('role_name', 'Guru BK');
            });
        })->where('id', '!=', $id)->pluck('id');
        
        $guru = Guru::whereNotIn('id', $guruBkIds)
            ->orderBy('nama')
            ->get();
        
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

        // Convert status to is_active
        $validated['is_active'] = $validated['status'] === 'Aktif' ? 1 : 0;
        unset($validated['status']);

        $gurubk->update($validated);
        
        // Update user jika ada
        $user = User::where('guru_id', $gurubk->id)->first();
        if ($user) {
            $user->update([
                'name' => $validated['nama'],
                'email' => $validated['email'] ?? null,
                'is_active' => $validated['is_active'],
            ]);
        }

        return redirect()->route('guru_bk.index')->with('success', 'Data Guru BK berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $gurubk = Guru::findOrFail($id);
        
        if ($gurubk->foto) {
            Storage::disk('public')->delete($gurubk->foto);
        }
        
        // Delete user jika ada
        User::where('guru_id', $gurubk->id)->delete();

        $gurubk->delete();

        return redirect()->route('guru_bk.index')->with('success', 'Data Guru BK berhasil dihapus.');
    }
}
