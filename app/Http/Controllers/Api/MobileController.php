<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class MobileController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($field, $data['login'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Kredensial tidak valid.',
            ], 401);
        }

        if (! $user->is_active) {
            return response()->json([
                'message' => 'Akun tidak aktif.',
            ], 403);
        }

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $this->userPayload($user),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil.']);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function dashboard(Request $request)
    {
        $user = $request->user();

        $stats = [
            'guru' => DB::table('guru')->count(),
            'siswa' => DB::table('siswa')->count(),
            'kelas' => DB::table('kelas')->count(),
            'absensi' => DB::table('absensi_kelas')->count(),
        ];

        $attendance = $this->buildAttendanceSummary($user);

        return response()->json([
            'user' => $this->userPayload($user),
            'stats' => $stats,
            'attendance' => $attendance,
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'user' => $this->userPayload($request->user()),
        ]);
    }

    public function attendanceSummary(Request $request)
    {
        return response()->json([
            'attendance' => $this->buildAttendanceSummary($request->user()),
        ]);
    }

    public function classes(Request $request)
    {
        $classes = Kelas::query()
            ->select('id', 'nama_kelas', 'kode_kelas', 'tingkat_kelas', 'jurusan')
            ->orderBy('nama_kelas')
            ->get();

        return response()->json(['classes' => $classes]);
    }

    public function students(Request $request)
    {
        $students = Siswa::query()
            ->with('kelas:id,nama_kelas')
            ->select('id', 'nama', 'nis', 'nisn', 'kelas_id', 'jenis_kelamin', 'status_aktif', 'jabatan_kelas')
            ->orderBy('nama')
            ->get();

        return response()->json(['students' => $students]);
    }

    public function teachers(Request $request)
    {
        $teachers = Guru::query()
            ->select('id', 'nama_guru', 'nip', 'jenis_kelamin', 'status_aktif')
            ->orderBy('nama_guru')
            ->get();

        return response()->json(['teachers' => $teachers]);
    }

    public function schedule(Request $request)
    {
        $user = $request->user();
        $data = [];

        if ($user->guru_id) {
            $data = DB::table('jadwal_kbm')
                ->where('guru_id', $user->guru_id)
                ->select('id', 'hari', 'jam_mulai', 'jam_selesai', 'kelas_id', 'mapel_id')
                ->orderBy('hari')
                ->get();
        }

        return response()->json(['schedule' => $data]);
    }

    protected function buildAttendanceSummary(User $user): array
    {
        $summary = [
            'hadir' => 0,
            'terlambat' => 0,
            'izin' => 0,
            'sakit' => 0,
            'alpha' => 0,
            'total' => 0,
            'present_percent' => 0,
        ];

        if ($user->siswa_id && $user->siswa) {
            $rows = DB::table('absensi_siswa')
                ->join('absensi_kelas', 'absensi_kelas.id', '=', 'absensi_siswa.absensi_kelas_id')
                ->where('absensi_siswa.siswa_id', $user->siswa_id)
                ->select('absensi_siswa.status')
                ->get();

            foreach ($rows as $row) {
                $status = mb_strtolower(trim((string) $row->status));

                if ($status === 'hadir') {
                    $summary['hadir']++;
                } elseif (in_array($status, ['terlambat', 'telat'], true)) {
                    $summary['terlambat']++;
                } elseif (in_array($status, ['izin', 'ijin'], true)) {
                    $summary['izin']++;
                } elseif ($status === 'sakit') {
                    $summary['sakit']++;
                } elseif (in_array($status, ['alpa', 'alpha', 'alfa', 'absen'], true)) {
                    $summary['alpha']++;
                }
            }

            $summary['total'] = $summary['hadir'] + $summary['terlambat'] + $summary['izin'] + $summary['sakit'] + $summary['alpha'];
            $summary['present_percent'] = $summary['total'] > 0 ? round(($summary['hadir'] / $summary['total']) * 100, 2) : 0;
        }

        return $summary;
    }

    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'role' => $user->role?->role_name,
            'roles' => $user->roleNames(),
            'guru_id' => $user->guru_id,
            'siswa_id' => $user->siswa_id,
            'is_active' => (bool) $user->is_active,
        ];
    }
}
