<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JadwalKbm;
use App\Models\Role;
use App\Models\User;
use App\Traits\GuruRoleTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class GuruPiketController extends Controller
{
    use GuruRoleTrait;

    public function index()
    {
        $gurupiket = $this->queryGuruByRole('Guru Piket')
            ->orderBy('created_at', 'desc')->get();

        $workDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $guruByHari = collect($workDays)->mapWithKeys(function ($hari) use ($gurupiket) {
            $items = $gurupiket->filter(function ($guru) use ($hari) {
                $hariPiket = (array) ($guru->hari_piket ?? []);
                return in_array($hari, $hariPiket, true);
            })->values();

            return [$hari => $items];
        });
        $hasAny = $guruByHari->flatten(1)->isNotEmpty();
        
        return view('guru_piket.index', compact('gurupiket', 'workDays', 'guruByHari', 'hasAny'));
    }

    public function create()
    {
        $allHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $guruPiketIds = $this->getGuruIdsByRole('Guru Piket');

        $guru = Guru::with('user')
            ->whereNotIn('id', $guruPiketIds)
            ->orderBy('nama')
            ->get();

        $guruIds = $guru->pluck('id');
        $jadwalHariByGuru = JadwalKbm::whereIn('guru_id', $guruIds)
            ->select('guru_id', 'hari')
            ->distinct()
            ->get()
            ->groupBy('guru_id')
            ->map(function ($items) {
                return $items->pluck('hari')->unique()->values()->all();
            });

        $availableHariByGuru = $guruIds->mapWithKeys(function ($id) use ($allHari, $jadwalHariByGuru) {
            $hariMengajar = $jadwalHariByGuru->get($id, []);
            return [$id => array_values(array_diff($allHari, $hariMengajar))];
        })->all();

        return view('guru_piket.create', compact('guru', 'allHari', 'availableHariByGuru'));
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
            'hari_piket' => 'nullable|array',
            'hari_piket.*' => 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
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
            if (! empty($validated['hari_piket'])) {
                $hariMengajar = JadwalKbm::where('guru_id', $guru->id)
                    ->select('hari')
                    ->distinct()
                    ->pluck('hari')
                    ->unique()
                    ->values()
                    ->all();
                $hariBentrok = array_intersect($validated['hari_piket'], $hariMengajar);
                if (! empty($hariBentrok)) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['hari_piket' => 'Hari piket bentrok dengan jadwal mengajar.']);
                }
            }

            $guruData = $this->filterGuruUpdateData($validated, ['hari_piket']);

            if (! empty($guruData)) {
                $guru->update($guruData);
            }

            $guruPiketRole = Role::where('role_name', 'Guru Piket')->first();
            if ($guruPiketRole) {
                $user = $guru->user;
                if ($user) {
                    if (empty($guru->email) && ! empty($user->email)) {
                        $guru->update(['email' => $user->email]);
                    }

                    $userData = [
                        'name' => $guru->nama,
                        'is_active' => $validated['is_active'],
                    ];

                    if (! empty($user->email)) {
                        $userData['email'] = $user->email;
                    }

                    $user->update($userData);
                    $this->syncUserRole($user, 'Guru Piket');
                } else {
                    if (empty($guru->email)) {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['email' => 'Email wajib diisi untuk membuat akun guru piket.']);
                    }

                    $emailExists = User::where('email', $guru->email)->exists();
                    if ($emailExists) {
                        return redirect()->back()
                            ->withInput()
                            ->withErrors(['email' => 'Email sudah digunakan oleh akun lain.']);
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
                    $this->syncUserRole($user, 'Guru Piket');
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
            $this->syncUserRole($user, 'Guru Piket');
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
        $allHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $hariMengajar = JadwalKbm::where('guru_id', $gurupiket->id)
            ->select('hari')
            ->distinct()
            ->pluck('hari')
            ->unique()
            ->values()
            ->all();
        $availableHari = array_values(array_diff($allHari, $hariMengajar));
        $guru = Guru::all();
        return view('guru_piket.edit', compact('gurupiket', 'guru', 'allHari', 'availableHari'));
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
            'hari_piket' => 'nullable|array',
            'hari_piket.*' => 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            if ($gurupiket->foto) {
                Storage::disk('public')->delete($gurupiket->foto);
            }
            $validated['foto'] = $request->file('foto')->store('guru', 'public');
        }

        if (! empty($validated['hari_piket'])) {
            $hariMengajar = JadwalKbm::where('guru_id', $gurupiket->id)
                ->select('hari')
                ->distinct()
                ->pluck('hari')
                ->unique()
                ->values()
                ->all();
            $hariBentrok = array_intersect($validated['hari_piket'], $hariMengajar);
            if (! empty($hariBentrok)) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['hari_piket' => 'Hari piket bentrok dengan jadwal mengajar.']);
            }
        }

        $validated = $this->convertStatusToIsActive($validated);

        $gurupiket->update($validated);

        $user = User::where('guru_id', $gurupiket->id)->first();
        if ($user) {
            $user->update([
                'name' => $validated['nama'],
                'email' => $validated['email'] ?? $user->email,
                'is_active' => $validated['is_active'],
            ]);
            $this->syncUserRole($user, 'Guru Piket');
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
                $this->syncUserRole($user, 'Guru Piket');
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
            $this->detachUserRole($user, 'Guru Piket');
        }

        return redirect()->route('guru_piket.index')->with('success', 'Data Guru Piket berhasil dihapus.');
    }
}
