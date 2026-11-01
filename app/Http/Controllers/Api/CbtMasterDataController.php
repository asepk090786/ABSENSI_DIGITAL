<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\Semester;
use App\Models\TahunAjaran;
use App\Models\MataPelajaran;
use App\Models\TugasGuru;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class CbtMasterDataController extends Controller
{
    private const DEFAULT_PER_PAGE = 250;
    private const MAX_PER_PAGE = 500;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:' . self::MAX_PER_PAGE],
        ]);
        $perPage = (int) ($validated['per_page'] ?? self::DEFAULT_PER_PAGE);
        $page = (int) ($validated['page'] ?? 1);

        $teachers = Guru::query()
            ->select(['id', 'nip', 'nama', 'is_active'])
            ->with('user:id,guru_id,username,is_active')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        $students = Siswa::query()
            ->select(['id', 'nis', 'nisn', 'nama', 'status_aktif', 'kelas_id'])
            ->with([
                'user:id,siswa_id,username,is_active,foto',
                'kelas:id,nama_kelas,kode_kelas,tingkat_kelas,jurusan',
            ])
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'source' => 'simadis',
            'schema_version' => 1,
            'password_included' => false,
            'guru' => $this->pagePayload($teachers, static fn (Guru $guru): array => [
                'simadis_id' => (string) $guru->id,
                'simadis_user_id' => $guru->user ? (string) $guru->user->id : null,
                'nip' => $guru->nip,
                'nama' => $guru->nama,
                'is_active' => (bool) $guru->is_active,
                'username' => $guru->user?->username,
                'user_is_active' => $guru->user ? (bool) $guru->user->is_active : null,
            ]),
            'siswa' => $this->pagePayload($students, static fn (Siswa $siswa): array => [
                'simadis_id' => (string) $siswa->id,
                'simadis_user_id' => $siswa->user ? (string) $siswa->user->id : null,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'nama' => $siswa->nama,
                'is_active' => (bool) $siswa->status_aktif,
                'current_class_id' => $siswa->kelas_id !== null ? (string) $siswa->kelas_id : null,
                'current_class' => $siswa->kelas ? [
                    'simadis_id' => (string) $siswa->kelas->id,
                    'nama_kelas' => $siswa->kelas->nama_kelas,
                    'kode_kelas' => $siswa->kelas->kode_kelas,
                    'tingkat_kelas' => $siswa->kelas->tingkat_kelas,
                    'jurusan' => $siswa->kelas->jurusan,
                ] : null,
                'username' => $siswa->user?->username,
                'user_is_active' => $siswa->user ? (bool) $siswa->user->is_active : null,
                'foto_available' => is_string($siswa->user?->foto)
                    && trim($siswa->user->foto) !== ''
                    && Storage::disk('public')->exists($siswa->user->foto),
            ]),
        ])->header('Cache-Control', 'no-store, private');
    }

    public function studentPhoto(string $student)
    {
        $siswa = Siswa::query()
            ->with('user:id,siswa_id,foto')
            ->findOrFail($student);
        $photoPath = $siswa->user?->foto;

        abort_unless(is_string($photoPath) && trim($photoPath) !== '', 404);

        $disk = Storage::disk('public');
        abort_unless($disk->exists($photoPath), 404);
        abort_unless(in_array($disk->mimeType($photoPath), ['image/jpeg', 'image/png'], true), 415);

        return $disk->response($photoPath, null, [
            'Cache-Control' => 'private, max-age=300',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function academic(Request $request)
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:' . self::MAX_PER_PAGE],
        ]);
        $perPage = (int) ($validated['per_page'] ?? self::DEFAULT_PER_PAGE);
        $page = (int) ($validated['page'] ?? 1);

        $years = TahunAjaran::query()
            ->select(['id', 'nama_tahun', 'is_active'])
            ->orderBy('nama_tahun')
            ->paginate($perPage, ['*'], 'page', $page);
        $semesters = Semester::query()
            ->select(['id', 'tahun_ajaran_id', 'nama_semester', 'is_active'])
            ->with('tahunAjaran:id,nama_tahun')
            ->orderBy('tahun_ajaran_id')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);
        $classes = Kelas::query()
            ->select(['id', 'nama_kelas', 'kode_kelas', 'tingkat_kelas', 'jurusan', 'wali_kelas_id'])
            ->with([
                'waliKelas:id,nip,nama',
                'waliKelas.user:id,guru_id,username,is_active',
            ])
            ->withCount('siswa')
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'source' => 'simadis',
            'schema_version' => 1,
            'password_included' => false,
            'class_membership_scope' => 'current_only',
            'period_membership_available' => false,
            'tahun_ajaran' => $this->pagePayload($years, static fn (TahunAjaran $year): array => [
                'simadis_id' => (string) $year->id,
                'label' => $year->nama_tahun,
                'is_active' => (bool) $year->is_active,
            ]),
            'semester' => $this->pagePayload($semesters, static fn (Semester $semester): array => [
                'simadis_id' => (string) $semester->id,
                'tahun_ajaran_id' => (string) $semester->tahun_ajaran_id,
                'tahun_ajaran_label' => $semester->tahunAjaran?->nama_tahun,
                'label' => $semester->nama_semester,
                'is_active' => (bool) $semester->is_active,
            ]),
            'kelas' => $this->pagePayload($classes, static fn (Kelas $class): array => [
                'simadis_id' => (string) $class->id,
                'nama_kelas' => $class->nama_kelas,
                'kode_kelas' => $class->kode_kelas,
                'tingkat_kelas' => $class->tingkat_kelas,
                'jurusan' => $class->jurusan,
                'current_student_count' => (int) $class->siswa_count,
                'wali_kelas' => $class->waliKelas ? [
                    'simadis_id' => (string) $class->waliKelas->id,
                    'nip' => $class->waliKelas->nip,
                    'nama' => $class->waliKelas->nama,
                    'username' => $class->waliKelas->user?->username,
                    'user_is_active' => $class->waliKelas->user
                        ? (bool) $class->waliKelas->user->is_active
                        : null,
                ] : null,
            ]),
            'limitations' => [
                'class_membership' => 'SIMADIS exposes current student-to-class assignment only.',
                'class_period' => 'SIMADIS class records are not linked to a specific academic year or semester.',
            ],
        ])->header('Cache-Control', 'no-store, private');
    }

    public function teaching(Request $request)
    {
        $validated = $request->validate([
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:' . self::MAX_PER_PAGE],
        ]);
        $perPage = (int) ($validated['per_page'] ?? self::DEFAULT_PER_PAGE);
        $page = (int) ($validated['page'] ?? 1);

        $subjects = MataPelajaran::query()
            ->select(['id', 'kode_mapel', 'nama_mapel', 'kategori', 'jenis_kegiatan_id'])
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);
        $assignments = TugasGuru::query()
            ->select([
                'id',
                'guru_id',
                'mata_pelajaran_id',
                'tingkat_kelas',
                'kelas_id',
                'is_active',
            ])
            ->with([
                'guru:id,nip,nama',
                'mataPelajaran:id,kode_mapel,nama_mapel',
                'kelas:id,kode_kelas,nama_kelas',
            ])
            ->orderBy('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'source' => 'simadis',
            'schema_version' => 1,
            'password_included' => false,
            'assignment_period_available' => false,
            'mata_pelajaran' => $this->pagePayload($subjects, static fn (MataPelajaran $subject): array => [
                'simadis_id' => (string) $subject->id,
                'kode_mapel' => $subject->kode_mapel,
                'nama_mapel' => $subject->nama_mapel,
                'kategori' => $subject->kategori,
                'jenis_kegiatan_id' => $subject->jenis_kegiatan_id !== null
                    ? (string) $subject->jenis_kegiatan_id
                    : null,
            ]),
            'penugasan' => $this->pagePayload($assignments, static fn (TugasGuru $assignment): array => [
                'simadis_id' => (string) $assignment->id,
                'is_active' => (bool) $assignment->is_active,
                'tingkat_kelas' => $assignment->tingkat_kelas,
                'guru' => $assignment->guru ? [
                    'simadis_id' => (string) $assignment->guru->id,
                    'nip' => $assignment->guru->nip,
                    'nama' => $assignment->guru->nama,
                ] : null,
                'mata_pelajaran' => $assignment->mataPelajaran ? [
                    'simadis_id' => (string) $assignment->mataPelajaran->id,
                    'kode_mapel' => $assignment->mataPelajaran->kode_mapel,
                    'nama_mapel' => $assignment->mataPelajaran->nama_mapel,
                ] : null,
                'kelas' => $assignment->kelas ? [
                    'simadis_id' => (string) $assignment->kelas->id,
                    'kode_kelas' => $assignment->kelas->kode_kelas,
                    'nama_kelas' => $assignment->kelas->nama_kelas,
                ] : null,
            ]),
            'limitations' => [
                'assignment_period' => 'SIMADIS teaching assignments are not linked to an academic year or semester.',
                'cbt_assignment_structure' => 'Garuda CBT stores teacher subject assignments in serialized jabatan_guru fields; normalized mapping requires manual review.',
            ],
        ])->header('Cache-Control', 'no-store, private');
    }

    private function pagePayload(LengthAwarePaginator $page, callable $map): array
    {
        return [
            'data' => $page->getCollection()->map($map)->values(),
            'pagination' => [
                'current_page' => $page->currentPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'last_page' => $page->lastPage(),
            ],
        ];
    }
}
