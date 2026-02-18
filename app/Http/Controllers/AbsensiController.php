<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AbsensiKelas;
use App\Models\AgendaGuru;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\JamBelajar;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\JadwalKbm;
use Carbon\Carbon;

class AbsensiController extends Controller
{
    public function index()
    {
        $tahun = DB::table('tahun_ajaran')->where('is_active',1)->first();
        $semester = DB::table('semester')->where('is_active',1)->first();

        if (! $tahun || ! $semester) {
            $items = collect();
            return view('absensi.index', compact('items'))
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $user = auth()->user();
        $isAdminOrKepala = $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $query = AbsensiKelas::with(['kelas', 'guru', 'jamBelajar', 'tahunAjaran', 'semester', 'absensiSiswa'])
            ->where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id);
        
        // Filter by guru_id if user is a teacher (not admin or kepala sekolah)
        if ($user->guru_id && !$isAdminOrKepala) {
            $query->where('guru_id', $user->guru_id);
        }
        
        $items = $query->orderBy('tanggal', 'desc')->get();
        
        // Get quick access classes for teacher
        $kelasQuickAccess = collect();
        if ($user->guru_id && !$isAdminOrKepala) {
            // Get all classes taught by this teacher in current semester
            $kelasQuickAccess = JadwalKbm::with(['kelas'])
                ->where('guru_id', $user->guru_id)
                ->where('tahun_ajaran_id', $tahun->id)
                ->where('semester_id', $semester->id)
                ->get()
                ->pluck('kelas')
                ->unique('id')
                ->sortBy('nama_kelas')
                ->values();
        }
        
        return view('absensi.index', compact('items', 'kelasQuickAccess'));
    }

