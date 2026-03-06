<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\User;
use App\Models\Role;
use App\Traits\GuruRoleTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;

class GuruBkController extends Controller
{
    use GuruRoleTrait;

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
        $hasGuruBkKelasColumn = Schema::hasTable('kelas') && Schema::hasColumn('kelas', 'guru_bk_id');

        $guruBkQuery = $this->queryGuruByRole('Guru BK')
            ->orderBy('created_at', 'desc');

        if ($hasGuruBkKelasColumn) {
            $guruBkQuery->with('kelasBinaanBk');
        }

        $gurubk = $guruBkQuery->get();
        
        return view('guru_bk.index', compact('gurubk', 'hasGuruBkKelasColumn'));
    }

    public function create()
    {
        $guruBkIds = $this->getGuruIdsByRole('Guru BK');

        $guru = Guru::with('user')
            ->whereNotIn('id', $guruBkIds)
            ->orderBy('nama')
            ->get();

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        
        return view('guru_bk.create', compact('guru', 'kelasList'));
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
            'kelas_binaan' => 'nullable|array',
            'kelas_binaan.*' => 'exists:kelas,id',
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

        $validated = $this->convertStatusToIsActive($validated);

        $guruData = $this->filterGuruUpdateData($validated);

        if (! empty($guruData)) {
            $guru->update($guruData);
        }

        $kelasBinaanIds = collect($validated['kelas_binaan'] ?? [])
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        Kelas::where('guru_bk_id', $guru->id)->update(['guru_bk_id' => null]);
        if (! empty($kelasBinaanIds)) {
            Kelas::whereIn('id', $kelasBinaanIds)->update(['guru_bk_id' => $guru->id]);
        }

        $guruBkRole = Role::where('role_name', 'Guru BK')->first();
        if ($guruBkRole) {
            $user->update([
                'name' => $guru->nama,
                'email' => $validated['email'] ?? $user->email,
                'is_active' => $validated['is_active'],
            ]);
            $this->syncUserRole($user, 'Guru BK');
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

        $guruBkIds = $this->getGuruIdsByRole('Guru BK', (int) $id);

        $guru = Guru::with('user')
            ->whereNotIn('id', $guruBkIds)
            ->orderBy('nama')
            ->get();

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $kelasBinaanIds = Kelas::where('guru_bk_id', $id)->pluck('id')->all();
        
        return view('guru_bk.edit', compact('gurubk', 'guru', 'kelasList', 'kelasBinaanIds'));
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
            'kelas_binaan' => 'nullable|array',
            'kelas_binaan.*' => 'exists:kelas,id',
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

        $validated = $this->convertStatusToIsActive($validated);

        $kelasBinaanIds = collect($validated['kelas_binaan'] ?? [])
            ->filter()
            ->map(fn ($kelasId) => (int) $kelasId)
            ->unique()
            ->values()
            ->all();

        unset($validated['kelas_binaan']);

        $gurubk->update($validated);

        Kelas::where('guru_bk_id', $gurubk->id)->update(['guru_bk_id' => null]);
        if (! empty($kelasBinaanIds)) {
            Kelas::whereIn('id', $kelasBinaanIds)->update(['guru_bk_id' => $gurubk->id]);
        }

        $user->update([
            'name' => $validated['nama'],
            'email' => $validated['email'] ?? $user->email,
            'is_active' => $validated['is_active'],
        ]);

        $this->syncUserRole($user, 'Guru BK');

        return redirect()->route('guru_bk.index')->with('success', 'Data Guru BK berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $gurubk = Guru::findOrFail($id);

        Kelas::where('guru_bk_id', $gurubk->id)->update(['guru_bk_id' => null]);

        $user = User::where('guru_id', $gurubk->id)->first();
        if ($user) {
            $this->detachUserRole($user, 'Guru BK');
        }

        return redirect()->route('guru_bk.index')->with('success', 'Data Guru BK berhasil dihapus.');
    }
}
