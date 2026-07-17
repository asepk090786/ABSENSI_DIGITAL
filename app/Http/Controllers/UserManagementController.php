<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Guru;
use App\Models\KepalaSekolah;
use App\Models\Siswa;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class UserManagementController extends Controller
{
    public function edit(User $user)
    {
        $roles = Role::orderBy('role_name')->get();
        $guru = Guru::orderBy('nama')->get();
        return view('user_management.edit', compact('user', 'roles', 'guru'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $user->id],
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'role_id' => ['required', 'exists:roles,id'],
            'is_active' => ['required', 'boolean'],
            'guru_id' => ['nullable', 'exists:guru,id'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user->name = $data['name'];
        $user->username = $data['username'];
        $user->email = $data['email'] ?? null;
        $user->role_id = $data['role_id'];
        $user->guru_id = $data['guru_id'] ?? null;
        $user->is_active = $data['is_active'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        return redirect()->route('users.index')->with('success', 'Akun berhasil diperbarui.');
    }
    public function index(Request $request)
    {
        $query = User::with(['role', 'guru', 'kepalaSekolah', 'siswa']);
        $search = $request->input('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhereHas('role', function($qr) use ($search) {
                      $qr->where('role_name', 'like', "%$search%") ;
                  });
            });
        }
        $users = $query->orderBy('name')->paginate(20)->appends(['search' => $search]);
        return view('user_management.index', compact('users', 'search'));
    }

    public function create()
    {
        $roles = Role::orderBy('role_name')->get();
        $guru = Guru::with('user')->orderBy('nama')->get();
        $kepala = KepalaSekolah::with('guru')->orderBy('nama')->get();
        $siswa = Siswa::orderBy('nama')->get();

        return view('user_management.create', compact('roles', 'guru', 'kepala', 'siswa'));
    }

    public function store(Request $request)
    {
        $data = $request->all();

        $guru = isset($data['guru_id']) ? Guru::with('user')->find($data['guru_id']) : null;
        $existingGuruUser = $guru?->user;

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'role_id' => ['required', 'exists:roles,id'],
            'guru_id' => ['nullable', 'exists:guru,id'],
            'kepala_sekolah_id' => ['nullable', 'exists:kepala_sekolah,id'],
            'siswa_id' => ['nullable', 'exists:siswa,id'],
        ];

        if ($existingGuruUser) {
            $rules['username'] = ['required', 'string', 'max:50', 'unique:users,username,' . $existingGuruUser->id];
            $rules['email'] = ['nullable', 'email', 'max:150', 'unique:users,email,' . $existingGuruUser->id];
            $rules['password'] = ['nullable', 'string', 'min:6'];
        } else {
            $rules['username'] = ['required', 'string', 'max:50', 'unique:users,username'];
            $rules['email'] = ['nullable', 'email', 'max:150', 'unique:users,email'];
            $rules['password'] = ['required', 'string', 'min:6'];
        }

        $validator = Validator::make($data, $rules);

        $role = Role::find($data['role_id'] ?? null);

        $validator->after(function ($validator) use ($role, $data) {
            if (! $role) {
                return;
            }

            $roleName = $role->role_name;

            if ($roleName === 'Siswa' && empty($data['siswa_id'])) {
                $validator->errors()->add('siswa_id', 'Pilih siswa untuk akun Siswa.');
            }

            if ($roleName === 'Kepala Sekolah' && empty($data['kepala_sekolah_id'])) {
                $validator->errors()->add('kepala_sekolah_id', 'Pilih kepala sekolah untuk peran ini.');
            }

            if (str_contains($roleName, 'Guru') && empty($data['guru_id'])) {
                $validator->errors()->add('guru_id', 'Pilih guru untuk peran guru.');
            }
        });

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if ($existingGuruUser) {
            $existingGuruUser->name = $data['name'];
            $existingGuruUser->nip = $data['nip'] ?? null;
            $existingGuruUser->username = $data['username'];
            $existingGuruUser->email = $data['email'] ?? null;
            $existingGuruUser->jenis_kelamin = $data['jenis_kelamin'];
            $existingGuruUser->role_id = $data['role_id'];
            $existingGuruUser->guru_id = $data['guru_id'] ?? null;
            $existingGuruUser->kepala_sekolah_id = $data['kepala_sekolah_id'] ?? null;
            $existingGuruUser->siswa_id = $data['siswa_id'] ?? null;

            if (! empty($data['password'])) {
                $existingGuruUser->password = Hash::make($data['password']);
            }

            $existingGuruUser->save();

            return redirect()->route('users.index')->with('success', 'Akun guru berhasil diperbarui menjadi ' . ($role?->role_name ?? 'pengguna baru') . '.');
        }

        User::create([
            'name' => $data['name'],
            'nip' => $data['nip'] ?? null,
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => Hash::make($data['password']),
            'jenis_kelamin' => $data['jenis_kelamin'],
            'role_id' => $data['role_id'],
            'guru_id' => $data['guru_id'] ?? null,
            'kepala_sekolah_id' => $data['kepala_sekolah_id'] ?? null,
            'siswa_id' => $data['siswa_id'] ?? null,
        ]);

        return redirect()->route('users.index')->with('success', 'Akun berhasil dibuat.');
    }

    public function show(User $user)
    {
        $user->load('role');
        return view('user_management.show', compact('user'));
    }

    public function destroy(User $user)
    {
        // optional: prevent self-delete
        if (auth()->id() === $user->id) {
            return back()->withErrors(['delete' => 'Tidak dapat menghapus akun sendiri.']);
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Akun berhasil dihapus.');
    }

    public function bulkDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->withErrors(['bulk' => 'Tidak ada akun yang dipilih.']);
        }

        $currentUserId = auth()->id();

        // Prevent self-delete
        if (in_array($currentUserId, $ids)) {
            return back()->withErrors(['bulk' => 'Tidak dapat menghapus akun sendiri.']);
        }

        $deletedCount = User::whereIn('id', $ids)->delete();

        return redirect()->route('users.index')
            ->with('success', "{$deletedCount} akun berhasil dihapus.");
    }

    public function activate(User $user)
    {
        $user->is_active = ! $user->is_active;
        $user->save();

        return back()->with('success', $user->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
    }

    public function export()
    {
        return Excel::download(new UsersExport(), 'akun_pengguna.xlsx');
    }

    public function templateDownload()
    {
        return response()->download(
            public_path('import_akun_pengguna.xlsx'),
            'import_akun_pengguna.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $import = new UsersImport();
        Excel::import($import, $request->file('file'));

        $message = 'Import selesai. ' . $import->created . ' akun berhasil ditambahkan.';

        return back()
            ->with('success', $message)
            ->with('import_errors', $import->errors);
    }
}
