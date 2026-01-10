<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Role;
use App\Models\Siswa;
use App\Models\User;
use App\Exports\KelasExport;
use App\Exports\KelasTemplateExport;
use App\Exports\KelasSiswaExport;
use App\Exports\KelasSiswaTemplateExportNew;
use App\Imports\KelasImport;
use App\Imports\KelasSiswaImport;

class KelasController extends Controller
{
    public function index()
    {
        $items = Kelas::with(['waliKelas'])->withCount('siswa')->orderBy('nama_kelas')->get();
        return view('kelas.index', compact('items'));
    }

    public function create()
    {
        $guruList = Guru::orderBy('nama')->get();
        $sekolah = \App\Models\Sekolah::first();
        return view('kelas.create', compact('guruList', 'sekolah'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255|unique:kelas,nama_kelas',
            'tingkat_kelas' => 'nullable|string|max:50',
            'wali_kelas_id' => 'nullable|exists:guru,id',
        ]);

        Kelas::create($validated);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kela)
    {
        $kela->load(['waliKelas', 'siswa.user']);
        $guruList = Guru::orderBy('nama')->get();
        $sekolah = \App\Models\Sekolah::first();
        
        // Ambil siswa yang belum punya kelas
        $siswaWithoutClass = Siswa::whereNull('kelas_id')
            ->orWhere('kelas_id', 0)
            ->orderBy('nama')
            ->get();
        
        return view('kelas.edit', [
            'kelas' => $kela, 
            'guruList' => $guruList, 
            'sekolah' => $sekolah,
            'siswaWithoutClass' => $siswaWithoutClass
        ]);
    }

    public function update(Request $request, Kelas $kela)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:255|unique:kelas,nama_kelas,' . $kela->id,
            'tingkat_kelas' => 'nullable|string|max:50',
            'wali_kelas_id' => 'nullable|exists:guru,id',
        ]);

        $kela->update($validated);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kela)
    {
        if ($kela->siswa()->count() > 0) {
            return redirect()->route('kelas.index')->with('error', 'Tidak dapat menghapus karena masih ada siswa di kelas ini.');
        }

        $kela->delete();

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
    }

    public function export()
    {
        return Excel::download(new KelasExport, 'data_kelas_' . date('Ymd_His') . '.xlsx');
    }

    public function templateDownload()
    {
        return Excel::download(new KelasTemplateExport, 'template_import_kelas.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        try {
            $import = new KelasImport();
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();

            if (count($errors) > 0) {
                return redirect()->route('kelas.index')
                    ->with('warning', 'Import selesai dengan beberapa error.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('kelas.index')->with('success', 'Data kelas berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('kelas.index')->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function studentExport(Kelas $kela)
    {
        return Excel::download(new KelasSiswaExport($kela->id), 'data_siswa_kelas_' . $kela->id . '_' . date('Ymd_His') . '.xlsx');
    }

    public function studentTemplate(Kelas $kela)
    {
        // Force no-cache headers
        $filename = 'TEMPLATE_BARU_' . $kela->id . '_' . time() . '.xlsx';
        
        return Excel::download(new KelasSiswaTemplateExportNew($kela->id), $filename)
            ->withHeaders([
                'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma' => 'no-cache',
                'Expires' => 'Sat, 01 Jan 2000 00:00:00 GMT',
            ]);
    }

    public function studentImport(Request $request, Kelas $kela)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);

        try {
            $import = new KelasSiswaImport($kela->id);
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();

            if (count($errors) > 0) {
                return redirect()->route('kelas.edit', $kela->id)
                    ->with('warning', 'Import siswa selesai dengan beberapa error.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('kelas.edit', $kela->id)->with('success', 'Siswa kelas berhasil diimport.');
        } catch (\Exception $e) {
            return redirect()->route('kelas.edit', $kela->id)->with('error', 'Gagal import: ' . $e->getMessage());
        }
    }

    public function addStudent(Request $request, Kelas $kela)
    {
        $validated = $request->validate([
            'nis' => 'required|string|max:50|unique:siswa,nis',
            'nisn' => 'required|string|max:50|unique:siswa,nisn',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'email' => 'required|email|max:255|unique:users,email|unique:siswa,email',
            'username' => 'required|string|max:255|unique:users,username',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $roleSiswa = Role::where('role_name', 'Siswa')->first();

        if (! $roleSiswa) {
            return redirect()->route('kelas.edit', $kela->id)->with('error', 'Role Siswa belum tersedia.');
        }

        $siswa = Siswa::create([
            'nis' => $validated['nis'],
            'nisn' => $validated['nisn'],
            'nama' => $validated['nama'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'kelas_id' => $kela->id,
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

        return redirect()->route('kelas.edit', $kela->id)->with('success', 'Siswa berhasil ditambahkan ke kelas.');
    }

    public function assignExistingStudent(Request $request, Kelas $kela)
    {
        $validated = $request->validate([
            'siswa_ids' => 'required|array|min:1',
            'siswa_ids.*' => 'required|exists:siswa,id',
        ], [
            'siswa_ids.required' => 'Silakan pilih minimal satu siswa.',
            'siswa_ids.min' => 'Silakan pilih minimal satu siswa.',
            'siswa_ids.*.exists' => 'Siswa tidak ditemukan.',
        ]);

        // Update kelas_id untuk siswa yang dipilih
        $updated = Siswa::whereIn('id', $validated['siswa_ids'])
            ->where(function ($query) {
                $query->whereNull('kelas_id')->orWhere('kelas_id', 0);
            })
            ->update(['kelas_id' => $kela->id]);

        if ($updated > 0) {
            $message = $updated === 1 
                ? '1 siswa berhasil ditambahkan ke kelas.' 
                : "{$updated} siswa berhasil ditambahkan ke kelas.";
            return redirect()->route('kelas.edit', $kela->id)->with('success', $message);
        }

        return redirect()->route('kelas.edit', $kela->id)->with('error', 'Tidak ada siswa yang dapat ditambahkan. Siswa mungkin sudah memiliki kelas.');
    }
}
