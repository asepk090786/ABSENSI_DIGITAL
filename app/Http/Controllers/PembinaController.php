<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Role;
use App\Models\User;
use App\Traits\GuruRoleTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class PembinaController extends Controller
{
    use GuruRoleTrait;

    public function index()
    {
        $pembina = $this->queryGuruByRole('Pembina')
            ->orderBy('created_at', 'desc')->get();
        
        return view('pembina.index', compact('pembina'));
    }

    public function create()
    {
        $pembinaIds = $this->getGuruIdsByRole('Pembina');

        $guru = Guru::with('user')
            ->whereNotIn('id', $pembinaIds)
            ->orderBy('nama')
            ->get();
        return view('pembina.create', compact('guru'));
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

        $validated = $this->convertStatusToIsActive($validated);

        if ($request->filled('guru_id')) {
            $guru = Guru::findOrFail($validated['guru_id']);
            $guruData = $this->filterGuruUpdateData($validated);

            if (! empty($guruData)) {
                $guru->update($guruData);
            }

            $pembinaRole = Role::where('role_name', 'Pembina')->first();
            if ($pembinaRole) {
                $user = $guru->user;
                if ($user) {
                    $user->update([
                        'name' => $guru->nama,
                        'email' => $guru->email,
                        'is_active' => $validated['is_active'],
                    ]);
                    $this->syncUserRole($user, 'Pembina');
                } else {
                    if (empty($guru->email)) {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['email' => 'Email wajib diisi untuk membuat akun pembina.']);
                    }

                    $roleGuru = Role::where('role_name', 'Guru')->first();
                    $user = User::create([
                        'name' => $guru->nama,
                        'username' => 'pembina_' . $guru->id,
                        'email' => $guru->email,
                        'password' => Hash::make('password123'),
                        'role_id' => $roleGuru ? $roleGuru->id : $pembinaRole->id,
                        'guru_id' => $guru->id,
                        'is_active' => $validated['is_active'],
                    ]);
                    $this->syncUserRole($user, 'Pembina');
                }
            }

            return redirect()->route('pembina.index')->with('success', 'Data Pembina berhasil ditambahkan.');
        }

        $guru = Guru::create($validated);

        $pembinaRole = Role::where('role_name', 'Pembina')->first();
        if ($pembinaRole) {
            if (empty($validated['email'])) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['email' => 'Email wajib diisi untuk membuat akun pembina.']);
            }

            $roleGuru = Role::where('role_name', 'Guru')->first();
            $user = User::create([
                'name' => $validated['nama'],
                'username' => 'pembina_' . $guru->id,
                'email' => $validated['email'],
                'password' => Hash::make('password123'),
                'role_id' => $roleGuru ? $roleGuru->id : $pembinaRole->id,
                'guru_id' => $guru->id,
                'is_active' => $validated['is_active'],
            ]);
            $this->syncUserRole($user, 'Pembina');
        }

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

        $validated = $this->convertStatusToIsActive($validated);

        $pembina->update($validated);

        $user = User::where('guru_id', $pembina->id)->first();
        if ($user) {
            $user->update([
                'name' => $validated['nama'],
                'email' => $validated['email'] ?? $user->email,
                'is_active' => $validated['is_active'],
            ]);
            $this->syncUserRole($user, 'Pembina');
        } else {
            $pembinaRole = Role::where('role_name', 'Pembina')->first();
            if ($pembinaRole) {
                if (empty($validated['email'])) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['email' => 'Email wajib diisi untuk membuat akun pembina.']);
                }

                $roleGuru = Role::where('role_name', 'Guru')->first();
                $user = User::create([
                    'name' => $validated['nama'],
                    'username' => 'pembina_' . $pembina->id,
                    'email' => $validated['email'],
                    'password' => Hash::make('password123'),
                    'role_id' => $roleGuru ? $roleGuru->id : $pembinaRole->id,
                    'guru_id' => $pembina->id,
                    'is_active' => $validated['is_active'],
                ]);
                $this->syncUserRole($user, 'Pembina');
            }
        }

        return redirect()->route('pembina.index')->with('success', 'Data Pembina berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $pembina = Guru::findOrFail($id);
        
        if ($pembina->foto) {
            Storage::disk('public')->delete($pembina->foto);
        }

        $user = User::where('guru_id', $pembina->id)->first();
        if ($user) {
            $this->detachUserRole($user, 'Pembina');
        }

        return redirect()->route('pembina.index')->with('success', 'Data Pembina berhasil dihapus.');
    }
}
