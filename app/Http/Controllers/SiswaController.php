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

    private function ensureWaliKelasCanStoreSiswa(Request $request): void
    {
        $user = auth()->user();

        if (! $user || ! $user->hasRole('Wali Kelas')) {
            return;
        }

        $guru = $user->guru;
        if (! $guru) {
            abort(403, 'Akses ditolak. Anda tidak terdaftar sebagai guru.');
        }

        $kelasBinaan = Kelas::where('wali_kelas_id', $guru->id)->first();
        if (! $kelasBinaan) {
            abort(403, 'Akses ditolak. Anda tidak ditugaskan sebagai wali kelas.');
        }

        if ((int) $request->input('kelas_id') !== $kelasBinaan->id) {
            abort(403, 'Akses ditolak. Anda hanya dapat menambahkan siswa ke kelas binaan Anda.');
        }
    }

    private function canManageClassPositions(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['Admin', 'Wali Kelas']);
    }

    private function canManageClassPositionsForStudent(Siswa $siswa): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        if ($this->canManageClassPositions()) {
            return true;
        }

        $guru = $user->guru;
        if (! $guru) {
            return false;
        }

        return Kelas::where('id', $siswa->kelas_id)
            ->where('wali_kelas_id', $guru->id)
            ->exists();
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
        $this->ensureWaliKelasCanStoreSiswa($request);

        $validated = $request->validate([
            'nis' => 'required|string|max:50|unique:siswa,nis',
            'nisn' => 'required|string|max:50|unique:siswa,nisn',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas_id' => 'required|exists:kelas,id',
            'email' => 'required|email|max:255|unique:users,email|unique:siswa,email',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
            'jabatan_kelas' => 'nullable|in:ketua,wakil,sekretaris,bendahara',
        ]);

        $roleSiswa = Role::where('role_name', 'Siswa')->first();

        if (! $roleSiswa) {
            return redirect()->route('siswa.index')->with('error', 'Role Siswa belum tersedia.');
        }

        $siswaData = [
            'nis' => $validated['nis'],
            'nisn' => $validated['nisn'],
            'nama' => $validated['nama'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'kelas_id' => $validated['kelas_id'],
            'email' => $validated['email'],
            'status_aktif' => true,
        ];

        if ($this->canManageClassPositions()) {
            $siswaData['jabatan_kelas'] = $validated['jabatan_kelas'] ?? null;
        }

        $siswa = Siswa::create($siswaData);

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
        $canManageClassPositions = $this->canManageClassPositionsForStudent($siswa);
        $backRoute = $canManageClassPositions ? route('wali_kelas.siswa') : route('siswa.index');

        return view('siswa.edit', compact('siswa', 'kelasList', 'canManageClassPositions', 'backRoute'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $this->ensureWaliKelasCanEditSiswa($siswa);
        $redirectToWaliKelasSiswa = $this->canManageClassPositionsForStudent($siswa);

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
            'jabatan_kelas' => 'nullable|in:ketua,wakil,sekretaris,bendahara',
        ]);

        $roleSiswa = Role::where('role_name', 'Siswa')->first();

        if (! $roleSiswa) {
            return redirect()->route('siswa.index')->with('error', 'Role Siswa belum tersedia.');
        }

        $siswaData = [
            'nis' => $validated['nis'],
            'nisn' => $validated['nisn'],
            'nama' => $validated['nama'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'kelas_id' => $validated['kelas_id'],
            'email' => $validated['email'],
        ];

        if ($this->canManageClassPositions()) {
            $siswaData['jabatan_kelas'] = $validated['jabatan_kelas'] ?? null;
        }

        $siswa->update($siswaData);

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

        if ($redirectToWaliKelasSiswa) {
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

    /**
     * Delete multiple siswa at once
     */
    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'siswa_ids' => 'required|array',
            'siswa_ids.*' => 'required|integer|exists:siswa,id'
        ]);

        try {
            $count = 0;
            foreach ($validated['siswa_ids'] as $id) {
                $siswa = Siswa::find($id);
                if (! $siswa) continue;

                // Check permissions for Wali Kelas
                $this->ensureWaliKelasCanEditSiswa($siswa);

                if ($siswa->user) {
                    $siswa->user->delete();
                }
                $siswa->delete();
                $count++;
            }

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Berhasil menghapus {$count} data siswa.",
                    'count' => $count
                ]);
            }

            return redirect()->route('siswa.index')->with('success', "Berhasil menghapus {$count} data siswa.");
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menghapus data siswa: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('siswa.index')->with('error', 'Gagal menghapus data siswa: ' . $e->getMessage());
        }
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
