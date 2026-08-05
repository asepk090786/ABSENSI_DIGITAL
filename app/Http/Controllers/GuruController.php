<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\GuruExport;
use App\Imports\GuruImport;

class GuruController extends Controller
{
    private function resolveDefaultGuruRole(): ?Role
    {
        return Role::whereIn('role_name', ['Guru', 'Guru Mapel', 'Guru Kelas'])
            ->orderByRaw("CASE role_name WHEN 'Guru' THEN 1 WHEN 'Guru Mapel' THEN 2 WHEN 'Guru Kelas' THEN 3 ELSE 99 END")
            ->first();
    }

    private function generateSimadisIdentity(): array
    {
        $maxAttempt = 50;
        $attempt = 0;

        do {
            $attempt++;
            $rand = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $username = 'simadis' . $rand;
            $email = $username . '@simadis.sch.id';

            $usernameExists = User::where('username', $username)->exists();
            $emailExists = User::where('email', $email)->exists();

            if (! $usernameExists && ! $emailExists) {
                return [$username, $email];
            }
        } while ($attempt < $maxAttempt);

        throw new \RuntimeException('Gagal menghasilkan username/email unik. Silakan coba lagi.');
    }

    public function index()
    {
        $items = Guru::with('user')->orderBy('nama')->get();
        return view('guru.index', compact('items'));
    }

