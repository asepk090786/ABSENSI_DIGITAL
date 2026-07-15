<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\JadwalKbm;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class GuruPiketController extends Controller
{
    /**
     * Check if the authenticated user is an admin
     * 
     * @return bool
     */
    private function isAdmin()
    {
        return auth()->check() && auth()->user()->role && auth()->user()->role->role_name === 'Admin';
    }

    /**
     * Abort with 403 if user is not admin
     */
    private function authorizeAdmin()
    {
        if (!$this->isAdmin()) {
            abort(403, 'Akses ditolak. Hanya admin yang dapat mengakses fitur ini.');
        }
    }

    public function index()
    {
        $gurupiket = Guru::with('user')
            ->whereHas('user')
            ->whereNotNull('hari_piket')
            ->orderBy('nama')
            ->get();

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
        $this->authorizeAdmin();

        $allHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        $guru = Guru::with('user')
            ->whereHas('user')
            ->where(function ($query) {
                $query->whereNull('hari_piket')
                    ->orWhere('hari_piket', '[]')
                    ->orWhere('hari_piket', '');
            })
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
        $this->authorizeAdmin();

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

        $validated['is_active'] = $validated['status'] === 'Aktif' ? 1 : 0;
        unset($validated['status']);

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

            $guruData = array_intersect_key($validated, array_flip([
                'nama',
                'nip',
                'alamat',
                'telepon',
                'email',
                'hari_piket',
                'foto',
            ]));

            $guruData = array_filter($guruData, function ($value) {
                return $value !== null && $value !== '';
            });

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
                    $user->roles()->syncWithoutDetaching([$guruPiketRole->id]);
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
        $this->authorizeAdmin();

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
        $this->authorizeAdmin();

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

    public function generate(Request $request)
    {
        $this->authorizeAdmin();

        $workDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = $tahun ? DB::table('semester')->where('tahun_ajaran_id', $tahun->id)->where('is_active', 1)->first() : null;

        if (! $tahun || ! $semester) {
            return redirect()->route('guru_piket.index')->with('error', 'Tahun ajaran atau semester aktif belum ditetapkan.');
        }

        $gurupiket = Guru::with('user')
            ->whereHas('user')
            ->orderBy('nama')
            ->get();

        if ($gurupiket->isEmpty()) {
            return redirect()->route('guru_piket.index')->with('error', 'Tidak ada data guru. Tambah data guru terlebih dahulu.');
        }

        $guruFreeDays = $gurupiket->mapWithKeys(function ($guru) use ($workDays, $tahun, $semester) {
            $hariMengajar = JadwalKbm::where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $tahun->id)
                ->where('semester_id', $semester->id)
                ->select('hari')
                ->distinct()
                ->pluck('hari')
                ->all();

            $freeDays = array_values(array_diff($workDays, $hariMengajar));
            return [$guru->id => $freeDays];
        });

        $assignments = [];
        $maxDaysPerTeacher = 2;

        $guruIds = $guruFreeDays->keys()->values()->all();
        $dayOffset = 0;

        foreach ($guruIds as $guruId) {
            $freeDays = $guruFreeDays[$guruId];

            if (empty($freeDays)) {
                continue;
            }

            $assignedDays = [];
            foreach (range(0, $maxDaysPerTeacher - 1) as $i) {
                $dayIndex = ($dayOffset + $i) % count($freeDays);
                $assignedDays[] = $freeDays[$dayIndex];
            }

            $assignments[$guruId] = $assignedDays;
            $dayOffset = ($dayOffset + 1) % count($workDays);
        }

        foreach ($gurupiket as $guru) {
            $guru->hari_piket = $assignments[$guru->id] ?? [];
            $guru->save();
        }

        $assignedDays = collect($assignments)->flatten()->unique()->values()->all();
        $unassignedDays = collect($workDays)->diff($assignedDays)->values()->all();

        if (empty($unassignedDays)) {
            $message = 'Jadwal piket otomatis berhasil dibuat.';
        } else {
            $message = 'Jadwal piket dibuat, tetapi hari ' . implode(', ', $unassignedDays) . ' belum terisi karena tidak ada guru yang bebas pada hari tersebut.';
        }

        return redirect()->route('guru_piket.index')->with('success', $message);
    }

    public function destroy($id)
    {
        $this->authorizeAdmin();

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

        $gurupiket->hari_piket = null;
        $gurupiket->save();

        return redirect()->route('guru_piket.index')->with('success', 'Data Guru Piket berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $this->authorizeAdmin();

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:guru,id',
        ]);

        $guruList = Guru::whereIn('id', $request->ids)->get();
        $deleted = 0;

        foreach ($guruList as $gurupiket) {
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

            $gurupiket->hari_piket = null;
            $gurupiket->save();
            $deleted++;
        }

        return redirect()->route('guru_piket.index')->with('success', "$deleted data Guru Piket berhasil dihapus.");
    }
}
