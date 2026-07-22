<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\JenisPelanggaran;

class PiketPelanggaranController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $hariPiketArr = (array) ($user->guru->hari_piket ?? []);
        $todayEng = \Carbon\Carbon::now()->format('l');
        $map = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $todayIndo = $map[$todayEng] ?? null;
        $isGuruPiket = $user && in_array($todayIndo, $hariPiketArr, true);

        if (!$isGuruPiket || empty($user->guru_id)) {
            abort(403, 'Menu ini hanya untuk Guru Piket.');
        }

        $tahunAjaranAktif = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semesterAktif = DB::table('semester')->where('is_active', 1)->first();

        $kelasAktif = DB::table('jadwal_kbm as jk')
            ->join('kelas as k', 'jk.kelas_id', '=', 'k.id')
            ->when($tahunAjaranAktif, function ($query) use ($tahunAjaranAktif) {
                $query->where('jk.tahun_ajaran_id', $tahunAjaranAktif->id);
            })
            ->when($semesterAktif, function ($query) use ($semesterAktif) {
                $query->where('jk.semester_id', $semesterAktif->id);
            })
            ->select('k.id', 'k.nama_kelas')
            ->distinct()
            ->orderBy('k.nama_kelas')
            ->get();

        $kelasId = $request->get('kelas_id');
        $selectedTanggal = $request->get('tanggal', now()->format('Y-m-d'));

        $siswaList = collect();
        $existingBySiswa = collect();
        $absensiBySiswa = collect();
        $jamKeSatu = DB::table('jam_belajar')->orderBy('urutan')->first();

        if (!empty($kelasId)) {
            $siswaList = DB::table('siswa')
                ->where('kelas_id', $kelasId)
                ->orderBy('nama')
                ->get(['id', 'nis', 'nama', 'jabatan_kelas']);

            $existingBySiswa = DB::table('pelanggaran_siswa')
                ->where('kelas_id', $kelasId)
                ->whereDate('tanggal', $selectedTanggal)
                ->get()
                ->keyBy('siswa_id');

            $absensiBySiswa = DB::table('absensi_siswa as asi')
                ->join('absensi_kelas as ak', 'asi.absensi_kelas_id', '=', 'ak.id')
                ->where('ak.kelas_id', $kelasId)
                ->whereDate('ak.tanggal', $selectedTanggal)
                ->when($jamKeSatu, fn ($query) => $query->where('ak.jam_belajar_id', $jamKeSatu->id))
                ->select('asi.siswa_id', 'asi.status', 'asi.keterangan')
                ->get()
                ->keyBy('siswa_id');
        }

        $jenisPelanggaranOptions = JenisPelanggaran::query()
            ->where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'kode', 'nama', 'poin_default']);

        $lateMinutesPreview = 0;
        if ($jamKeSatu && !empty($jamKeSatu->jam_mulai)) {
            $mulai = Carbon::parse($selectedTanggal . ' ' . $jamKeSatu->jam_mulai);
            $now = now();
            $lateMinutesPreview = $now->greaterThan($mulai) ? $mulai->diffInMinutes($now) : 0;
        }

        return view('piket_kbm.pelanggaran', compact(
            'kelasAktif',
            'kelasId',
            'selectedTanggal',
            'siswaList',
            'existingBySiswa',
            'absensiBySiswa',
            'jenisPelanggaranOptions',
            'jamKeSatu',
            'lateMinutesPreview'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $hariPiketArr = (array) ($user->guru->hari_piket ?? []);
        $todayEng = \Carbon\Carbon::now()->format('l');
        $map = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        $todayIndo = $map[$todayEng] ?? null;
        $isGuruPiket = $user && in_array($todayIndo, $hariPiketArr, true);

        if (!$isGuruPiket || empty($user->guru_id)) {
            abort(403, 'Menu ini hanya untuk Guru Piket.');
        }

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tanggal' => 'required|date',
            'status' => 'required|array|min:1',
            'status.*' => 'required|in:hadir,terlambat,sakit,izin,alpa',
            'jenis_pelanggaran_id' => 'nullable|array',
            'jenis_pelanggaran_id.*' => 'nullable|exists:jenis_pelanggaran,id',
            'point' => 'nullable|array',
            'point.*' => 'nullable|integer|min:0|max:1000',
            'pelanggaran' => 'nullable|array',
            'pelanggaran.*' => 'nullable|string|max:1000',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:255',
        ]);

        $tahunAjaranAktif = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semesterAktif = DB::table('semester')->where('is_active', 1)->first();
        $jamKeSatu = DB::table('jam_belajar')->orderBy('urutan')->first();

        $jamBelajarId = $jamKeSatu->id ?? DB::table('jam_belajar')->value('id');
        if (!$jamBelajarId) {
            return back()->withInput()->withErrors(['error' => 'Data jam belajar belum tersedia.']);
        }

        $jamMulai = $jamKeSatu->jam_mulai ?? null;
        $mulai = $jamMulai ? Carbon::parse($validated['tanggal'] . ' ' . $jamMulai) : null;
        $waktuInput = now();
        $lateMinutes = ($mulai && $waktuInput->greaterThan($mulai)) ? $mulai->diffInMinutes($waktuInput) : 0;

        $statusMap = [
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'sakit' => 'Sakit',
            'izin' => 'Izin',
            'alpa' => 'Absen',
        ];

        DB::transaction(function () use ($validated, $user, $tahunAjaranAktif, $semesterAktif, $jamBelajarId, $statusMap, $lateMinutes, $waktuInput, $jamMulai) {
            $absensi = DB::table('absensi_kelas')
                ->where('kelas_id', $validated['kelas_id'])
                ->where('guru_id', $user->guru_id)
                ->where('jam_belajar_id', $jamBelajarId)
                ->whereDate('tanggal', $validated['tanggal'])
                ->when($tahunAjaranAktif, fn ($q) => $q->where('tahun_ajaran_id', $tahunAjaranAktif->id))
                ->when($semesterAktif, fn ($q) => $q->where('semester_id', $semesterAktif->id))
                ->first();

            if (!$absensi) {
                $absensiId = DB::table('absensi_kelas')->insertGetId([
                    'kelas_id' => $validated['kelas_id'],
                    'guru_id' => $user->guru_id,
                    'jam_belajar_id' => $jamBelajarId,
                    'tanggal' => $validated['tanggal'],
                    'status_kelas' => 'Monitoring Piket KBM',
                    'tahun_ajaran_id' => $tahunAjaranAktif->id ?? null,
                    'semester_id' => $semesterAktif->id ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $absensiId = $absensi->id;
            }

            foreach ($validated['status'] as $siswaId => $status) {
                $normalizedStatus = $statusMap[$status] ?? null;
                if (!$normalizedStatus) {
                    continue;
                }

                $keterangan = $validated['keterangan'][$siswaId] ?? null;
                $jenisPelanggaranId = $validated['jenis_pelanggaran_id'][$siswaId] ?? null;
                $jenisPelanggaran = $jenisPelanggaranId ? JenisPelanggaran::find($jenisPelanggaranId) : null;
                $deskripsiPelanggaran = $validated['pelanggaran'][$siswaId] ?? ($jenisPelanggaran->nama ?? null);
                $pointPelanggaran = $validated['point'][$siswaId] ?? null;
                if ($pointPelanggaran === null && $jenisPelanggaran) {
                    $pointPelanggaran = (int) $jenisPelanggaran->poin_default;
                }
                if ($pointPelanggaran === null) {
                    $pointPelanggaran = 0;
                }

                $existingAbsensiSiswa = DB::table('absensi_siswa')
                    ->where('absensi_kelas_id', $absensiId)
                    ->where('siswa_id', $siswaId)
                    ->first();

                if ($existingAbsensiSiswa) {
                    DB::table('absensi_siswa')
                        ->where('id', $existingAbsensiSiswa->id)
                        ->update([
                            'status' => $normalizedStatus,
                            'keterangan' => $keterangan,
                            'updated_at' => now(),
                        ]);
                } else {
                    DB::table('absensi_siswa')->insert([
                        'absensi_kelas_id' => $absensiId,
                        'siswa_id' => $siswaId,
                        'status' => $normalizedStatus,
                        'keterangan' => $keterangan,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $terlambatMenit = $status === 'terlambat' ? $lateMinutes : 0;
                $hasPelanggaran = !empty($deskripsiPelanggaran) || $status === 'terlambat';

                if ($hasPelanggaran) {
                    DB::table('pelanggaran_siswa')->updateOrInsert(
                        [
                            'kelas_id' => $validated['kelas_id'],
                            'siswa_id' => $siswaId,
                            'tanggal' => $validated['tanggal'],
                        ],
                        [
                            'guru_piket_id' => $user->guru_id,
                            'absensi_kelas_id' => $absensiId,
                            'status_absensi' => strtolower($normalizedStatus),
                            'deskripsi_pelanggaran' => $deskripsiPelanggaran,
                            'poin_pelanggaran' => $pointPelanggaran,
                            'jam_ke_1_mulai' => $jamMulai,
                            'waktu_input_pelanggaran' => $waktuInput,
                            'terlambat_menit' => $terlambatMenit,
                            'tahun_ajaran_id' => $tahunAjaranAktif->id ?? null,
                            'semester_id' => $semesterAktif->id ?? null,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        });

        return redirect()->route('piket.pelanggaran.index', [
            'kelas_id' => $validated['kelas_id'],
            'tanggal' => $validated['tanggal'],
        ])->with('success', 'Absensi dan pelanggaran siswa berhasil disimpan.');
    }
}