    public function create()
    {
        return view('guru.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:guru,nip',
            'pangkat_golongan' => 'nullable|string|max:100',
            'email' => 'required|email|max:150|unique:guru,email',
            'telepon' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Buat data guru
        $guru = Guru::create([
            'nama' => $validated['nama'],
            'nip' => $validated['nip'],
            'pangkat_golongan' => $validated['pangkat_golongan'] ?? null,
            'email' => $validated['email'],
            'telepon' => $validated['telepon'],
            'alamat' => $validated['alamat'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
        ]);

        // Buat akun user untuk guru
        $roleGuru = $this->resolveDefaultGuruRole();
        
        User::create([
            'name' => $validated['nama'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'email' => $validated['email'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'role_id' => $roleGuru->id,
            'guru_id' => $guru->id,
            'is_active' => true,
        ]);

        return redirect()->route('guru.index')->with('success', 'Data guru dan akun berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        return view('guru.edit', compact('guru'));
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:guru,nip,' . $guru->id,
            'pangkat_golongan' => 'nullable|string|max:100',
            'kode_guru' => 'nullable|string|max:50|unique:guru,kode_guru,' . $guru->id,
            'username' => 'nullable|string|max:50|unique:guru,username,' . $guru->id,
            'password' => 'nullable|string|min:4|confirmed',
            'telepon' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'email' => 'required|email|max:150|unique:guru,email,' . $guru->id,
        ]);

        // Status aktif/nonaktif akun
        $validated['is_active'] = $request->has('is_active') ? 1 : 0;

        // Simpan data guru (kecuali username/password)
        $guruData = $validated;
        unset($guruData['username'], $guruData['password']);
        // Email selalu disimpan (karena sudah divalidasi required)
        $guruData['email'] = $validated['email'];
        $guru->update($guruData);

        // Logika update/insert user jika username/password diisi
        $username = $validated['username'] ?? null;
        $password = $validated['password'] ?? null;
        // Status aktif: hanya jika username dan password diisi
        $is_active = ($username && $password) ? 1 : 0;
        $email = $validated['email'] ?? null;

        if ($username && $password) {
            // Pastikan email tidak null
            if (empty($email)) {
                return redirect()->back()->withInput()->withErrors(['email' => 'Email wajib diisi untuk membuat akun pengguna.']);
            }
            $user = $guru->user;
            if ($user) {
                // Update user
                $user->username = $username;
                $user->password = bcrypt($password);
                $user->is_active = 1;
                $user->name = $guru->nama;
                $user->email = $email;
                $user->jenis_kelamin = $guru->jenis_kelamin;
                $user->save();
            } else {
                // Buat user baru
                $roleGuru = $this->resolveDefaultGuruRole();
                \App\Models\User::create([
                    'name' => $guru->nama,
                    'username' => $username,
                    'password' => bcrypt($password),
                    'email' => $email,
                    'jenis_kelamin' => $guru->jenis_kelamin,
                    'role_id' => $roleGuru ? $roleGuru->id : null,
                    'guru_id' => $guru->id,
                    'is_active' => 1,
                ]);
            }
        } else if ($guru->user) {
            // Jika username atau password dikosongkan, set user nonaktif
            $guru->user->is_active = 0;
            $guru->user->save();
        }

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil diperbarui.');
    }

    public function destroy(Guru $guru)
    {
        // Hapus user terkait jika ada
        if ($guru->user) {
            $guru->user->delete();
        }
        
        $guru->delete();

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil dihapus.');
    }

    /**
     * Delete multiple gurus at once
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'guru_ids' => 'required|array',
            'guru_ids.*' => 'required|integer|exists:guru,id'
        ]);

        try {
            $count = 0;
            foreach ($validated['guru_ids'] as $guruId) {
                $guru = Guru::find($guruId);
                if ($guru) {
                    if ($guru->user) {
                        $guru->user->delete();
                    }
                    $guru->delete();
                    $count++;
                }
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$count} data guru.",
                    'count' => $count
                ]);
            }

            return redirect()->route('guru.index')->with('success', "Berhasil menghapus {$count} data guru.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data guru: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('guru.index')->with('error', 'Gagal menghapus data guru: ' . $e->getMessage());
        }
    }

    public function generateAccount(Guru $guru)
    {
        if ($guru->user) {
            return redirect()->route('guru.index')->with('warning', 'Guru ini sudah memiliki akun.');
        }

        $roleGuru = $this->resolveDefaultGuruRole();
        if (! $roleGuru) {
            return redirect()->route('guru.index')->with('error', 'Role guru tidak ditemukan. Pastikan role Guru Mapel/Guru Kelas tersedia.');
        }

        try {
            $nip = trim((string) ($guru->nip ?? ''));

            if ($nip !== '') {
                if (User::where('username', $nip)->exists()) {
                    return redirect()->route('guru.index')->with('error', 'Username dari NIP sudah digunakan. Gunakan NIP lain atau kosongkan NIP lalu generate ulang.');
                }

                $username = $nip;
                $plainPassword = $nip;

                $email = $guru->email;
                if (empty($email) || User::where('email', $email)->exists()) {
                    [, $generatedEmail] = $this->generateSimadisIdentity();
                    $email = $generatedEmail;
                }
            } else {
                [$username, $generatedEmail] = $this->generateSimadisIdentity();
                $plainPassword = $username;
                $email = 'simadis@simadis.sch.id';

                if (User::where('email', $email)->exists()) {
                    $email = $generatedEmail;
                }
            }

            $user = User::create([
                'name' => $guru->nama,
                'username' => $username,
                'password' => Hash::make($plainPassword),
                'email' => $email,
                'jenis_kelamin' => $guru->jenis_kelamin,
                'role_id' => $roleGuru->id,
                'guru_id' => $guru->id,
                'is_active' => 1,
            ]);

            $user->roles()->syncWithoutDetaching([$roleGuru->id]);

            if (empty($guru->email) || $guru->email !== $email) {
                $guru->update(['email' => $email]);
            }

            return redirect()->route('guru.index')
                ->with('success', 'Akun guru berhasil dibuat.')
                ->with('generated_credentials', [
                    'nama' => $guru->nama,
                    'username' => $username,
                    'password' => $plainPassword,
                    'email' => $email,
                ]);
        } catch (\Throwable $e) {
            return redirect()->route('guru.index')->with('error', 'Gagal generate akun: ' . $e->getMessage());
        }
    }

    public function generatePengawasAccount(Guru $guru)
    {
        if ($guru->user) {
            return redirect()->route('guru.index')->with('warning', 'Guru ini sudah memiliki akun.');
        }

        $rolePengawas = Role::where('role_name', 'Pengawas Pembina')->first();
        if (! $rolePengawas) {
            return redirect()->route('guru.index')->with('error', 'Role Pengawas Pembina tidak ditemukan.');
        }

        try {
            $nip = trim((string) ($guru->nip ?? ''));
            if ($nip !== '' && ! User::where('username', $nip)->exists()) {
                $username = $nip;
                $plainPassword = $nip;
            } else {
                [$username, $generatedEmail] = $this->generateSimadisIdentity('pengawas');
                $plainPassword = $username;
            }

            $email = trim((string) ($guru->email ?? ''));
            if ($email === '' || User::where('email', $email)->exists()) {
                $email = $generatedEmail ?? ($username . '@simadis.sch.id');
                if (User::where('email', $email)->exists()) {
                    $email = $this->ensureUniqueEmail($email);
                }
            }

            $user = User::create([
                'name' => $guru->nama,
                'username' => $username,
                'password' => Hash::make($plainPassword),
                'email' => $email,
                'jenis_kelamin' => $guru->jenis_kelamin,
                'role_id' => $rolePengawas->id,
                'guru_id' => $guru->id,
                'is_active' => 1,
            ]);

            $user->roles()->syncWithoutDetaching([$rolePengawas->id]);

            if (empty($guru->email) || $guru->email !== $email) {
                $guru->update(['email' => $email]);
            }

            return redirect()->route('guru.index')
                ->with('success', 'Akun Pengawas Pembina berhasil dibuat.')
                ->with('generated_credentials', [
                    'nama' => $guru->nama,
                    'username' => $username,
                    'password' => $plainPassword,
                    'email' => $email,
                ]);
        } catch (\Throwable $e) {
            return redirect()->route('guru.index')->with('error', 'Gagal generate akun Pengawas Pembina: ' . $e->getMessage());
        }
    }

    public function export()
    {
        return Excel::download(new GuruExport, 'data_guru_' . date('Ymd_His') . '.xlsx');
    }

    public function templateDownload(Request $request)
    {
        $mode = $request->query('mode', 'create');
        $fileName = $mode === 'update' ? 'template_update_guru.xlsx' : 'template_import_guru.xlsx';

        return Excel::download(new \App\Exports\GuruTemplateExport($mode), $fileName);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
            'mode' => 'nullable|in:create,update',
        ]);

        try {
            $mode = $request->input('mode', 'create');
            $import = new GuruImport($mode);
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();

            if (count($errors) > 0) {
                return redirect()->route('guru.index')
                    ->with('warning', $mode === 'update'
                        ? "Proses update selesai. {$updated} diperbarui, dengan " . count($errors) . ' error.'
                        : "Import selesai. {$created} dibuat, {$updated} diperbarui, dengan " . count($errors) . ' error.')
                    ->with('import_errors', $errors);
            }

            $message = $mode === 'update'
                ? 'Data guru berhasil diupdate.'
                : 'Data guru berhasil diimport.';

            if ($mode !== 'update' && $created > 0 && $updated > 0) {
                $message = "Import selesai. {$created} data baru dibuat, {$updated} data diperbarui.";
            } elseif ($mode !== 'update' && $updated > 0) {
                $message = "{$updated} data guru berhasil diperbarui.";
            } elseif ($mode !== 'update' && $created > 0) {
                $message = "{$created} data guru berhasil ditambahkan.";
            } elseif ($mode === 'update' && $updated > 0) {
                $message = "{$updated} data guru berhasil diperbarui.";
            }

            return redirect()->route('guru.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('guru.index')->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