    public function create(Request $request)
    {
        $tahunAjaran = TahunAjaran::where('is_active', 1)->first();
        $semester = Semester::where('is_active', 1)->first();

        if (!$tahunAjaran || !$semester) {
            return redirect()->route('absensi.index')
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $user = auth()->user();
        $isAdminOrKepala = $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        $selectedKelasId = $request->get('kelas_id');
        $selectedJamBelajarId = null;
        $isQuickAccess = false;
        $selectedDate = $request->get('tanggal', date('Y-m-d'));
        
        // Check if this is quick access or manual with kelas preselected
        if (!empty($selectedKelasId)) {
            $isQuickAccess = true;
        }
        
        // Validate teacher schedule access
        if ($user->guru_id && !$isAdminOrKepala) {
            if ($selectedKelasId) {
                $hariIndonesia = [
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                    'Sunday' => 'Minggu'
                ];
                $hariEnglish = Carbon::parse($selectedDate)->format('l');
                $hariQuery = $hariIndonesia[$hariEnglish] ?? $hariEnglish;
                
                $hasSchedule = JadwalKbm::where('guru_id', $user->guru_id)
                    ->where('kelas_id', $selectedKelasId)
                    ->where('hari', $hariQuery)
                    ->where('tahun_ajaran_id', $tahunAjaran->id)
                    ->where('semester_id', $semester->id)
                    ->exists();
                    
                if (!$hasSchedule) {
                    // Don't redirect, just show warning - let user change date
                    session()->flash('warning', 'Anda tidak memiliki jadwal mengajar di kelas ini pada hari ' . $hariQuery . ' (' . date('d/m/Y', strtotime($selectedDate)) . '). Silakan pilih tanggal lain.');
                    // Clear selectedKelasId so form shows normally but kelas is pre-selected
                    // Keep the kelas selected but don't auto-load siswa
                }
            }
        }

        $hariIndonesia = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];

        $multiSlotJadwal = collect();
        
        // Get jadwal for current user if they are a teacher
        if ($user->guru_id && !$isAdminOrKepala) {
            // Get today's schedule for display
            $hariIni = date('l');
            $jadwalHariIni = JadwalKbm::with(['kelas', 'jamBelajar', 'mataPelajaran'])
                ->where('guru_id', $user->guru_id)
                ->where('hari', $hariIndonesia[$hariIni] ?? $hariIni)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('semester_id', $semester->id)
                ->orderBy('jam_ke')
                ->get();
            
            // If coming from quick access, auto-fill jam belajar from today's schedule
            if ($isQuickAccess && $selectedKelasId) {
                $jadwalKelas = $jadwalHariIni->where('kelas_id', $selectedKelasId)->first();
                if ($jadwalKelas) {
                    $selectedJamBelajarId = $jadwalKelas->jam_belajar_id;
                }
            }
            
            // Get ALL classes taught by this teacher (all days)
            $allJadwal = JadwalKbm::with(['kelas'])
                ->where('guru_id', $user->guru_id)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('semester_id', $semester->id)
                ->get();
            
            // Get unique classes from all schedules
            $kelasList = $allJadwal->pluck('kelas')->unique('id')->sortBy('nama_kelas')->values();
            
            // Filter jam belajar based on teacher's schedule for selected class and date
            if ($selectedKelasId) {
                $hariEnglish = Carbon::parse($selectedDate)->format('l');
                $hariQuery = $hariIndonesia[$hariEnglish] ?? $hariEnglish;

                $multiSlotJadwal = JadwalKbm::with(['jamBelajar'])
                    ->where('guru_id', $user->guru_id)
                    ->where('kelas_id', $selectedKelasId)
                    ->where('hari', $hariQuery)
                    ->where('tahun_ajaran_id', $tahunAjaran->id)
                    ->where('semester_id', $semester->id)
                    ->orderBy('jam_ke')
                    ->get();
                    
                // Only show jam belajar from teacher's schedule
                $scheduledJamIds = $multiSlotJadwal->pluck('jam_belajar_id')->unique();
                $jamBelajarList = JamBelajar::whereIn('id', $scheduledJamIds)->orderBy('urutan')->get();
                
                if (!$selectedJamBelajarId && $multiSlotJadwal->isNotEmpty()) {
                    $selectedJamBelajarId = $multiSlotJadwal->first()->jam_belajar_id;
                }
            } else {
                $jamBelajarList = JamBelajar::orderBy('urutan')->get();
            }
            
            $guruList = Guru::where('id', $user->guru_id)->get();
            $jadwalList = $jadwalHariIni;
        } else {
            // Admin or kepala sekolah can see all
            $kelasList = Kelas::orderBy('nama_kelas')->get();
            $guruList = Guru::orderBy('nama')->get();
            $jamBelajarList = JamBelajar::orderBy('urutan')->get();
            $jadwalList = collect();
        }

        return view('absensi.create', compact(
            'kelasList',
            'guruList',
            'jamBelajarList',
            'tahunAjaran',
            'semester',
            'jadwalList',
            'selectedKelasId',
            'selectedJamBelajarId',
            'isQuickAccess',
            'selectedDate',
            'multiSlotJadwal'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:guru,id',
            'jam_belajar_id' => 'required|exists:jam_belajar,id',
            'tanggal' => 'required|date',
            'status_kelas' => 'nullable|string|max:100',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'semester_id' => 'required|exists:semester,id',
        ]);
        
        // Validate teacher can only input attendance for their schedule
        $user = auth()->user();
        $isAdminOrKepala = $user->hasAnyRole(['Admin', 'Kepala Sekolah']);
        if ($user->guru_id && !$isAdminOrKepala) {
            if ($validated['guru_id'] != $user->guru_id) {
                return back()->withErrors(['error' => 'Anda hanya dapat menginput absensi untuk jadwal Anda sendiri.']);
            }
            
            $hariIndonesia = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu'
            ];
            $hariEnglish = Carbon::parse($validated['tanggal'])->format('l');
            $hariQuery = $hariIndonesia[$hariEnglish] ?? $hariEnglish;
            
            $hasSchedule = JadwalKbm::where('guru_id', $user->guru_id)
                ->where('kelas_id', $validated['kelas_id'])
                ->where('hari', $hariQuery)
                ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
                ->where('semester_id', $validated['semester_id'])
                ->exists();
                
            if (!$hasSchedule) {
                return back()->withErrors(['error' => 'Anda tidak memiliki jadwal mengajar di kelas ini pada hari tersebut.']);
            }
        }

