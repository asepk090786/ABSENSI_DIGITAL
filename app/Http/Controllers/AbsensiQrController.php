<?php

namespace App\Http\Controllers;

use App\Models\AbsensiKelas;
use App\Models\AbsensiSiswa;
use App\Models\JadwalKbm;
use App\Models\QrLoginToken;
use App\Models\Semester;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiQrController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        abort_unless($this->isTeacher($user), 403);

        $scheduleContext = $this->todayScheduleContext($user);

        return view('absensi.qr-scan', [
            'schedules' => $scheduleContext['schedules'],
            'currentSchedule' => $scheduleContext['current'],
        ]);
    }

    public function scan(Request $request)
    {
        $user = $request->user();
        if (! $this->isTeacher($user)) {
            return response()->json(['success' => false, 'message' => 'Fitur ini hanya dapat digunakan oleh guru.'], 403);
        }

        $validated = $request->validate([
            'qr_text' => ['required', 'string', 'max:2000'],
            'status_kelas' => ['required', 'string', 'in:Sangat Kondusif,Kondusif,Normal,Kurang Kondusif,Tidak Kondusif'],
        ]);

        $token = $this->extractLoginToken($validated['qr_text']);
        if (! $token) {
            return response()->json(['success' => false, 'message' => 'QR code bukan kartu login SIMADIS yang valid.'], 422);
        }

        $loginToken = QrLoginToken::with('user.siswa')
            ->where('token_hash', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();

        $studentUser = $loginToken?->user;
        if (! $studentUser || ! $studentUser->is_active || ! $studentUser->hasRole('Siswa') || ! $studentUser->siswa_id || ! $studentUser->siswa) {
            return response()->json(['success' => false, 'message' => 'QR code siswa tidak valid atau sudah tidak aktif.'], 422);
        }

        $tahun = TahunAjaran::where('is_active', 1)->first();
        $semester = Semester::where('is_active', 1)->first();
        if (! $tahun || ! $semester) {
            return response()->json(['success' => false, 'message' => 'Tahun ajaran atau semester aktif belum diset.'], 422);
        }

        $tanggal = Carbon::today()->toDateString();
        $dayMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
        ];
        $today = Carbon::now('Asia/Jakarta');
        $schedules = JadwalKbm::with(['kelas', 'jamBelajar', 'mataPelajaran'])
            ->where('guru_id', $user->guru_id)
            ->where('kelas_id', $studentUser->siswa->kelas_id)
            ->where('hari', $dayMap[$today->format('l')] ?? $today->format('l'))
            ->where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id)
            ->orderBy('jam_ke')
            ->get()
            ->unique('id')
            ->values();

        if ($schedules->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => sprintf('Tidak ada jadwal Anda hari ini untuk kelas %s.', $studentUser->siswa->nama_kelas ?? 'siswa tersebut'),
            ], 422);
        }

        $attendanceItems = DB::transaction(function () use ($schedules, $tahun, $semester, $tanggal, $studentUser, $validated) {
            $items = [];

            foreach ($schedules as $schedule) {
                $absensi = AbsensiKelas::firstOrCreate(
                    [
                        'kelas_id' => $schedule->kelas_id,
                        'guru_id' => $schedule->guru_id,
                        'jam_belajar_id' => $schedule->jam_belajar_id,
                        'tanggal' => $tanggal,
                        'tahun_ajaran_id' => $tahun->id,
                        'semester_id' => $semester->id,
                    ],
                    ['status_kelas' => $validated['status_kelas']]
                );

                if ($absensi->status_kelas !== $validated['status_kelas']) {
                    $absensi->update(['status_kelas' => $validated['status_kelas']]);
                }

                AbsensiSiswa::updateOrCreate(
                    ['absensi_kelas_id' => $absensi->id, 'siswa_id' => $studentUser->siswa_id],
                    ['status' => 'hadir', 'keterangan' => null]
                );

                $items[] = [
                    'mapel' => $schedule->mataPelajaran->nama_mapel ?? 'Mata Pelajaran',
                    'jam' => $schedule->jamBelajar->urutan ?? $schedule->jam_ke,
                    'absensi_kelas_id' => $absensi->id,
                ];
            }

            return $items;
        });

        return response()->json([
            'success' => true,
            'message' => sprintf('%s tercatat hadir pada %d jadwal mapel hari ini untuk kelas %s.', $studentUser->siswa->nama, count($attendanceItems), $studentUser->siswa->kelas->nama_kelas ?? 'siswa tersebut'),
            'student' => $studentUser->siswa->nama,
            'class' => $studentUser->siswa->kelas->nama_kelas ?? null,
            'lesson' => $attendanceItems[0]['jam'] ?? null,
            'absensi_kelas_id' => $attendanceItems[0]['absensi_kelas_id'] ?? null,
            'lessons' => $attendanceItems,
            'status_kelas' => $validated['status_kelas'],
        ]);
    }

    public function save(Request $request)
    {
        $user = $request->user();
        if (! $this->isTeacher($user)) {
            return response()->json(['success' => false, 'message' => 'Fitur ini hanya dapat digunakan oleh guru.'], 403);
        }

        $validated = $request->validate([
            'status_kelas' => ['required', 'string', 'in:Sangat Kondusif,Kondusif,Normal,Kurang Kondusif,Tidak Kondusif'],
        ]);

        $scheduleContext = $this->todayScheduleContext($user);
        $schedule = $scheduleContext['current'];
        if (! $schedule) {
            return response()->json(['success' => false, 'message' => 'Tidak ada jadwal mengajar Anda yang sedang berlangsung hari ini.'], 422);
        }

        $tahun = TahunAjaran::where('is_active', 1)->first();
        $semester = Semester::where('is_active', 1)->first();
        if (! $tahun || ! $semester) {
            return response()->json(['success' => false, 'message' => 'Tahun ajaran atau semester aktif belum diset.'], 422);
        }

        $tanggal = Carbon::today()->toDateString();
        $result = DB::transaction(function () use ($schedule, $tahun, $semester, $tanggal, $validated) {
            $absensi = AbsensiKelas::firstOrCreate(
                [
                    'kelas_id' => $schedule->kelas_id,
                    'guru_id' => $schedule->guru_id,
                    'jam_belajar_id' => $schedule->jam_belajar_id,
                    'tanggal' => $tanggal,
                    'tahun_ajaran_id' => $tahun->id,
                    'semester_id' => $semester->id,
                ],
                ['status_kelas' => $validated['status_kelas']]
            );

            $absensi->update(['status_kelas' => $validated['status_kelas']]);

            $studentIds = DB::table('siswa')
                ->where('kelas_id', $schedule->kelas_id)
                ->where('status_aktif', 1)
                ->pluck('id');

            $recordedStudentIds = AbsensiSiswa::where('absensi_kelas_id', $absensi->id)
                ->whereIn('siswa_id', $studentIds)
                ->whereNotIn('status', ['alpa', 'alpha', 'alfa', 'absen', 'tidak_hadir'])
                ->pluck('siswa_id');

            $alpaCount = 0;
            foreach ($studentIds->diff($recordedStudentIds) as $studentId) {
                AbsensiSiswa::create([
                    'absensi_kelas_id' => $absensi->id,
                    'siswa_id' => $studentId,
                    'status' => 'alpa',
                    'keterangan' => 'Tidak melakukan scan QR.',
                ]);
                $alpaCount++;
            }

            return [$absensi, $recordedStudentIds->count(), $alpaCount];
        });

        return response()->json([
            'success' => true,
            'message' => sprintf('Absensi berhasil disimpan. %d siswa tercatat hadir dan %d siswa tercatat alpa.', $result[1], $result[2]),
            'hadir_count' => $result[1],
            'alpa_count' => $result[2],
            'absensi_kelas_id' => $result[0]->id,
        ]);
    }

    private function todayScheduleContext($user): array
    {
        $tahun = TahunAjaran::where('is_active', 1)->first();
        $semester = Semester::where('is_active', 1)->first();
        $dayMap = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu',
        ];
        $now = Carbon::now('Asia/Jakarta');

        $schedules = JadwalKbm::with(['kelas', 'jamBelajar'])
            ->where('guru_id', $user->guru_id)
            ->where('hari', $dayMap[$now->format('l')] ?? $now->format('l'))
            ->when($tahun, fn ($query) => $query->where('tahun_ajaran_id', $tahun->id))
            ->when($semester, fn ($query) => $query->where('semester_id', $semester->id))
            ->get()
            ->sortBy(fn ($schedule) => $schedule->jamBelajar->urutan ?? PHP_INT_MAX)
            ->values();

        $current = $schedules->first(function ($schedule) use ($now) {
            $start = $this->scheduleTime($schedule->jamBelajar?->jam_mulai, $now);
            $end = $this->scheduleTime($schedule->jamBelajar?->jam_selesai, $now);
            return $start && $end && $now->betweenIncluded($start, $end);
        });

        return ['schedules' => $schedules, 'current' => $current];
    }

    private function scheduleTime(?string $time, Carbon $date): ?Carbon
    {
        if (! $time) {
            return null;
        }

        [$hour, $minute] = array_pad(explode(':', $time), 2, 0);
        return $date->copy()->setTime((int) $hour, (int) $minute, 0);
    }

    private function extractLoginToken(string $value): ?string
    {
        $path = parse_url(trim($value), PHP_URL_PATH);
        $segments = $path ? explode('/', trim($path, '/')) : [];
        $index = array_search('qr-login', $segments, true);
        $token = $index !== false ? ($segments[$index + 1] ?? null) : null;

        return $token && preg_match('/^[A-Za-z0-9]{64}$/', $token) ? $token : null;
    }

    private function isTeacher($user): bool
    {
        return $user && $user->guru_id && $user->hasAnyRole([
            'Guru', 'Guru Mapel', 'Guru Kelas', 'Wali Kelas', 'Guru BK', 'Guru Piket',
        ]);
    }
}
