<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbsensiGuru;
use App\Models\AbsensiKelas;
use App\Models\AbsensiSiswa;
use App\Models\Guru;
use App\Models\JadwalKbm;
use App\Models\Kelas;
use App\Models\Semester;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\PersonalAccessToken;

class MobileApiController extends Controller
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

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
        ]);

        $user = $request->user();
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        return response()->json([
            'message' => 'Profil berhasil diperbarui.',
            'user' => $this->userPayload($user),
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Password saat ini tidak cocok.'], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['message' => 'Password berhasil diperbarui.']);
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
            ->select('id', 'nama_kelas', 'kode_kelas', 'tingkat_kelas', 'jurusan', 'wali_kelas_id')
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
            ->select('id', 'nama', 'nip', 'jenis_kelamin', 'status_aktif')
            ->orderBy('nama')
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

    public function mobileDashboard(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasRole('Admin');
        $isGuruPanel = $user->hasAnyRole(['Guru', 'Guru Mapel', 'Guru Kelas', 'Wali Kelas', 'Guru BK', 'Guru Piket']);
        $isSiswa = $user->hasRole('Siswa');
        $isKepalaSekolah = $user->hasRole('Kepala Sekolah');

        $stats = [
            'guru' => DB::table('guru')->count(),
            'siswa' => DB::table('siswa')->count(),
            'kelas' => DB::table('kelas')->count(),
            'absensi' => DB::table('absensi_kelas')->count(),
        ];

        $attendanceSummary = [];
        $classBreakDown = collect();
        $rekapGuruHarian = collect();
        $guruStats = [];

        $tahun = DB::table('tahun_ajaran')->where('is_active',1)->first();
        $semester = DB::table('semester')->where('is_active',1)->first();
        $tahunAjaran = $tahun ? $tahun->nama_tahun : null;
        $semestrName = $semester ? $semester->nama_semester : null;

        if ($isSiswa) {
            $attendanceSummary = $this->buildAttendanceSummary($user);
        } elseif ($isAdmin || $isKepalaSekolah) {
            $tanggalHariIni = now()->toDateString();

            $filterTanggal = $request->get('filter_tanggal');
            $filterMinggu = $request->get('filter_minggu');
            $filterBulan = $request->get('filter_bulan');

            $tanggalMulai = null;
            $tanggalSelesai = null;
            $labelPeriode = 'Hari Ini';
            $periodeTanggal = $tanggalHariIni;

            if ($filterTanggal) {
                $tanggalMulai = $tanggalSelesai = $filterTanggal;
                $periodeTanggal = $filterTanggal;
                $labelPeriode = \Carbon\Carbon::parse($filterTanggal)->format('d M Y');
            } elseif ($filterMinggu) {
                [$year, $week] = explode('-W', $filterMinggu);
                $tanggalMulai = \Carbon\Carbon::now()->setISODate($year, (int) $week)->startOfWeek()->toDateString();
                $tanggalSelesai = \Carbon\Carbon::now()->setISODate($year, (int) $week)->endOfWeek()->toDateString();
                $periodeTanggal = $tanggalMulai;
                $labelPeriode = "Minggu ke-$week, $year";
            } elseif ($filterBulan) {
                [$year, $month] = explode('-', $filterBulan);
                $tanggalMulai = "$year-$month-01";
                $tanggalSelesai = \Carbon\Carbon::create($year, $month)->endOfMonth()->toDateString();
                $periodeTanggal = $tanggalMulai;
                $labelPeriode = \Carbon\Carbon::create($year, $month)->format('M Y');
            }

            if (
                Schema::hasTable('absensi_siswa') &&
                Schema::hasTable('absensi_kelas') &&
                Schema::hasTable('kelas')
            ) {
                $dailySiswaSubQuery = DB::table('absensi_siswa as abs_s')
                    ->join('absensi_kelas as abs_k', 'abs_s.absensi_kelas_id', '=', 'abs_k.id')
                    ->select(
                        'abs_k.kelas_id',
                        'abs_s.siswa_id',
                        DB::raw('DATE(abs_k.tanggal) as tanggal'),
                        DB::raw("MAX(CASE
                            WHEN LOWER(abs_s.status) IN ('alpha','alpa','alfa','absen','tidak_hadir') THEN 5
                            WHEN LOWER(abs_s.status) = 'sakit' THEN 4
                            WHEN LOWER(abs_s.status) IN ('izin','ijin') THEN 3
                            WHEN LOWER(abs_s.status) IN ('terlambat','telat') THEN 2
                            WHEN LOWER(abs_s.status) = 'hadir' THEN 1
                            ELSE 0
                        END) as status_rank")
                    )
                    ->groupBy('abs_k.kelas_id', 'abs_s.siswa_id', DB::raw('DATE(abs_k.tanggal)'));

                if ($tahun) {
                    $dailySiswaSubQuery->where('abs_k.tahun_ajaran_id', $tahun->id);
                }
                if ($semester) {
                    $dailySiswaSubQuery->where('abs_k.semester_id', $semester->id);
                }

                $rekapSiswaPerKelasQuery = DB::query()
                    ->fromSub($dailySiswaSubQuery, 'daily_siswa')
                    ->join('kelas as k', 'daily_siswa.kelas_id', '=', 'k.id')
                    ->when($tanggalMulai && $tanggalSelesai, function ($q) use ($tanggalMulai, $tanggalSelesai) {
                        $q->whereBetween('daily_siswa.tanggal', [$tanggalMulai, $tanggalSelesai]);
                    }, function ($q) use ($tanggalHariIni) {
                        $q->whereDate('daily_siswa.tanggal', $tanggalHariIni);
                    })
                    ->select(
                        'k.id as kelas_id',
                        'k.nama_kelas',
                        DB::raw('COUNT(*) as total_entri'),
                        DB::raw('SUM(CASE WHEN daily_siswa.status_rank = 1 THEN 1 ELSE 0 END) as hadir'),
                        DB::raw('SUM(CASE WHEN daily_siswa.status_rank = 2 THEN 1 ELSE 0 END) as terlambat'),
                        DB::raw('SUM(CASE WHEN daily_siswa.status_rank = 3 THEN 1 ELSE 0 END) as izin'),
                        DB::raw('SUM(CASE WHEN daily_siswa.status_rank = 4 THEN 1 ELSE 0 END) as sakit'),
                        DB::raw('SUM(CASE WHEN daily_siswa.status_rank = 5 THEN 1 ELSE 0 END) as alpha'),
                        DB::raw('MAX(daily_siswa.tanggal) as tanggal_terakhir')
                    )
                    ->groupBy('k.id', 'k.nama_kelas')
                    ->orderBy('k.nama_kelas');

                $classBreakDown = $rekapSiswaPerKelasQuery->get();
            }

            if (
                Schema::hasTable('absensi_guru') &&
                Schema::hasTable('guru')
            ) {
                $rekapGuruHarianQuery = DB::table('absensi_guru as ag')
                    ->when($tanggalMulai && $tanggalSelesai, function ($q) use ($tanggalMulai, $tanggalSelesai) {
                        $q->whereBetween('ag.tanggal', [$tanggalMulai, $tanggalSelesai]);
                    })
                    ->select(
                        'ag.tanggal',
                        DB::raw('COUNT(ag.id) as total_entri'),
                        DB::raw("SUM(CASE WHEN ag.status = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                        DB::raw("SUM(CASE WHEN ag.status = 'izin' THEN 1 ELSE 0 END) as izin"),
                        DB::raw("SUM(CASE WHEN ag.status = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                        DB::raw("SUM(CASE WHEN ag.status = 'tidak_hadir' THEN 1 ELSE 0 END) as tidak_hadir")
                    )
                    ->groupBy('ag.tanggal')
                    ->orderByDesc('ag.tanggal')
                    ->limit(14);

                if ($tahun) {
                    $rekapGuruHarianQuery->where(function ($q) use ($tahun) {
                        $q->where('ag.tahun_ajaran_id', $tahun->id)->orWhereNull('ag.tahun_ajaran_id');
                    });
                }
                if ($semester) {
                    $rekapGuruHarianQuery->where(function ($q) use ($semester) {
                        $q->where('ag.semester_id', $semester->id)->orWhereNull('ag.semester_id');
                    });
                }

                $rekapGuruHarian = $rekapGuruHarianQuery->get();
            }
        } elseif ($isGuruPanel) {
            $guruData = null;
            if ($user->guru_id) {
                $guruData = DB::table('guru')->where('id', $user->guru_id)->first();
            }

            $totalJadwal = 0;
            $jadwalHariIni = 0;
            if ($guruData && Schema::hasTable('jadwal_kbm')) {
                $totalJadwal = DB::table('jadwal_kbm')->where('guru_id', $guruData->id)->count();

                $hariIni = date('l');
                $hariIndonesia = [
                    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
                ];
                $jadwalHariIni = DB::table('jadwal_kbm')
                    ->where('guru_id', $guruData->id)
                    ->where('hari', $hariIndonesia[$hariIni] ?? $hariIni)
                    ->count();
            }

            $totalAbsensiGuru = 0;
            $absensiHariIni = 0;
            if ($guruData && Schema::hasTable('absensi_kelas')) {
                $totalAbsensiGuru = DB::table('absensi_kelas')->where('guru_id', $guruData->id)->count();
                $absensiHariIni = DB::table('absensi_kelas')
                    ->where('guru_id', $guruData->id)
                    ->whereDate('tanggal', date('Y-m-d'))
                    ->count();
            }

            $totalAgenda = 0;
            $agendaMingguIni = 0;
            if ($guruData && Schema::hasTable('agenda')) {
                $totalAgenda = DB::table('agenda')->where('guru_id', $guruData->id)->count();
                $startOfWeek = date('Y-m-d', strtotime('monday this week'));
                $endOfWeek = date('Y-m-d', strtotime('sunday this week'));
                $agendaMingguIni = DB::table('agenda')
                    ->where('guru_id', $guruData->id)
                    ->whereBetween('tanggal', [$startOfWeek, $endOfWeek])
                    ->count();
            }

            $totalNilai = 0;
            $kelasYangDiajar = 0;
            if ($guruData && Schema::hasTable('nilai_harian')) {
                $totalNilai = DB::table('nilai_harian')->where('guru_id', $guruData->id)->count();
                $kelasYangDiajar = DB::table('jadwal_kbm')
                    ->where('guru_id', $guruData->id)
                    ->distinct('kelas_id')
                    ->count('kelas_id');
            }

            $guruStats = [
                'total_jadwal' => $totalJadwal,
                'jadwal_hari_ini' => $jadwalHariIni,
                'total_absensi_guru' => $totalAbsensiGuru,
                'absensi_hari_ini' => $absensiHariIni,
                'total_agenda' => $totalAgenda,
                'agenda_minggu_ini' => $agendaMingguIni,
                'total_nilai' => $totalNilai,
                'kelas_yang_diajar' => $kelasYangDiajar,
            ];
        }

        return response()->json([
            'user' => $this->userPayload($user),
            'stats' => $stats,
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $semestrName,
            'attendance_summary' => $attendanceSummary,
            'class_breakdown' => $classBreakDown->map(function ($item) {
                return [
                    'kelas_id' => $item->kelas_id,
                    'nama_kelas' => $item->nama_kelas,
                    'total_entri' => (int) $item->total_entri,
                    'hadir' => (int) $item->hadir,
                    'terlambat' => (int) $item->terlambat,
                    'izin' => (int) $item->izin,
                    'sakit' => (int) $item->sakit,
                    'alpha' => (int) $item->alpha,
                    'tanggal_terakhir' => $item->tanggal_terakhir,
                ];
            })->values(),
            'rekap_guru_harian' => $rekapGuruHarian->map(function ($item) {
                return [
                    'tanggal' => $item->tanggal,
                    'total_entri' => (int) $item->total_entri,
                    'hadir' => (int) $item->hadir,
                    'izin' => (int) $item->izin,
                    'sakit' => (int) $item->sakit,
                    'tidak_hadir' => (int) $item->tidak_hadir,
                ];
            })->values(),
            'guru_stats' => $guruStats,
        ]);
    }

    public function mobileClasses(Request $request)
    {
        $query = Kelas::query()
            ->select('id', 'nama_kelas', 'kode_kelas', 'tingkat_kelas', 'jurusan', 'wali_kelas_id')
            ->with(['waliKelas:id,nama', 'waliKelas.user:id,name'])
            ->withCount('siswa');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhere('kode_kelas', 'like', "%{$search}%")
                  ->orWhere('jurusan', 'like', "%{$search}%");
            });
        }

        if ($tingkat = $request->get('tingkat_kelas')) {
            $query->where('tingkat_kelas', 'like', "%{$tingkat}%");
        }

        if ($jurusan = $request->get('jurusan')) {
            $query->where('jurusan', $jurusan);
        }

        $user = $request->user();
        if ($user->hasRole('Wali Kelas') && $user->guru_id) {
            $query->where('wali_kelas_id', $user->guru_id);
        }

        $classes = $query->orderBy('nama_kelas')->get();

        return response()->json([
            'classes' => $classes->map(function ($kelas) {
                return [
                    'id' => $kelas->id,
                    'nama_kelas' => $kelas->nama_kelas,
                    'kode_kelas' => $kelas->kode_kelas,
                    'tingkat_kelas' => $kelas->tingkat_kelas,
                    'jurusan' => $kelas->jurusan,
                    'wali_kelas_id' => $kelas->wali_kelas_id,
                    'wali_kelas' => $kelas->waliKelas ? [
                        'id' => $kelas->waliKelas->id,
                        'nama' => $kelas->waliKelas->nama,
                        'user' => $kelas->waliKelas->user ? [
                            'id' => $kelas->waliKelas->user->id,
                            'name' => $kelas->waliKelas->user->name,
                        ] : null,
                    ] : null,
                    'total_siswa' => $kelas->siswa_count,
                ];
            })->values(),
        ]);
    }

    public function classDetail($id)
    {
        $kelas = Kelas::with(['waliKelas:id,nama,email,telepon', 'waliKelas.user:id,name,email'])
            ->withCount('siswa')
            ->findOrFail($id);

        return response()->json([
            'id' => $kelas->id,
            'nama_kelas' => $kelas->nama_kelas,
            'kode_kelas' => $kelas->kode_kelas,
            'tingkat_kelas' => $kelas->tingkat_kelas,
            'jurusan' => $kelas->jurusan,
            'wali_kelas_id' => $kelas->wali_kelas_id,
            'wali_kelas' => $kelas->waliKelas ? [
                'id' => $kelas->waliKelas->id,
                'nama' => $kelas->waliKelas->nama,
                'email' => $kelas->waliKelas->email,
                'telepon' => $kelas->waliKelas->telepon,
                'user' => $kelas->waliKelas->user ? [
                    'id' => $kelas->waliKelas->user->id,
                    'name' => $kelas->waliKelas->user->name,
                ] : null,
            ] : null,
            'total_siswa' => $kelas->siswa_count,
        ]);
    }

    public function classStudents($id)
    {
        $kelas = Kelas::findOrFail($id);

        $students = Siswa::where('kelas_id', $kelas->id)
            ->select('id', 'nama', 'nis', 'nisn', 'kelas_id', 'jenis_kelamin', 'status_aktif', 'jabatan_kelas')
            ->orderBy('nama')
            ->get();

        return response()->json([
            'class' => [
                'id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
            ],
            'students' => $students->map(function ($siswa) {
                return [
                    'id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'nisn' => $siswa->nisn,
                    'kelas_id' => $siswa->kelas_id,
                    'jenis_kelamin' => $siswa->jenis_kelamin,
                    'status_aktif' => (bool) $siswa->status_aktif,
                    'jabatan_kelas' => $siswa->jabatan_kelas,
                ];
            })->values(),
        ]);
    }

    public function mobileStudents(Request $request)
    {
        $query = Siswa::query()
            ->with('kelas:id,nama_kelas')
            ->select('id', 'nama', 'nis', 'nisn', 'kelas_id', 'jenis_kelamin', 'status_aktif', 'jabatan_kelas');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($kelasId = $request->get('kelas_id')) {
            $query->where('kelas_id', $kelasId);
        }

        $user = $request->user();
        if ($user->hasRole('Siswa') && $user->siswa_id) {
            $query->where('id', $user->siswa_id);
        } elseif ($user->hasRole('Wali Kelas') && $user->guru_id) {
            $waliKelas = Kelas::where('wali_kelas_id', $user->guru_id)->pluck('id');
            if ($waliKelas->count() > 0) {
                $query->whereIn('kelas_id', $waliKelas);
            } else {
                $query->where('id', 0);
            }
        }

        $students = $query->orderBy('nama')->get();

        return response()->json([
            'students' => $students->map(function ($siswa) {
                return [
                    'id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'nis' => $siswa->nis,
                    'nisn' => $siswa->nisn,
                    'kelas_id' => $siswa->kelas_id,
                    'kelas' => $siswa->kelas ? ['id' => $siswa->kelas->id, 'nama_kelas' => $siswa->kelas->nama_kelas] : null,
                    'jenis_kelamin' => $siswa->jenis_kelamin,
                    'status_aktif' => (bool) $siswa->status_aktif,
                    'jabatan_kelas' => $siswa->jabatan_kelas,
                ];
            })->values(),
        ]);
    }

    public function studentDetail(Request $request, $id)
    {
        $siswa = Siswa::with('kelas:id,nama_kelas')->findOrFail($id);
        $user = $request->user();

        if ($user->hasRole('Siswa') && $user->siswa_id && $siswa->id != $user->siswa_id) {
            abort(403, 'Akses ditolak.');
        }

        return response()->json([
            'id' => $siswa->id,
            'nama' => $siswa->nama,
            'nis' => $siswa->nis,
            'nisn' => $siswa->nisn,
            'kelas_id' => $siswa->kelas_id,
            'kelas' => $siswa->kelas ? ['id' => $siswa->kelas->id, 'nama_kelas' => $siswa->kelas->nama_kelas] : null,
            'jenis_kelamin' => $siswa->jenis_kelamin,
            'status_aktif' => (bool) $siswa->status_aktif,
            'jabatan_kelas' => $siswa->jabatan_kelas,
        ]);
    }

    public function mobileTeachers(Request $request)
    {
        $query = Guru::query()
            ->select('id', 'nama', 'nip', 'jenis_kelamin', 'status_aktif');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nip', 'like', "%{$search}%");
            });
        }

        $user = $request->user();
        if ($user->hasRole('Guru') && $user->guru_id && !$user->hasRole('Admin')) {
            $query->where('id', $user->guru_id);
        }

        $teachers = $query->orderBy('nama')->get();

        return response()->json([
            'teachers' => $teachers->map(function ($guru) {
                return [
                    'id' => $guru->id,
                    'nama' => $guru->nama,
                    'nip' => $guru->nip,
                    'jenis_kelamin' => $guru->jenis_kelamin,
                    'status_aktif' => (bool) $guru->status_aktif,
                ];
            })->values(),
        ]);
    }

    public function teacherDetail(Request $request, $id)
    {
        $guru = Guru::with('user')->findOrFail($id);
        $user = $request->user();

        if ($user->hasRole('Guru') && $user->guru_id && $guru->id != $user->guru_id && !$user->hasRole('Admin')) {
            abort(403, 'Akses ditolak.');
        }

        return response()->json([
            'id' => $guru->id,
            'nama' => $guru->nama,
            'nip' => $guru->nip,
            'jenis_kelamin' => $guru->jenis_kelamin,
            'telepon' => $guru->telepon,
            'alamat' => $guru->alamat,
            'tanggal_lahir' => $guru->tanggal_lahir,
            'status_aktif' => (bool) $guru->status_aktif,
            'email' => $guru->email,
            'user' => $guru->user ? [
                'id' => $guru->user->id,
                'name' => $guru->user->name,
                'email' => $guru->user->email,
            ] : null,
        ]);
    }

    public function mobileAttendance(Request $request)
    {
        $query = AbsensiKelas::query()
            ->with(['kelas:id,nama_kelas', 'guru:id,nama', 'semester:id,nama_semester', 'tahunAjaran:id,nama_tahun'])
            ->select('id', 'kelas_id', 'guru_id', 'tanggal', 'status_kelas', 'tahun_ajaran_id', 'semester_id');

        if ($tanggal = $request->get('tanggal')) {
            $query->whereDate('tanggal', $tanggal);
        }

        if ($kelasId = $request->get('kelas_id')) {
            $query->where('kelas_id', $kelasId);
        }

        if ($guruId = $request->get('guru_id')) {
            $query->where('guru_id', $guruId);
        }

        $user = $request->user();
        if ($user->hasRole('Guru') && $user->guru_id) {
            $query->where('guru_id', $user->guru_id);
        } elseif ($user->hasRole('Siswa') && $user->siswa_id) {
            $siswa = Siswa::find($user->siswa_id);
            if ($siswa && $siswa->kelas_id) {
                $query->where('kelas_id', $siswa->kelas_id);
            } else {
                $query->where('id', 0);
            }
        }

        $attendance = $query->orderByDesc('tanggal')->paginate(15);

        return response()->json([
            'data' => $attendance->items(),
            'pagination' => [
                'current_page' => $attendance->currentPage(),
                'last_page' => $attendance->lastPage(),
                'per_page' => $attendance->perPage(),
                'total' => $attendance->total(),
            ],
        ]);
    }

    public function attendanceDetail(Request $request, $id)
    {
        $absensi = AbsensiKelas::with(['kelas', 'guru', 'jamBelajar', 'absensiSiswa.siswa'])
            ->findOrFail($id);

        $user = $request->user();
        if ($user->hasRole('Guru') && $user->guru_id && $absensi->guru_id != $user->guru_id) {
            abort(403, 'Akses ditolak.');
        }

        return response()->json([
            'id' => $absensi->id,
            'kelas_id' => $absensi->kelas_id,
            'kelas' => $absensi->kelas ? ['id' => $absensi->kelas->id, 'nama_kelas' => $absensi->kelas->nama_kelas] : null,
            'guru_id' => $absensi->guru_id,
            'guru' => $absensi->guru ? ['id' => $absensi->guru->id, 'nama' => $absensi->guru->nama] : null,
            'tanggal' => $absensi->tanggal ? $absensi->tanggal->format('Y-m-d') : null,
            'status_kelas' => $absensi->status_kelas,
            'tahun_ajaran_id' => $absensi->tahun_ajaran_id,
            'semester_id' => $absensi->semester_id,
            'students' => $absensi->absensiSiswa->map(function ($item) {
                return [
                    'id' => $item->id,
                    'siswa_id' => $item->siswa_id,
                    'nama' => $item->siswa->nama ?? null,
                    'nis' => $item->siswa->nis ?? null,
                    'status' => $item->status,
                    'keterangan' => $item->keterangan,
                ];
            })->values(),
        ]);
    }

    public function bulkUpdateAttendanceStudents(Request $request, $id)
    {
        $request->validate([
            'students' => 'required|array',
            'students.*.absensi_siswa_id' => 'required|integer|exists:absensi_siswa,id',
            'students.*.status' => 'required|string|in:hadir,terlambat,izin,sakit,alpha',
            'students.*.keterangan' => 'nullable|string|max:255',
        ]);

        $absensi = AbsensiKelas::findOrFail($id);
        $user = $request->user();

        if ($user->hasRole('Guru') && $user->guru_id && $absensi->guru_id != $user->guru_id) {
            abort(403, 'Akses ditolak.');
        }

        foreach ($request->students as $item) {
            AbsensiSiswa::where('id', $item['absensi_siswa_id'])
                ->where('absensi_kelas_id', $id)
                ->update([
                    'status' => $item['status'],
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
        }

        return response()->json(['message' => 'Data absensi berhasil diperbarui.']);
    }

    public function createAttendance(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'nullable|exists:guru,id',
            'tanggal' => 'required|date',
            'status_kelas' => 'nullable|string',
            'tahun_ajaran_id' => 'nullable|exists:tahun_ajaran,id',
            'semester_id' => 'nullable|exists:semester,id',
            'students' => 'required|array|min:1',
            'students.*.siswa_id' => 'required|integer|exists:siswa,id',
            'students.*.status' => 'required|string|in:hadir,terlambat,izin,sakit,alpha',
            'students.*.keterangan' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        $guruId = $validated['guru_id'] ?? null;
        if ($user->hasRole('Guru') && $user->guru_id) {
            $guruId = $user->guru_id;
        }

        $absensi = AbsensiKelas::create([
            'kelas_id' => $validated['kelas_id'],
            'guru_id' => $guruId,
            'tanggal' => $validated['tanggal'],
            'status_kelas' => $validated['status_kelas'],
            'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
            'semester_id' => $validated['semester_id'],
        ]);

        foreach ($validated['students'] as $student) {
            AbsensiSiswa::create([
                'absensi_kelas_id' => $absensi->id,
                'siswa_id' => $student['siswa_id'],
                'status' => $student['status'],
                'keterangan' => $student['keterangan'] ?? null,
            ]);
        }

        return response()->json([
            'message' => 'Absensi berhasil dibuat.',
            'data' => [
                'id' => $absensi->id,
                'kelas_id' => $absensi->kelas_id,
                'guru_id' => $absensi->guru_id,
                'tanggal' => $absensi->tanggal,
                'status_kelas' => $absensi->status_kelas,
            ],
        ], 201);
    }

    public function attendanceRekap(Request $request)
    {
        $tanggal = $request->get('tanggal', now()->toDateString());

        $query = DB::table('absensi_guru as ag')
            ->when($request->user()->hasRole('Guru') && $request->user()->guru_id, function ($q) use ($request) {
                $q->where('ag.guru_id', $request->user()->guru_id);
            })
            ->select(
                'ag.tanggal',
                DB::raw('COUNT(ag.id) as total_entri'),
                DB::raw("SUM(CASE WHEN ag.status = 'hadir' THEN 1 ELSE 0 END) as hadir"),
                DB::raw("SUM(CASE WHEN ag.status = 'izin' THEN 1 ELSE 0 END) as izin"),
                DB::raw("SUM(CASE WHEN ag.status = 'sakit' THEN 1 ELSE 0 END) as sakit"),
                DB::raw("SUM(CASE WHEN ag.status = 'tidak_hadir' THEN 1 ELSE 0 END) as tidak_hadir")
            )
            ->whereDate('ag.tanggal', $tanggal)
            ->groupBy('ag.tanggal');

        $result = $query->first();

        return response()->json([
            'tanggal' => $tanggal,
            'total_entri' => (int) ($result->total_entri ?? 0),
            'hadir' => (int) ($result->hadir ?? 0),
            'izin' => (int) ($result->izin ?? 0),
            'sakit' => (int) ($result->sakit ?? 0),
            'tidak_hadir' => (int) ($result->tidak_hadir ?? 0),
        ]);
    }

    public function mobileSchedule(Request $request)
    {
        $user = $request->user();

        if ($user->guru_id) {
            $data = JadwalKbm::with(['jamBelajar:id,jam_mulai,jam_selesai', 'kelas:id,nama_kelas', 'mataPelajaran:id,nama_mapel'])
                ->where('guru_id', $user->guru_id)
                ->orderBy('hari')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'hari' => $item->hari,
                        'jam_mulai' => $item->jamBelajar->jam_mulai ?? null,
                        'jam_selesai' => $item->jamBelajar->jam_selesai ?? null,
                        'jam_ke' => $item->jam_ke,
                        'kelas_id' => $item->kelas_id,
                        'kelas' => $item->kelas ? ['id' => $item->kelas->id, 'nama_kelas' => $item->kelas->nama_kelas] : null,
                        'mapel_id' => $item->mata_pelajaran_id,
                        'mapel' => $item->mataPelajaran ? ['id' => $item->mataPelajaran->id, 'nama_mapel' => $item->mataPelajaran->nama_mapel] : null,
                    ];
                })->values();

            return response()->json(['schedule' => $data]);
        }

        if ($user->hasRole('Siswa') && $user->siswa_id) {
            $siswa = Siswa::with('kelas:id')->find($user->siswa_id);
            if ($siswa && $siswa->kelas_id) {
                $data = JadwalKbm::with(['jamBelajar:id,jam_mulai,jam_selesai', 'kelas:id,nama_kelas', 'mataPelajaran:id,nama_mapel'])
                    ->where('kelas_id', $siswa->kelas_id)
                    ->orderBy('hari')
                    ->get()
                    ->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'hari' => $item->hari,
                            'jam_mulai' => $item->jamBelajar->jam_mulai ?? null,
                            'jam_selesai' => $item->jamBelajar->jam_selesai ?? null,
                            'jam_ke' => $item->jam_ke,
                            'kelas_id' => $item->kelas_id,
                            'kelas' => $item->kelas ? ['id' => $item->kelas->id, 'nama_kelas' => $item->kelas->nama_kelas] : null,
                            'mapel_id' => $item->mata_pelajaran_id,
                            'mapel' => $item->mataPelajaran ? ['id' => $item->mataPelajaran->id, 'nama_mapel' => $item->mataPelajaran->nama_mapel] : null,
                        ];
                    })->values();

                return response()->json(['schedule' => $data]);
            }
        }

        return response()->json(['schedule' => []]);
    }

    public function activeTahunAjaran()
    {
        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        return response()->json([
            'data' => $tahun ? [
                'id' => $tahun->id,
                'nama_tahun' => $tahun->nama_tahun,
                'is_active' => (bool) $tahun->is_active,
            ] : null,
        ]);
    }

    public function activeSemester()
    {
        $semester = DB::table('semester')->where('is_active', 1)->first();
        return response()->json([
            'data' => $semester ? [
                'id' => $semester->id,
                'nama_semester' => $semester->nama_semester,
                'is_active' => (bool) $semester->is_active,
            ] : null,
        ]);
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