        try {
            DB::beginTransaction();

            $hariIndonesia = [
                'Monday' => 'Senin',
                'Tuesday' => 'Selasa',
                'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis',
                'Friday' => 'Jumat',
                'Saturday' => 'Sabtu',
                'Sunday' => 'Minggu'
            ];

            $hariEnglish = Carbon::parse($validated['tanggal'])->format('l');
            $hariQuery = $hariIndonesia[$hariEnglish] ?? $hariEnglish;

            // Cari semua jam mengajar pada hari dan kelas yang sama untuk guru ini
            $jadwalJamIds = JadwalKbm::where('guru_id', $validated['guru_id'])
                ->where('kelas_id', $validated['kelas_id'])
                ->where('hari', $hariQuery)
                ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
                ->where('semester_id', $validated['semester_id'])
                ->pluck('jam_belajar_id')
                ->unique();

            $targetJamIds = $jadwalJamIds
                ->push($validated['jam_belajar_id'])
                ->unique()
                ->values();

            $absensiSiswa = $request->input('absensi_siswa', []);
            $keteranganSiswa = $request->input('keterangan_siswa', []);

            $createdAbsensi = [];
            $skippedJamIds = [];
            $firstExistingAbsensi = null;

            foreach ($targetJamIds as $jamId) {
                $existing = AbsensiKelas::where('kelas_id', $validated['kelas_id'])
                    ->where('guru_id', $validated['guru_id'])
                    ->where('jam_belajar_id', $jamId)
                    ->whereDate('tanggal', $validated['tanggal'])
                    ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
                    ->where('semester_id', $validated['semester_id'])
                    ->first();

                if ($existing) {
                    $skippedJamIds[] = $jamId;
                    $firstExistingAbsensi = $firstExistingAbsensi ?: $existing;
                    continue;
                }

                $absensi = AbsensiKelas::create(array_merge($validated, [
                    'jam_belajar_id' => $jamId,
                ]));
                foreach ($absensiSiswa as $siswaId => $status) {
                    if (!empty($status)) {
                        \App\Models\AbsensiSiswa::create([
                            'absensi_kelas_id' => $absensi->id,
                            'siswa_id' => $siswaId,
                            'status' => $status,
                            'keterangan' => $keteranganSiswa[$siswaId] ?? null,
                        ]);
                    }
                }

                // Sync to agenda guru
                $this->syncAbsensiToAgendaGuru($absensi);
                $this->updateAgendaGuruAttendanceNote($absensi);

                $createdAbsensi[] = $absensi;
            }

            DB::commit();

            $jamBelajarLabels = JamBelajar::whereIn('id', $targetJamIds)
                ->orderBy('urutan')
                ->get()
                ->map(function ($jam) {
                    return 'Jam ke-' . $jam->urutan;
                })->implode(', ');

            $skippedLabels = JamBelajar::whereIn('id', $skippedJamIds)
                ->orderBy('urutan')
                ->get()
                ->map(function ($jam) {
                    return 'Jam ke-' . $jam->urutan;
                })->implode(', ');

            $successMessage = !empty($createdAbsensi)
                ? 'Absensi kelas berhasil disimpan untuk ' . count($absensiSiswa) . ' siswa.'
                : 'Absensi sudah tersedia untuk jadwal ini.';
            if ($targetJamIds->count() > 1) {
                $successMessage .= ' Diterapkan ke: ' . $jamBelajarLabels . '.';
            }
            if (!empty($skippedJamIds)) {
                $successMessage .= ' Melewati jam yang sudah terisi: ' . $skippedLabels . '.';
            }

            $redirectAbsensiId = !empty($createdAbsensi)
                ? $createdAbsensi[0]->id
                : ($firstExistingAbsensi->id ?? null);

            if (!$redirectAbsensiId) {
                return redirect()->route('absensi.index')->with('success', $successMessage);
            }

            return redirect()->route('absensi.show', $redirectAbsensiId)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Terjadi kesalahan saat menyimpan absensi: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $absensi = AbsensiKelas::with(['kelas', 'guru', 'jamBelajar', 'tahunAjaran', 'semester', 'absensiSiswa.siswa'])
            ->findOrFail($id);

        return view('absensi.show', compact('absensi'));
    }

    public function edit($id)
    {
        $absensi = AbsensiKelas::findOrFail($id);
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $guruList = Guru::orderBy('nama')->get();
        $jamBelajarList = JamBelajar::orderBy('urutan')->get();
        $tahunAjaranList = TahunAjaran::orderBy('nama_tahun')->get();
        $semesterList = Semester::all();

        return view('absensi.edit', compact('absensi', 'kelasList', 'guruList', 'jamBelajarList', 'tahunAjaranList', 'semesterList'));
    }

