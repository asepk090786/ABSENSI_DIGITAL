<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $teachers = Guru::with('user:id,name,username,email,foto,guru_id,is_active')
            ->orderBy('nama')
            ->get();

        $students = Siswa::with([
            'kelas:id,nama_kelas,kode_kelas,wali_kelas_id',
            'user:id,name,username,email,foto,siswa_id,is_active',
        ])->orderBy('nama')->get();

        $classes = Kelas::with([
            'waliKelas.user:id,name,username,email,foto,guru_id,is_active',
            'tugasGuru.guru.user:id,name,username,email,foto,guru_id,is_active',
            'tugasGuru.mataPelajaran:id,kode_mapel,nama_mapel,kategori,jenis_kegiatan_id',
        ])->orderBy('nama_kelas')->get();

        $users = User::with(['role:id,role_name', 'roles:id,role_name'])
            ->orderBy('name')
            ->get();

        return response()->json([
            'message' => 'Master data berhasil diambil.',
            'password_included' => false,
            'mata_pelajaran' => MataPelajaran::orderBy('nama_mapel')->get(),
            'guru' => $teachers->map(fn (Guru $guru) => $this->teacherPayload($guru))->values(),
            'siswa' => $students->map(fn (Siswa $siswa) => [
                'id' => $siswa->id,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'nama' => $siswa->nama,
                'jenis_kelamin' => $siswa->jenis_kelamin,
                'email' => $siswa->email,
                'status_aktif' => (bool) $siswa->status_aktif,
                'jabatan_kelas' => $siswa->jabatan_kelas,
                'kelas_id' => $siswa->kelas_id,
                'kelas' => $siswa->kelas ? [
                    'id' => $siswa->kelas->id,
                    'nama_kelas' => $siswa->kelas->nama_kelas,
                    'kode_kelas' => $siswa->kelas->kode_kelas,
                ] : null,
                'user' => $siswa->user ? $this->userPayload($siswa->user) : null,
            ])->values(),
            'users' => $users->map(fn (User $user) => $this->userPayload($user))->values(),
            'kelas' => $classes->map(fn (Kelas $kelas) => [
                'id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
                'kode_kelas' => $kelas->kode_kelas,
                'tingkat_kelas' => $kelas->tingkat_kelas,
                'jurusan' => $kelas->jurusan,
                'wali_kelas_id' => $kelas->wali_kelas_id,
                'wali_kelas' => $kelas->waliKelas ? $this->teacherPayload($kelas->waliKelas) : null,
                'guru' => $kelas->tugasGuru->map(fn ($tugas) => [
                    'id' => $tugas->guru?->id,
                    'nama' => $tugas->guru?->nama,
                    'nip' => $tugas->guru?->nip,
                    'user' => $tugas->guru?->user ? $this->userPayload($tugas->guru->user) : null,
                    'mata_pelajaran' => $tugas->mataPelajaran ? [
                        'id' => $tugas->mataPelajaran->id,
                        'kode_mapel' => $tugas->mataPelajaran->kode_mapel,
                        'nama_mapel' => $tugas->mataPelajaran->nama_mapel,
                    ] : null,
                    'is_active' => (bool) $tugas->is_active,
                ])->filter(fn ($guru) => $guru['id'] !== null)->values(),
            ])->values(),
        ]);
    }

    private function teacherPayload(Guru $guru): array
    {
        return [
            'id' => $guru->id,
            'nama' => $guru->nama,
            'nip' => $guru->nip,
            'email' => $guru->email,
            'telepon' => $guru->telepon,
            'alamat' => $guru->alamat,
            'jenis_kelamin' => $guru->jenis_kelamin,
            'is_active' => (bool) $guru->is_active,
            'foto' => $this->fotoUrl($guru->foto),
            'user' => $guru->user ? $this->userPayload($guru->user) : null,
        ];
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'foto' => $this->fotoUrl($user->foto),
            'password_configured' => ! empty($user->password),
            'role' => $user->role?->role_name,
            'roles' => $user->roleNames(),
            'guru_id' => $user->guru_id,
            'siswa_id' => $user->siswa_id,
            'is_active' => (bool) $user->is_active,
        ];
    }

    private function fotoUrl(?string $foto): ?string
    {
        return $foto ? (str_contains($foto, '://') ? $foto : Storage::disk('public')->url($foto)) : null;
    }
}