<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuruBkController extends Controller
{
    private function resolveUserForGuru(Guru $guru): ?User
    {
        if ($guru->user) {
            return $guru->user;
        }

        if (!empty($guru->email)) {
            $userByEmail = User::where('email', $guru->email)->first();
            if ($userByEmail) {
                if (empty($userByEmail->guru_id)) {
                    $userByEmail->update(['guru_id' => $guru->id]);
                }
                return $userByEmail;
            }
        }

        if (!empty($guru->nip)) {
            $userByNip = User::where('nip', $guru->nip)->first();
            if ($userByNip) {
                if (empty($userByNip->guru_id)) {
                    $userByNip->update(['guru_id' => $guru->id]);
                }
                return $userByNip;
            }
        }

        return null;
    }

    public function index()
    {
        $gurubk = Guru::with('user')
            ->whereHas('user', function($query) {
                $query->whereHas('roles', function($q) {
                    $q->where('role_name', 'Guru BK');
                })->orWhereHas('role', function($q) {
                    $q->where('role_name', 'Guru BK');
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('guru_bk.index', compact('gurubk'));
    }

    public function create()
    {
        $guruBkIds = Guru::whereHas('user', function($query) {
            $query->whereHas('roles', function($q) {
                $q->where('role_name', 'Guru BK');
            })->orWhereHas('role', function($q) {
                $q->where('role_name', 'Guru BK');
            });
        })->pluck('id');

        $guru = Guru::with('user')
            ->whereNotIn('id', $guruBkIds)
            ->orderBy('nama')
            ->get();
        
        return view('guru_bk.create', compact('guru'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'nama' => 'nullable|string|max:255',
            'nip' => 'nullable|string|max:50',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $guru = Guru::with('user')->findOrFail($validated['guru_id']);
        $user = $this->resolveUserForGuru($guru);

        if (! $user) {
            return redirect()->back()
                ->withInput()
                    ->withErrors(['guru_id' => 'Guru terpilih belum memiliki akun user. Buat akun guru terlebih dahulu di menu akun pengguna.']);
        }

        if (!empty($validated['email'])) {
            $emailExists = User::where('email', $validated['email'])
                ->where('id', '!=', $user->id)
                ->exists();

            if ($emailExists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['email' => 'Email sudah digunakan oleh akun lain.']);
            }
        }

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('guru', 'public');
        }

        $validated['is_active'] = $validated['status'] === 'Aktif' ? 1 : 0;
        unset($validated['status']);

        $guruData = array_intersect_key($validated, array_flip([
            'nama',
            'nip',
            'alamat',
            'telepon',
            'email',
            'foto',
            'is_active',
        ]));

        $guruData = array_filter($guruData, function ($value) {
            return $value !== null && $value !== '';
        });

        if (! empty($guruData)) {
            $guru->update($guruData);
        }

        $guruBkRole = Role::where('role_name', 'Guru BK')->first();
        if ($guruBkRole) {
            $user->update([
                'name' => $guru->nama,
                'email' => $validated['email'] ?? $user->email,
                'is_active' => $validated['is_active'],
            ]);
            $user->roles()->syncWithoutDetaching([$guruBkRole->id]);
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

        $guruBkIds = Guru::whereHas('user', function($query) {
            $query->whereHas('roles', function($q) {
                $q->where('role_name', 'Guru BK');
            })->orWhereHas('role', function($q) {
                $q->where('role_name', 'Guru BK');
            });
        })->where('id', '!=', $id)->pluck('id');

        $guru = Guru::with('user')
            ->whereNotIn('id', $guruBkIds)
            ->orderBy('nama')
            ->get();
        
        return view('guru_bk.edit', compact('gurubk', 'guru'));
    }

    public function update(Request $request, $id)
    {
        $gurubk = Guru::findOrFail($id);
        $user = User::where('guru_id', $gurubk->id)->first();

        if (! $user) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['guru_id' => 'Guru BK ini belum memiliki akun user.']);
        }

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

        if (!empty($validated['email'])) {
            $emailExists = User::where('email', $validated['email'])
                ->where('id', '!=', $user->id)
                ->exists();

            if ($emailExists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['email' => 'Email sudah digunakan oleh akun lain.']);
            }
        }

        if ($request->hasFile('foto')) {
            if ($gurubk->foto) {
                Storage::disk('public')->delete($gurubk->foto);
            }
            $validated['foto'] = $request->file('foto')->store('guru', 'public');
        }

        $validated['is_active'] = $validated['status'] === 'Aktif' ? 1 : 0;
        unset($validated['status']);

        $gurubk->update($validated);

        $user->update([
            'name' => $validated['nama'],
            'email' => $validated['email'] ?? $user->email,
            'is_active' => $validated['is_active'],
        ]);

        $guruBkRole = Role::where('role_name', 'Guru BK')->first();
        if ($guruBkRole) {
            $user->roles()->syncWithoutDetaching([$guruBkRole->id]);
        }

        return redirect()->route('guru_bk.index')->with('success', 'Data Guru BK berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $gurubk = Guru::findOrFail($id);

        $user = User::where('guru_id', $gurubk->id)->first();
        if ($user) {
            $guruBkRole = Role::where('role_name', 'Guru BK')->first();
            if ($guruBkRole) {
                $user->roles()->detach($guruBkRole->id);
            }
        }

        return redirect()->route('guru_bk.index')->with('success', 'Data Guru BK berhasil dihapus.');
    }
}
