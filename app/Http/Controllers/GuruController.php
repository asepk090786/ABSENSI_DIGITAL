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
            'email' => $validated['email'],
            'telepon' => $validated['telepon'],
            'alamat' => $validated['alamat'],
            'tanggal_lahir' => $validated['tanggal_lahir'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
        ]);

        // Buat akun user untuk guru
        $roleGuru = Role::where('role_name', 'Guru')->first();
        
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
            'email' => 'nullable|email|max:150|unique:guru,email,' . $guru->id,
            'telepon' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
        ]);

        $guru->update($validated);

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

    public function export()
    {
        return Excel::download(new GuruExport, 'data_guru_' . date('Ymd_His') . '.xlsx');
    }

    public function templateDownload()
    {
        return Excel::download(new \App\Exports\GuruTemplateExport, 'template_import_guru.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        try {
            $import = new GuruImport();
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            
            if (count($errors) > 0) {
                return redirect()->route('guru.index')
                    ->with('warning', 'Import selesai dengan beberapa error.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('guru.index')->with('success', 'Data guru berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('guru.index')->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }
}
