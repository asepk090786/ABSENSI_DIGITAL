<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class GuruPiketController extends Controller
{
    public function index()
    {
        $gurupiket = Guru::with('user')->whereHas('user', function($query) {
            $query->whereHas('roles', function($q) {
                $q->where('role_name', 'Guru Piket');
            })->orWhereHas('role', function($q) {
                $q->where('role_name', 'Guru Piket');
            });
        })->orderBy('created_at', 'desc')->get();
        
        return view('guru_piket.index', compact('gurupiket'));
    }

    public function create()
    {
        $guruPiketIds = Guru::whereHas('user', function($query) {
            $query->whereHas('roles', function($q) {
                $q->where('role_name', 'Guru Piket');
            })->orWhereHas('role', function($q) {
                $q->where('role_name', 'Guru Piket');
            });
        })->pluck('id');

        $guru = Guru::with('user')
            ->whereNotIn('id', $guruPiketIds)
            ->orderBy('nama')
            ->get();
        return view('guru_piket.create', compact('guru'));
    }

    public function store(Request $request)
    {
        $rules = [
            'guru_id' => 'nullable|exists:guru,id',
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:guru',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'alamat' => 'nullable|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ];

        if ($request->filled('guru_id')) {
            $rules['nama'] = 'nullable|string|max:255';
            $rules['nip'] = 'nullable|string|max:50';
            $rules['email'] = 'nullable|email|max:100';
        }

        $validated = $request->validate($rules);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('guru', 'public');
        }

        $validated['is_active'] = $validated['status'] === 'Aktif' ? 1 : 0;
        unset($validated['status']);

        if ($request->filled('guru_id')) {
            $guru = Guru::findOrFail($validated['guru_id']);
            $guruData = array_intersect_key($validated, array_flip([
                'nama',
                'nip',
                'alamat',
                'telepon',
                'email',
                'foto',
            ]));

            if (! empty($guruData)) {
                $guru->update($guruData);
            }

            $guruPiketRole = Role::where('role_name', 'Guru Piket')->first();
            if ($guruPiketRole) {
                $user = $guru->user;
                if ($user) {
                    $user->update([
                        'name' => $guru->nama,
                        'email' => $guru->email,
                        'is_active' => $validated['is_active'],
                    ]);
                    $user->roles()->syncWithoutDetaching([$guruPiketRole->id]);
                } else {
                    if (empty($guru->email)) {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['email' => 'Email wajib diisi untuk membuat akun guru piket.']);
                    }

                    $roleGuru = Role::where('role_name', 'Guru')->first();
                    $user = User::create([
                        'name' => $guru->nama,
                        'username' => 'guru_piket_' . $guru->id,
                        'email' => $guru->email,
                        'password' => Hash::make('password123'),
                        'role_id' => $roleGuru ? $roleGuru->id : $guruPiketRole->id,
                        'guru_id' => $guru->id,
                        'is_active' => $validated['is_active'],
                    ]);
                    $user->roles()->syncWithoutDetaching([$guruPiketRole->id]);
                }
            }

            return redirect()->route('guru_piket.index')->with('success', 'Data Guru Piket berhasil ditambahkan.');
        }

        $guru = Guru::create($validated);

        $guruPiketRole = Role::where('role_name', 'Guru Piket')->first();
        if ($guruPiketRole) {
            if (empty($validated['email'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['email' => 'Email wajib diisi untuk membuat akun guru piket.']);
            }

            $roleGuru = Role::where('role_name', 'Guru')->first();
            $user = User::create([
                'name' => $validated['nama'],
                'username' => 'guru_piket_' . $guru->id,
                'email' => $validated['email'],
                'password' => Hash::make('password123'),
                'role_id' => $roleGuru ? $roleGuru->id : $guruPiketRole->id,
                'guru_id' => $guru->id,
                'is_active' => $validated['is_active'],
            ]);
            $user->roles()->syncWithoutDetaching([$guruPiketRole->id]);
        }

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

        $validated['is_active'] = $validated['status'] === 'Aktif' ? 1 : 0;
        unset($validated['status']);

        $gurupiket->update($validated);

        $user = User::where('guru_id', $gurupiket->id)->first();
        if ($user) {
            $user->update([
                'name' => $validated['nama'],
                'email' => $validated['email'] ?? $user->email,
                'is_active' => $validated['is_active'],
            ]);
            $guruPiketRole = Role::where('role_name', 'Guru Piket')->first();
            if ($guruPiketRole) {
                $user->roles()->syncWithoutDetaching([$guruPiketRole->id]);
            }
        } else {
            $guruPiketRole = Role::where('role_name', 'Guru Piket')->first();
            if ($guruPiketRole) {
                if (empty($validated['email'])) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['email' => 'Email wajib diisi untuk membuat akun guru piket.']);
                }

                $roleGuru = Role::where('role_name', 'Guru')->first();
                $user = User::create([
                    'name' => $validated['nama'],
                    'username' => 'guru_piket_' . $gurupiket->id,
                    'email' => $validated['email'],
                    'password' => Hash::make('password123'),
                    'role_id' => $roleGuru ? $roleGuru->id : $guruPiketRole->id,
                    'guru_id' => $gurupiket->id,
                    'is_active' => $validated['is_active'],
                ]);
                $user->roles()->syncWithoutDetaching([$guruPiketRole->id]);
            }
        }

        return redirect()->route('guru_piket.index')->with('success', 'Data Guru Piket berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $gurupiket = Guru::findOrFail($id);
        
        if ($gurupiket->foto) {
            Storage::disk('public')->delete($gurupiket->foto);
        }

        $user = User::where('guru_id', $gurupiket->id)->first();
        if ($user) {
            $guruPiketRole = Role::where('role_name', 'Guru Piket')->first();
            if ($guruPiketRole) {
                $user->roles()->detach($guruPiketRole->id);
            }
        }

        return redirect()->route('guru_piket.index')->with('success', 'Data Guru Piket berhasil dihapus.');
    }
}
