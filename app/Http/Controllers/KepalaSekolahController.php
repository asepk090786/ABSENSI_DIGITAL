<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\KepalaSekolah;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class KepalaSekolahController extends Controller
{
    public function index()
    {
        $kepalaSekolah = KepalaSekolah::with(['guru', 'user'])->orderBy('tanggal_mulai_jabatan', 'desc')->get();
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

    public function generateAccount(KepalaSekolah $kepalaSekolah)
    {
        if ($kepalaSekolah->user) {
            return redirect()->route('kepala_sekolah.index')->with('warning', 'Akun Kepala Sekolah sudah tersedia.');
        }

        $role = Role::where('role_name', 'Kepala Sekolah')->first();
        if (! $role) {
            return redirect()->route('kepala_sekolah.index')->with('error', 'Role Kepala Sekolah tidak ditemukan.');
        }

        $guru = $kepalaSekolah->guru;
        $username = trim((string) ($kepalaSekolah->nip ?? ''));
        $plainPassword = $username;
        $email = trim((string) ($kepalaSekolah->email ?? ''));

        if ($username === '' || User::where('username', $username)->exists()) {
            [$username, $email] = $this->generateSimadisIdentity('kepala');
            $plainPassword = $username;
        }

        if ($email === '' || User::where('email', $email)->exists()) {
            $email = $this->ensureUniqueEmail($username . '@simadis.sch.id');
        }

        $user = User::create([
            'name' => $kepalaSekolah->nama,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($plainPassword),
            'jenis_kelamin' => $guru?->jenis_kelamin,
            'role_id' => $role->id,
            'guru_id' => $guru?->id,
            'kepala_sekolah_id' => $kepalaSekolah->id,
            'is_active' => 1,
        ]);

        $user->roles()->syncWithoutDetaching([$role->id]);

        return redirect()->route('kepala_sekolah.index')
            ->with('success', 'Akun Kepala Sekolah berhasil dibuat.')
            ->with('generated_credentials', [
                'nama' => $kepalaSekolah->nama,
                'username' => $username,
                'password' => $plainPassword,
                'email' => $email,
            ]);
    }

    public function show(KepalaSekolah $kepalaSekolah)
    {
        $kepalaSekolah->load(['guru', 'user']);
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

    private function generateSimadisIdentity(string $prefix = 'kepala'): array
    {
        $maxAttempt = 50;
        $attempt = 0;

        do {
            $attempt++;
            $rand = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $username = $prefix . $rand;
            $email = $username . '@simadis.sch.id';
        } while ($attempt < $maxAttempt && User::where('username', $username)->orWhere('email', $email)->exists());

        if (User::where('username', $username)->orWhere('email', $email)->exists()) {
            throw new \RuntimeException('Gagal menghasilkan identitas unik untuk Kepala Sekolah.');
        }

        return [$username, $email];
    }

    private function ensureUniqueEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            throw new \InvalidArgumentException('Email tidak valid.');
        }

        [$local, $domain] = explode('@', $email, 2);
        $candidate = $email;
        $counter = 1;

        while (User::where('email', $candidate)->exists()) {
            $candidate = $local . '+' . $counter . '@' . $domain;
            $counter++;
        }

        return $candidate;
    }
}