    public function update(Request $request, $id)
    {
        $absensi = AbsensiKelas::findOrFail($id);

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:guru,id',
            'jam_belajar_id' => 'required|exists:jam_belajar,id',
            'tanggal' => 'required|date',
            'status_kelas' => 'nullable|string|max:100',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'semester_id' => 'required|exists:semester,id',
        ]);

        $absensi->update($validated);
        
        // Update attendance note in agenda guru
        $this->updateAgendaGuruAttendanceNote($absensi);

        return redirect()->route('absensi.show', $absensi->id)
            ->with('success', 'Absensi kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $absensi = AbsensiKelas::findOrFail($id);
        $absensi->delete();

        return redirect()->route('absensi.index')
            ->with('success', 'Absensi kelas berhasil dihapus.');
    }

    public function getSiswa(Request $request)
    {
        try {
            $kelasId = $request->get('kelas_id');
            
            if (!$kelasId) {
                return response()->json([
                    'siswa' => [],
                    'error' => 'Kelas ID tidak ditemukan'
                ], 400);
            }

            $siswa = \App\Models\Siswa::where('kelas_id', $kelasId)
                ->where('status_aktif', 1)
                ->orderBy('nama')
                ->select('id', 'nis', 'nisn', 'nama', 'jenis_kelamin')
                ->get();

            return response()->json([
                'siswa' => $siswa,
                'count' => $siswa->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'siswa' => [],
                'error' => $e->getMessage(),
                'debug' => config('app.debug') ? $e->getTrace() : null
            ], 500);
        }
    }

    /**
     * Sync attendance to agenda guru
     * Mencatat bahwa guru sudah melakukan absensi di agenda guru
     */
    private function syncAbsensiToAgendaGuru(AbsensiKelas $absensi)
    {
        // Cari atau buat agenda guru dengan kriteria yang sama
        $agendaGuru = AgendaGuru::where('guru_id', $absensi->guru_id)
            ->where('jam_belajar_id', $absensi->jam_belajar_id)
            ->where('tanggal', $absensi->tanggal)
            ->where('tahun_ajaran_id', $absensi->tahun_ajaran_id)
            ->where('semester_id', $absensi->semester_id)
            ->first();

        if (!$agendaGuru) {
            // Create agenda guru if not exists
            $kelas = DB::table('kelas')->find($absensi->kelas_id);
            $kegiatan = $kelas ? $kelas->nama_kelas . ' - Absensi' : 'Absensi';
            
            AgendaGuru::create([
                'guru_id' => $absensi->guru_id,
                'jam_belajar_id' => $absensi->jam_belajar_id,
                'tanggal' => $absensi->tanggal,
                'kegiatan' => $kegiatan,
                'tahun_ajaran_id' => $absensi->tahun_ajaran_id,
                'semester_id' => $absensi->semester_id,
            ]);
        }
    }

    /**
     * Update attendance status in agenda guru
     * Jika ada perubahan status absensi, update catatan di agenda guru
     */
    private function updateAgendaGuruAttendanceNote(AbsensiKelas $absensi)
    {
        $absensiSummary = \App\Models\AbsensiSiswa::where('absensi_kelas_id', $absensi->id)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $notes = [];
        foreach ($absensiSummary as $summary) {
            $notes[] = $summary->status . ': ' . $summary->total;
        }
        
        if (!empty($notes)) {
            $agendaGuru = AgendaGuru::where('guru_id', $absensi->guru_id)
                ->where('jam_belajar_id', $absensi->jam_belajar_id)
                ->where('tanggal', $absensi->tanggal)
                ->where('tahun_ajaran_id', $absensi->tahun_ajaran_id)
                ->where('semester_id', $absensi->semester_id)
                ->first();

            if ($agendaGuru) {
                // Update kegiatan to include attendance summary if not already there
                $kelas = DB::table('kelas')->find($absensi->kelas_id);
                $summary = implode(', ', $notes);
                $kegiatan = $kelas ? $kelas->nama_kelas : '';
                $kegiatan .= ' | Absensi: ' . $summary;
                
                // Only update if summary not already in kegiatan
                if (strpos($agendaGuru->kegiatan, 'Absensi:') === false) {
                    $agendaGuru->kegiatan = $agendaGuru->kegiatan . ' (' . $summary . ')';
                    $agendaGuru->save();
                }
            }
        }
    }
}
