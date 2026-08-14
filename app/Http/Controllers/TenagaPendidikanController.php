<?php

namespace App\Http\Controllers;

use App\Models\TenagaPendidikan;
use App\Models\User;
use App\Models\Role;
use App\Exports\TenagaPendidikanExport;
use App\Exports\TenagaPendidikanTemplateExport;
use App\Imports\TenagaPendidikanImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class TenagaPendidikanController extends Controller
{
    public function index()
    {
        $items = TenagaPendidikan::with('user')->orderBy('nama')->paginate(20);
        return view('tenaga_pendidikan.index', compact('items'));
    }

    public function create()
    {
        return view('tenaga_pendidikan.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:tenaga_pendidikan,nip',
            'jabatan' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'required|in:L,P',
            'email' => 'required|email|max:255|unique:tenaga_pendidikan,email',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle foto upload
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('tenaga_pendidikan', 'public');
        }

        // Buat data tenaga pendidikan
        $tenagaPendidikan = TenagaPendidikan::create([
            'nama' => $validated['nama'],
            'nip' => $validated['nip'],
            'jabatan' => $validated['jabatan'] ?? null,
            'telepon' => $validated['telepon'] ?? null,
            'alamat' => $validated['alamat'] ?? null,
            'tanggal_lahir' => $validated['tanggal_lahir'] ?? null,
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'email' => $validated['email'],
            'foto' => $fotoPath,
        ]);

        // Buat akun user untuk tenaga pendidikan
        $role = Role::where('role_name', 'Tenaga Pendidikan')->first();
        if (!$role) {
            // Jika role belum ada, hapus data yang baru dibuat dan return error
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            $tenagaPendidikan->delete();
            return redirect()->back()
                ->withErrors(['error' => 'Role Tenaga Pendidikan tidak ditemukan. Hubungi administrator.'])
                ->withInput();
        }

        User::create([
            'name' => $validated['nama'],
            'username' => $validated['username'],
            'password' => Hash::make($validated['password']),
            'email' => $validated['email'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'role_id' => $role->id,
            'tenaga_pendidikan_id' => $tenagaPendidikan->id,
            'is_active' => 1,
        ]);

        return redirect()->route('tenaga_pendidikan.index')
            ->with('success', 'Data tenaga pendidikan dan akun login berhasil ditambahkan.');
    }

    public function edit(TenagaPendidikan $tenagaPendidikan)
    {
        return view('tenaga_pendidikan.edit', compact('tenagaPendidikan'));
    }

    public function update(Request $request, TenagaPendidikan $tenagaPendidikan)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'nip' => 'nullable|string|max:50|unique:tenaga_pendidikan,nip,' . $tenagaPendidikan->id,
            'jabatan' => 'nullable|string|max:255',
            'telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'email' => 'nullable|email|max:255|unique:tenaga_pendidikan,email,' . $tenagaPendidikan->id,
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Delete old foto if exists
            if ($tenagaPendidikan->foto && Storage::disk('public')->exists($tenagaPendidikan->foto)) {
                Storage::disk('public')->delete($tenagaPendidikan->foto);
            }
            $validated['foto'] = $request->file('foto')->store('tenaga_pendidikan', 'public');
        }

        $tenagaPendidikan->update($validated);

        return redirect()->route('tenaga_pendidikan.index')
            ->with('success', 'Data tenaga pendidikan berhasil diperbarui.');
    }

    public function show(TenagaPendidikan $tenagaPendidikan)
    {
        $tenagaPendidikan->load('user');
        return view('tenaga_pendidikan.show', compact('tenagaPendidikan'));
    }

    public function destroy(TenagaPendidikan $tenagaPendidikan)
    {
        if ($tenagaPendidikan->user) {
            return back()->withErrors(['delete' => 'Tidak dapat menghapus karena memiliki akun user. Hapus akun user terlebih dahulu.']);
        }

        $tenagaPendidikan->delete();

        return redirect()->route('tenaga_pendidikan.index')
            ->with('success', 'Data tenaga pendidikan berhasil dihapus.');
    }

    public function generateAccount(TenagaPendidikan $tenagaPendidikan)
    {
        if ($tenagaPendidikan->user) {
            return redirect()->route('tenaga_pendidikan.index')
                ->with('warning', 'Akun Tenaga Pendidikan sudah tersedia.');
        }

        $role = Role::where('role_name', 'Tenaga Pendidikan')->first();
        if (!$role) {
            return redirect()->route('tenaga_pendidikan.index')
                ->with('error', 'Role Tenaga Pendidikan tidak ditemukan.');
        }

        $username = trim((string) ($tenagaPendidikan->nip ?? ''));
        $plainPassword = $username;
        $email = trim((string) ($tenagaPendidikan->email ?? ''));

        if ($username === '' || User::where('username', $username)->exists()) {
            [$username, $email] = $this->generateUniqueCredentials($tenagaPendidikan->id);
            $plainPassword = $username;
        }

        if ($email === '' || User::where('email', $email)->exists()) {
            $email = $this->ensureUniqueEmail($username . '@simadis.sch.id');
        }

        $user = User::create([
            'name' => $tenagaPendidikan->nama,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($plainPassword),
            'jenis_kelamin' => $tenagaPendidikan->jenis_kelamin,
            'role_id' => $role->id,
            'tenaga_pendidikan_id' => $tenagaPendidikan->id,
            'is_active' => 1,
        ]);

        return redirect()->route('tenaga_pendidikan.show', $tenagaPendidikan)
            ->with('success', 'Akun berhasil dibuat. Username: ' . $username . ' | Password: ' . $plainPassword);
    }

    private function generateUniqueCredentials($tenagaPendidikanId): array
    {
        $maxAttempt = 50;
        $attempt = 0;

        do {
            $attempt++;
            $rand = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $username = 'tp' . $rand;
            $email = $username . '@simadis.sch.id';

            $usernameExists = User::where('username', $username)->exists();
            $emailExists = User::where('email', $email)->exists();

            if (!$usernameExists && !$emailExists) {
                return [$username, $email];
            }
        } while ($attempt < $maxAttempt);

        throw new \RuntimeException('Gagal menghasilkan username/email unik. Silakan coba lagi.');
    }

    private function ensureUniqueEmail($baseEmail): string
    {
        $email = $baseEmail;
        $counter = 1;

        while (User::where('email', $email)->exists()) {
            $parts = explode('@', $baseEmail);
            $email = $parts[0] . $counter . '@' . $parts[1];
            $counter++;
        }

        return $email;
    }

    public function export()
    {
        return Excel::download(new TenagaPendidikanExport, 'data_tenaga_pendidikan_' . date('Ymd_His') . '.xlsx');
    }

    public function templateDownload(Request $request)
    {
        $mode = $request->input('mode', 'create');
        $fileName = $mode === 'update' ? 'template_update_tenaga_pendidikan.xlsx' : 'template_import_tenaga_pendidikan.xlsx';
        return Excel::download(new TenagaPendidikanTemplateExport($mode), $fileName);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'mode' => 'in:create,update',
        ]);

        try {
            $mode = $request->input('mode', 'create');
            $import = new TenagaPendidikanImport($mode);
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $created = $import->getCreatedCount();
            $updated = $import->getUpdatedCount();

            if (!empty($errors)) {
                $warningMsg = $mode === 'update'
                    ? "Import update selesai. {$updated} diperbarui, dengan " . count($errors) . ' error.'
                    : "Import selesai. {$created} dibuat, {$updated} diperbarui, dengan " . count($errors) . ' error.';
                    
                return redirect()->route('tenaga_pendidikan.index')
                    ->with('warning', $warningMsg)
                    ->with('import_errors', $errors);
            }

            if ($mode === 'create') {
                $message = "Import selesai. {$created} data baru dibuat, {$updated} data diperbarui.";
            } else {
                $message = "Import update selesai. {$updated} data diperbarui.";
            }

            return redirect()->route('tenaga_pendidikan.index')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->route('tenaga_pendidikan.index')->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
