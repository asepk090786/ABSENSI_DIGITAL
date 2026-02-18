<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Role;
use App\Models\User;
use App\Exports\SiswaExport;
use App\Exports\SiswaTemplateExport;
use App\Imports\SiswaImport;

class SiswaController extends Controller
{
    private function ensureWaliKelasCanEditSiswa(Siswa $siswa): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasRole('Wali Kelas')) {
            return;
        }

        $guru = $user->guru;
        if (! $guru) {
            abort(403, 'Akses ditolak. Anda tidak terdaftar sebagai guru.');
        }

        $isKelasBinaan = Kelas::where('id', $siswa->kelas_id)
            ->where('wali_kelas_id', $guru->id)
            ->exists();

        if (! $isKelasBinaan) {
            abort(403, 'Akses ditolak. Anda hanya dapat mengubah data siswa di kelas binaan Anda.');
        }
    }

    public function index()
    {
        $items = Siswa::with(['user', 'kelas'])->orderBy('nama')->get();
        return view('siswa.index', compact('items'));
    }

    public function create()
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        return view('siswa.create', compact('kelasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:50|unique:siswa,nis',
            'nisn' => 'required|string|max:50|unique:siswa,nisn',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id',
            'email' => 'required|email|max:255|unique:users,email|unique:siswa,email',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $roleSiswa = Role::where('role_name', 'Siswa')->first();

        if (! $roleSiswa) {
            return redirect()->route('siswa.index')->with('error', 'Role Siswa belum tersedia.');
        }

        $siswa = Siswa::create([
            'nis' => $validated['nis'],
            'nisn' => $validated['nisn'],
            'nama' => $validated['nama'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'kelas_id' => $validated['kelas_id'],
            'email' => $validated['email'],
            'status_aktif' => true,
        ]);

        User::create([
            'name' => $validated['nama'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'role_id' => $roleSiswa->id,
            'siswa_id' => $siswa->id,
            'is_active' => true,
        ]);

        return redirect()->route('siswa.index')->with('success', 'Data siswa dan akun berhasil ditambahkan.');
    }

    public function edit(Siswa $siswa)
    {
        $this->ensureWaliKelasCanEditSiswa($siswa);

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        return view('siswa.edit', compact('siswa', 'kelasList'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $this->ensureWaliKelasCanEditSiswa($siswa);

        $userId = $siswa->user->id ?? null;

        $validated = $request->validate([
            'nis' => 'required|string|max:50|unique:siswa,nis,' . $siswa->id,
            'nisn' => 'required|string|max:50|unique:siswa,nisn,' . $siswa->id,
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id',
            'email' => 'required|email|max:255|unique:users,email,' . $userId . '|unique:siswa,email,' . $siswa->id,
            'username' => 'required|string|max:255|unique:users,username,' . $userId,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $roleSiswa = Role::where('role_name', 'Siswa')->first();

        if (! $roleSiswa) {
            return redirect()->route('siswa.index')->with('error', 'Role Siswa belum tersedia.');
        }

        $siswa->update([
            'nis' => $validated['nis'],
            'nisn' => $validated['nisn'],
            'nama' => $validated['nama'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'kelas_id' => $validated['kelas_id'],
            'email' => $validated['email'],
        ]);

        $user = $siswa->user ?: User::where('siswa_id', $siswa->id)->first();

        $userData = [
            'name' => $validated['nama'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'role_id' => $roleSiswa->id,
            'siswa_id' => $siswa->id,
        ];

        if (! empty($validated['password'])) {
            $userData['password'] = Hash::make($validated['password']);
        }

        if ($user) {
            $user->update($userData);
        } else {
            $userData['password'] = $userData['password'] ?? Hash::make('password123');
            $userData['is_active'] = true;
            User::create($userData);
        }

        if (auth()->check() && auth()->user()->hasRole('Wali Kelas')) {
            return redirect()->route('wali_kelas.siswa')->with('success', 'Data siswa berhasil diperbarui.');
        }

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        if ($siswa->user) {
            $siswa->user->delete();
        }

        $siswa->delete();

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new SiswaExport, 'data_siswa_' . date('Ymd_His') . '.xlsx');
    }

    public function templateDownload()
    {
        return Excel::download(new SiswaTemplateExport, 'template_import_siswa.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        try {
            $import = new SiswaImport();
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();

            if (count($errors) > 0) {
                return redirect()->route('siswa.index')
                    ->with('warning', 'Import selesai dengan beberapa error.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('siswa.index')->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
