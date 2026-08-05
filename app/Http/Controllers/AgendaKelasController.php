<?php

namespace App\Http\Controllers;

use App\Models\AgendaKelas;
use App\Models\AgendaGuru;
use App\Models\Kelas;
use App\Services\AgendaKelasStorageService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaKelasController extends Controller
{
    private AgendaKelasStorageService $agendaKelasStorageService;

    public function __construct(AgendaKelasStorageService $agendaKelasStorageService)
    {
        $this->agendaKelasStorageService = $agendaKelasStorageService;
    }
    public function index(Request $request)
    {
        $tahun = DB::table('tahun_ajaran')->where('is_active',1)->first();
        $semester = DB::table('semester')->where('is_active',1)->first();

        if (! $tahun || ! $semester) {
            $items = collect();
            $kelasQuickAccess = collect();
            $guruQuickAccess = collect();
            $selectedGuru = null;
            $filterGuruId = null;
            $filterJenisKegiatan = null;
            return view('agenda_kelas.index', compact('items', 'kelasQuickAccess', 'guruQuickAccess', 'selectedGuru', 'filterGuruId', 'filterJenisKegiatan'))
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        // Get guru yang login
        $user = auth()->user();
        $guru = $user->guru;

        // Get guru_id dari query parameter (untuk filter)
        $filterGuruId = $request->get('guru_id');
        $filterJenisKegiatan = $request->get('jenis_kegiatan');
        $selectedGuru = null;

        // Filter agenda
        $query = AgendaKelas::where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id);
        
        // If logged-in user is a student, restrict to their kelas only
        $user = auth()->user();
        $studentKelasId = null;
        if ($user->hasRole('Siswa')) {
            $siswa = $user->siswa;
            if ($siswa && !empty($siswa->kelas_id)) {
                $studentKelasId = $siswa->kelas_id;
                $query->where('kelas_id', $studentKelasId);
                // prepare quick access for student's class
                $kelasQuickAccess = DB::table('kelas')
                    ->where('id', $studentKelasId)
                    ->select('id', 'nama_kelas')
                    ->get();
            } else {
                $query->whereRaw('1 = 0');
                $kelasQuickAccess = collect();
            }
        }

        if ($guru) {
            // Jika ada guru yang login, hanya tampilkan agenda guru tersebut
            $query->where('guru_id', $guru->id);
        } elseif ($filterGuruId) {
            // Jika ada filter guru dari query parameter
            $query->where('guru_id', $filterGuruId);
        }

        if (in_array($filterJenisKegiatan, ['kbm', 'pengembangan_diri'], true)) {
            $query->where('jenis_kegiatan', $filterJenisKegiatan);
        }
        
        $items = $query->orderBy('tanggal','desc')
            ->get();
        
        // Get daftar guru untuk quick access (hanya guru yang mengajar di kelas siswa jika siswa login)
        $guruQuickAccess = DB::table('jadwal_kbm')
            ->join('guru', 'jadwal_kbm.guru_id', '=', 'guru.id')
            ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id ?? 0)
            ->when($studentKelasId, function ($query) use ($studentKelasId) {
                $query->where('jadwal_kbm.kelas_id', $studentKelasId);
            })
            ->select('guru.id', 'guru.nama', 'guru.kode_guru')
            ->distinct()
            ->orderBy('guru.nama')
            ->get();

        // Get kelas untuk guru yang dipilih atau guru yang login
        $activeGuruId = $guru ? $guru->id : ($filterGuruId ?: null);

        if ($activeGuruId) {
            $selectedGuru = DB::table('guru')->where('id', $activeGuruId)->first();
            
            $kelasQuickAccess = DB::table('jadwal_kbm')
                ->join('kelas', 'jadwal_kbm.kelas_id', '=', 'kelas.id')
                ->leftJoin('guru as wali', 'kelas.wali_kelas_id', '=', 'wali.id')
                ->where('jadwal_kbm.guru_id', $activeGuruId)
                ->when($studentKelasId, function ($query) use ($studentKelasId) {
                    $query->where('jadwal_kbm.kelas_id', $studentKelasId);
                })
                ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id ?? 0)
                ->select('kelas.id', 'kelas.nama_kelas', 'wali.nama as wali_nama')
                ->distinct()
                ->orderBy('kelas.nama_kelas')
                ->get();
        } else {
            $kelasQuickAccess = collect();
        }
        
        return view('agenda_kelas.index', compact('items', 'kelasQuickAccess', 'guruQuickAccess', 'selectedGuru', 'filterGuruId', 'filterJenisKegiatan'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $isSiswaOfficer = $user->hasRole('Siswa') && $user->hasClassPosition();

        if ($user->hasRole('Siswa') && ! $user->hasClassPosition()) {
            return redirect()->route('home')->with('error', 'Akses ditolak. Hanya siswa dengan jabatan kelas yang dapat membuat agenda kelas.');
        }

        $guru = $user->guru;
        if (! $guru && ! $isSiswaOfficer) {
            return redirect()->route('agenda_kelas.index')
                ->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        if (!$tahun || !$semester) {
            return redirect()->route('agenda_kelas.index')
                ->with('error', 'Tahun ajaran atau semester belum di-set aktif.');
        }
        
        // Get kelas berdasarkan jadwal mengajar guru yang login
        if ($isSiswaOfficer) {
            $siswa = $user->siswa;
            if (! $siswa || ! $siswa->kelas_id) {
                return redirect()->route('agenda_kelas.index')
                    ->with('error', 'Data siswa atau kelas tidak lengkap.');
            }

            $kelasModel = Kelas::find($siswa->kelas_id);
            if (! $kelasModel) {
                return redirect()->route('agenda_kelas.index')
                    ->with('error', 'Kelas Anda tidak ditemukan.');
            }

            $kelas = collect([$kelasModel]);
            $guru = $kelasModel->waliKelas;
            if (! $guru) {
                $guru = DB::table('guru')->where('id', $kelasModel->wali_kelas_id)->first();
            }
            // Ensure selectedKelasId defaults to student's class when not provided
            $selectedKelasId = $request->get('kelas_id', $kelasModel->id);
            // Get all teachers who have schedule for this class in current year/semester
            $guruList = DB::table('jadwal_kbm')
                ->join('guru', 'jadwal_kbm.guru_id', '=', 'guru.id')
                ->where('jadwal_kbm.kelas_id', $kelasModel->id)
                ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id)
                ->where('jadwal_kbm.semester_id', $semester->id)
                ->select('guru.id', 'guru.nama')
                ->distinct()
                ->orderBy('guru.nama')
                ->get();
        } else {
            $kelas = DB::table('jadwal_kbm')
                ->join('kelas', 'jadwal_kbm.kelas_id', '=', 'kelas.id')
                ->where('jadwal_kbm.guru_id', $guru->id)
                ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id)
                ->where('jadwal_kbm.semester_id', $semester->id)
                ->select('kelas.id', 'kelas.nama_kelas')
                ->distinct()
                ->orderBy('kelas.nama_kelas')
                ->get();
        }

        if (empty($selectedKelasId)) {
            $selectedKelasId = $request->get('kelas_id');
        }

        if (empty($selectedKelasId) && isset($kelas) && $kelas->isNotEmpty()) {
            $selectedKelasId = $kelas->first()->id;
        }

        // Get jadwal aktif untuk dipakai filter jam berdasarkan hari
        if ($isSiswaOfficer) {
            // For students, get all jadwal for the student's class (may include multiple teachers)
            $jadwalItems = DB::table('jadwal_kbm')
                ->join('jam_belajar', 'jadwal_kbm.jam_belajar_id', '=', 'jam_belajar.id')
                ->where('jadwal_kbm.kelas_id', $kelasModel->id)
                ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id)
                ->where('jadwal_kbm.semester_id', $semester->id)
                ->select(
                    'jadwal_kbm.guru_id',
                    'jadwal_kbm.kelas_id',
                    'jadwal_kbm.jam_belajar_id',
                    'jadwal_kbm.hari',
                    'jadwal_kbm.jam_ke',
                    'jam_belajar.urutan',
                    'jam_belajar.jam_mulai',
                    'jam_belajar.jam_selesai',
                    'jam_belajar.jenis'
                )
                ->orderBy('jadwal_kbm.kelas_id')
                ->orderBy('jadwal_kbm.jam_ke')
                ->get();
        } else {
            $jadwalItems = DB::table('jadwal_kbm')
                ->join('jam_belajar', 'jadwal_kbm.jam_belajar_id', '=', 'jam_belajar.id')
                ->where('jadwal_kbm.guru_id', $guru->id)
                ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id)
                ->where('jadwal_kbm.semester_id', $semester->id)
                ->select(
                    'jadwal_kbm.guru_id',
                    'jadwal_kbm.kelas_id',
                    'jadwal_kbm.jam_belajar_id',
                    'jadwal_kbm.hari',
                    'jadwal_kbm.jam_ke',
                    'jam_belajar.urutan',
                    'jam_belajar.jam_mulai',
                    'jam_belajar.jam_selesai',
                    'jam_belajar.jenis'
                )
                ->orderBy('jadwal_kbm.kelas_id')
                ->orderBy('jadwal_kbm.jam_ke')
                ->get();
        }

        $jadwalByKelas = [];
        foreach ($jadwalItems as $item) {
            $kelasId = (string) $item->kelas_id;

            if (!isset($jadwalByKelas[$kelasId])) {
                $jadwalByKelas[$kelasId] = [];
            }

            $jadwalByKelas[$kelasId][] = [
                'guru_id' => (int) ($item->guru_id ?? 0),
                'jam_belajar_id' => (int) $item->jam_belajar_id,
                'hari' => $item->hari,
                'jam_ke' => (int) $item->jam_ke,
                'urutan' => (int) ($item->urutan ?? $item->jam_ke),
                'jam_mulai' => $item->jam_mulai,
                'jam_selesai' => $item->jam_selesai,
                'jenis' => $item->jenis,
                'label' => $item->hari . ' - Jam Ke-' . $item->jam_ke . ' (' . $item->jam_mulai . ' - ' . $item->jam_selesai . ' | ' . $item->jenis . ')',
            ];
        }
        
        // For pengembangan_diri, add all jam_belajar for each hari if not already in jadwalByKelas
        // This allows pengembangan_diri to be created at any time slot
        $allJam = DB::table('jam_belajar')->orderBy('urutan')->get();
        $allHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        
        foreach ($allHari as $hari) {
            foreach ($allJam as $jam) {
                // Check if this jam/hari combination already exists in jadwalByKelas for this class
                $exists = false;
                if (isset($jadwalByKelas[(string)$selectedKelasId])) {
                    foreach ($jadwalByKelas[(string)$selectedKelasId] as $item) {
                        if ($item['hari'] === $hari && $item['jam_belajar_id'] === (int)$jam->id) {
                            $exists = true;
                            break;
                        }
                    }
                }
                
                // If not exists, add it for pengembangan_diri support
                if (!$exists && !empty($selectedKelasId)) {
                    $kelasId = (string)$selectedKelasId;
                    if (!isset($jadwalByKelas[$kelasId])) {
                        $jadwalByKelas[$kelasId] = [];
                    }
                    
                    $jadwalByKelas[$kelasId][] = [
                        'guru_id' => 0,
                        'jam_belajar_id' => (int)$jam->id,
                        'hari' => $hari,
                        'jam_ke' => (int)$jam->urutan,
                        'urutan' => (int)$jam->urutan,
                        'jam_mulai' => $jam->jam_mulai,
                        'jam_selesai' => $jam->jam_selesai,
                        'jenis' => $jam->jenis,
                        'label' => $hari . ' - Jam Ke-' . $jam->urutan . ' (' . $jam->jam_mulai . ' - ' . $jam->jam_selesai . ' | ' . $jam->jenis . ')',
                    ];
                }
            }
        }
        
        // Get all guru for reference (if needed for other features)
        if (!isset($guruList)) {
            $guruList = collect([$guru]);
        }

        // Get kelas_id dari query parameter (dari quick access) if not already set (e.g., student default)
        if (empty($selectedKelasId)) {
            $selectedKelasId = $request->get('kelas_id');
        }
        if (empty($selectedKelasId) && isset($kelas) && $kelas->isNotEmpty()) {
            $selectedKelasId = $kelas->first()->id;
        }

        $selectedDate = $request->get('tanggal', now()->format('Y-m-d'));
        $selectedJamBelajarId = old('jam_belajar_id');
        $selectedJenisKegiatan = old('jenis_kegiatan', $request->get('jenis_kegiatan', 'kbm'));
        $selectedHari = $this->getHariIndonesiaFromDate($selectedDate);
        $selectedGuruId = $request->get('guru_id', $guru->id ?? null);

        $existingTeacherAgenda = null;
        $allowStudentInput = true;
        if ($isSiswaOfficer && $selectedKelasId && $selectedDate) {
            $existingTeacherAgenda = AgendaKelas::where('kelas_id', $selectedKelasId)
                ->whereDate('tanggal', $selectedDate)
                ->where('tahun_ajaran_id', $tahun->id)
                ->where('semester_id', $semester->id)
                ->whereNotNull('guru_id')
                ->where('guru_id', '!=', 0)
                ->orderBy('jam_belajar_id')
                ->first();

            if ($existingTeacherAgenda) {
                $allowStudentInput = false;
                $selectedJenisKegiatan = old('jenis_kegiatan', $existingTeacherAgenda->jenis_kegiatan ?? $selectedJenisKegiatan);
                $selectedKelasId = old('kelas_id', $existingTeacherAgenda->kelas_id ?? $selectedKelasId);
                $selectedGuruId = old('guru_id', $existingTeacherAgenda->guru_id ?? $selectedGuruId);
                $selectedDate = old('tanggal', $existingTeacherAgenda->tanggal ?? $selectedDate);
                $selectedJamBelajarId = old('jam_belajar_id', $existingTeacherAgenda->jam_belajar_id ?? $selectedJamBelajarId);
                $selectedHari = $this->getHariIndonesiaFromDate($selectedDate);
            }
        }

        if (! $isSiswaOfficer && empty(old('tanggal')) && ! $request->has('tanggal') && $selectedKelasId && isset($jadwalByKelas[(string) $selectedKelasId])) {
            $jadwalHariGuru = collect($jadwalByKelas[(string) $selectedKelasId])
                ->when($selectedGuruId, function ($query) use ($selectedGuruId) {
                    return $query->where('guru_id', $selectedGuruId);
                })
                ->sortBy('urutan')
                ->values();

            if ($jadwalHariGuru->isNotEmpty() && ! $jadwalHariGuru->contains('hari', $selectedHari)) {
                $availableDays = $jadwalHariGuru->pluck('hari')->unique()->values()->all();
                $selectedDate = $this->getNearestScheduleDate($selectedDate, $availableDays);
                $selectedHari = $this->getHariIndonesiaFromDate($selectedDate);
            }
        }

        if ($selectedKelasId && empty($selectedJamBelajarId) && isset($jadwalByKelas[(string) $selectedKelasId])) {
            $jadwalHariTerpilih = collect($jadwalByKelas[(string) $selectedKelasId])
                ->where('hari', $selectedHari);

            if (! empty($selectedGuruId)) {
                $jadwalHariTerpilih = $jadwalHariTerpilih->where('guru_id', $selectedGuruId);
            }

            $jadwalHariTerpilih = $jadwalHariTerpilih->sortBy('jam_ke')->values();

            if ($jadwalHariTerpilih->isNotEmpty()) {
                $selectedJamBelajarId = $jadwalHariTerpilih->first()['jam_belajar_id'];
            }
        }
        // Build jamBelajarList for the selected class (use jadwalByKelas if available)
        if (!empty($selectedKelasId) && isset($jadwalByKelas[(string) $selectedKelasId])) {
            $scheduledJamIds = collect($jadwalByKelas[(string) $selectedKelasId])->pluck('jam_belajar_id')->unique()->values()->toArray();
            if (!empty($scheduledJamIds)) {
                $jamBelajarList = DB::table('jam_belajar')
                    ->whereIn('id', $scheduledJamIds)
                    ->orderBy('urutan')
                    ->get();
            } else {
                $jamBelajarList = DB::table('jam_belajar')->orderBy('urutan')->get();
            }
        } else {
            $jamBelajarList = DB::table('jam_belajar')->orderBy('urutan')->get();
        }

        $initialJamOptions = [];
        if (!empty($selectedKelasId) && isset($jadwalByKelas[(string) $selectedKelasId])) {
            $initialJamOptions = collect($jadwalByKelas[(string) $selectedKelasId])
                ->filter(function ($item) use ($selectedHari, $selectedGuruId) {
                    if (trim($item['hari']) !== trim($selectedHari)) {
                        return false;
                    }
                    if (! empty($selectedGuruId) && (string) $item['guru_id'] !== (string) $selectedGuruId) {
                        return false;
                    }
                    return true;
                })
                ->sortBy('jam_ke')
                ->values()
                ->all();
        }

        // Get daftar kegiatan dari database untuk dropdown
        $kegiatanList = DB::table('kegiatan')->orderBy('nama_kegiatan')->get();

        return view('agenda_kelas.create', compact(
            'kelas',
            'guru',
            'guruList',
            'selectedJenisKegiatan',
            'selectedKelasId',
            'selectedDate',
            'selectedHari',
            'selectedJamBelajarId',
            'selectedGuruId',
            'jadwalByKelas',
            'jamBelajarList',
            'initialJamOptions',
            'kegiatanList',
            'existingTeacherAgenda',
            'allowStudentInput',
            'isSiswaOfficer'
        ));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $isSiswaOfficer = $user->hasRole('Siswa') && $user->hasClassPosition();
        $guru = $user->guru;

        if (! $guru && ! $isSiswaOfficer) {
            return back()->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        $agendaId = $request->input('agenda_id');
        if ($agendaId) {
            $agenda = AgendaKelas::findOrFail($agendaId);
            if (! $this->canManageAgenda($agenda, $user)) {
                return back()->with('error', 'Anda tidak memiliki akses untuk menyimpan agenda ini.');
            }

            $data = $request->validate([
                'agenda_id' => 'nullable|integer',
                'jenis_kegiatan' => 'nullable|in:kbm,pengembangan_diri',
                'kelas_id' => 'nullable|integer',
                'guru_id' => 'nullable|integer',
                'jam_belajar_id' => 'nullable|integer',
                'tanggal' => 'nullable|date',
                'kegiatan' => 'nullable|string',
                'nama_kegiatan' => 'nullable|string|max:255',
                'tujuan_pembelajaran' => 'nullable|string',
                'strategi_pembelajaran' => 'nullable|string',
                'media_pembelajaran' => 'nullable|string',
                'sumber_belajar' => 'nullable|string',
                'penilaian' => 'nullable|string',
                'catatan_tambahan' => 'nullable|string',
                'apply_to_all_jam' => 'nullable|boolean',
            ]);

            $data['jenis_kegiatan'] = $data['jenis_kegiatan'] ?? $agenda->jenis_kegiatan;
            $data['kelas_id'] = $data['kelas_id'] ?? $agenda->kelas_id;
            $data['guru_id'] = $data['guru_id'] ?? $agenda->guru_id;
            $data['jam_belajar_id'] = $data['jam_belajar_id'] ?? $agenda->jam_belajar_id;
            $data['tanggal'] = $data['tanggal'] ?? $agenda->tanggal;
        } else {
            $data = $request->validate([
                'agenda_id' => 'nullable|integer',
                'jenis_kegiatan' => 'required|in:kbm,pengembangan_diri',
                'kelas_id' => 'nullable|integer',
                'guru_id' => 'required|integer',
                'jam_belajar_id' => 'nullable|integer',
                'tanggal' => 'required|date',
                'kegiatan' => 'nullable|string',
                'nama_kegiatan' => 'nullable|string|max:255',
                'tujuan_pembelajaran' => 'nullable|string',
                'strategi_pembelajaran' => 'nullable|string',
                'media_pembelajaran' => 'nullable|string',
                'sumber_belajar' => 'nullable|string',
                'penilaian' => 'nullable|string',
                'catatan_tambahan' => 'nullable|string',
                'apply_to_all_jam' => 'nullable|boolean',
            ]);
        }

        if ($data['jenis_kegiatan'] === 'kbm') {
            if (empty($data['kelas_id']) || empty($data['jam_belajar_id'])) {
                return back()->withInput()->withErrors('Untuk kegiatan KBM, kelas dan jam KBM wajib dipilih.');
            }
        } else {
            if (empty(trim((string) ($data['nama_kegiatan'] ?? '')))) {
                return back()->withInput()->withErrors('Nama kegiatan wajib diisi untuk Pengembangan Diri.');
            }
            if (empty(trim((string) ($data['kegiatan'] ?? '')))) {
                return back()->withInput()->withErrors('Uraian kegiatan wajib diisi untuk Pengembangan Diri.');
            }
            // For pengembangan_diri, keep jam_belajar_id but set kelas_id to null (general activity)
            $data['kelas_id'] = null;
            $data['apply_to_all_jam'] = false;
        }

        if ($isSiswaOfficer) {
            $siswa = $user->siswa;
            if (! $siswa || empty($siswa->kelas_id)) {
                return back()->with('error', 'Data siswa atau kelas tidak lengkap.');
            }

            if ($data['jenis_kegiatan'] === 'kbm') {
                if ((int) ($data['kelas_id'] ?? 0) !== (int) $siswa->kelas_id) {
                    return back()->withInput()->withErrors('Anda hanya dapat membuat agenda untuk kelas Anda.');
                }
            } else {
                $data['kelas_id'] = $siswa->kelas_id;
            }

            $kelasModel = Kelas::find($siswa->kelas_id);
            if ($kelasModel && empty($data['guru_id']) && $kelasModel->wali_kelas_id) {
                $data['guru_id'] = $kelasModel->wali_kelas_id;
            }
        }

        if ($this->isPastDate($data['tanggal']) && ! $this->canEditPastAgenda($user)) {
            return back()->withInput()->withErrors('Akses ditolak. Agenda tanggal lampau hanya dapat disimpan oleh Admin, Wali Kelas, atau Guru BK.');
        }

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();

        if (! $tahun || ! $semester) {
            return back()->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $hariAgenda = $this->getHariIndonesiaFromDate($data['tanggal']);

        if ($isSiswaOfficer) {
            $existingTeacherAgenda = AgendaKelas::where('kelas_id', $data['kelas_id'])
                ->whereDate('tanggal', $data['tanggal'])
                ->where('tahun_ajaran_id', $tahun->id)
                ->where('semester_id', $semester->id)
                ->whereNotNull('guru_id')
                ->where('guru_id', '!=', 0)
                ->first();

            if ($existingTeacherAgenda && (! $agendaId || (int) $existingTeacherAgenda->id !== (int) $agendaId)) {
                return back()->withInput()->withErrors('Guru sudah mengisi agenda kelas untuk tanggal ini. Anda tidak dapat mengubah data yang sudah dibuat guru.');
            }
        }

        // Hapus agenda_id dari data jika ada (jika dari form show)
        $agendaId = $data['agenda_id'] ?? null;
        unset($data['agenda_id']);
        
        // Ambil flag apply_to_all_jam
        $applyToAllJam = $data['apply_to_all_jam'] ?? false;
        unset($data['apply_to_all_jam']);

        if ($data['jenis_kegiatan'] === 'kbm' && ! $isSiswaOfficer) {
            $hasSchedule = DB::table('jadwal_kbm')
                ->where('guru_id', $guru->id)
                ->where('kelas_id', $data['kelas_id'])
                ->where('jam_belajar_id', $data['jam_belajar_id'])
                ->where('hari', $hariAgenda)
                ->where('tahun_ajaran_id', $tahun->id)
                ->where('semester_id', $semester->id)
                ->exists();

            if (!$hasSchedule) {
                return back()->withInput()->withErrors('Anda tidak memiliki jadwal mengajar untuk kelas dan jam KBM yang dipilih.');
            }
        }

        if (! $isSiswaOfficer && $data['guru_id'] != $guru->id) {
            return back()->withErrors('Guru yang dipilih tidak sesuai.');
        }

        $data['tahun_ajaran_id'] = $tahun->id;
        $data['semester_id'] = $semester->id;

        if ($agendaId && ! $applyToAllJam && $this->hasDuplicateAgendaKelas($data, $agendaId)) {
            return back()->withInput()->withErrors('Agenda kelas untuk guru, kelas, jam, tanggal, dan semester yang sama sudah ada.');
        }

        if ($data['jenis_kegiatan'] === 'kbm' && $applyToAllJam) {
            // Determine effective guru id: prefer submitted guru_id, fallback to logged-in guru (if any)
            $effectiveGuruId = $data['guru_id'] ?? ($guru->id ?? null);
            // Cari semua jam KBM untuk kelas yang sama — if effectiveGuruId is present, limit to that guru
            $allJamQuery = DB::table('jadwal_kbm')
                ->where('kelas_id', $data['kelas_id'])
                ->where('hari', $hariAgenda)
                ->where('tahun_ajaran_id', $tahun->id)
                ->where('semester_id', $semester->id);

            if (!empty($effectiveGuruId)) {
                $allJamQuery->where('guru_id', $effectiveGuruId);
            }

            $allJamForKelas = $allJamQuery->pluck('jam_belajar_id')->toArray();

            // Buat agenda untuk semua jam KBM
            $createdCount = 0;
            foreach ($allJamForKelas as $jamId) {
                $agendaData = $data;
                $agendaData['jam_belajar_id'] = $jamId;

                // Cek apakah sudah ada agenda untuk jam ini pada tanggal yang sama
                $existingAgenda = AgendaKelas::where('kelas_id', $data['kelas_id'])
                    ->where('guru_id', $guru->id)
                    ->where('jam_belajar_id', $jamId)
                    ->where('tanggal', $data['tanggal'])
                    ->where('tahun_ajaran_id', $tahun->id)
                    ->where('semester_id', $semester->id)
                    ->first();

                if ($existingAgenda) {
                    // Update existing
                    $existingAgenda->update($agendaData);
                    $this->agendaKelasStorageService->syncAgendaGuru($existingAgenda);
                } else {
                    // Create new
                    $newAgenda = AgendaKelas::create($agendaData);
                    $this->agendaKelasStorageService->syncAgendaGuru($newAgenda);
                }
                $createdCount++;
            }
            
            $message = "Agenda kelas berhasil disimpan untuk $createdCount jam KBM";
        } else {
            // Create single agenda
            if ($agendaId) {
                // Update existing agenda
                $agenda = AgendaKelas::findOrFail($agendaId);
                $agenda->update($data);
                $this->agendaKelasStorageService->syncAgendaGuru($agenda);
                $message = 'Agenda kelas berhasil diperbarui';
            } else {
                if ($this->hasDuplicateAgendaKelas($data)) {
                    return back()->withInput()->withErrors('Agenda kelas untuk guru, kelas, jam, tanggal, dan semester yang sama sudah ada.');
                }

                // Create new agenda
                $agenda = AgendaKelas::create($data);
                $this->agendaKelasStorageService->syncAgendaGuru($agenda);
                $message = $agenda->jenis_kegiatan === 'pengembangan_diri'
                    ? 'Kegiatan pengembangan diri berhasil ditambahkan'
                    : 'Agenda kelas ditambahkan';
            }
        }

        return redirect()->route('agenda_kelas.index')->with('success', $message);
    }

    public function show($id)
    {
        $agenda = AgendaKelas::findOrFail($id);
        
        $user = auth()->user();
        $guruLogin = $user->guru;
        $isPrivilegedViewer = $user->hasAnyRole(['Admin', 'Kepala Sekolah', 'Wakil Kepala Sekolah']);
        
        if (!$isPrivilegedViewer) {
            // Allow students to view agenda for their own class
            if ($user->hasRole('Siswa')) {
                $siswa = $user->siswa;
                if (! $siswa || (int) $agenda->kelas_id !== (int) ($siswa->kelas_id ?? 0)) {
                    return redirect()->route('agenda_kelas.index')
                        ->with('error', 'Anda tidak memiliki akses untuk agenda ini.');
                }
            } else {
                if (!$guruLogin || (int) $agenda->guru_id !== (int) $guruLogin->id) {
                    return redirect()->route('agenda_kelas.index')
                        ->with('error', 'Anda tidak memiliki akses untuk agenda ini.');
                }
            }
        }

        $guru = DB::table('guru')->find($agenda->guru_id) ?: $guruLogin;

        if (!$guru) {
            return redirect()->route('agenda_kelas.index')
                ->with('error', 'Data guru untuk agenda ini tidak ditemukan.');
        }

        $kelas = DB::table('kelas')->find($agenda->kelas_id);
        $jamBelajar = DB::table('jam_belajar')->find($agenda->jam_belajar_id);

        return view('agenda_kelas.show', compact('agenda', 'kelas', 'jamBelajar', 'guru'));
    }

    public function edit($id)
    {
        $agenda = AgendaKelas::findOrFail($id);
        $user = auth()->user();
        $isSiswaOfficer = $user->hasRole('Siswa') && $user->hasClassPosition();

        if ($user->hasRole('Siswa') && ! $isSiswaOfficer) {
            return redirect()->route('home')->with('error', 'Akses ditolak. Hanya siswa dengan jabatan kelas yang dapat mengedit agenda kelas.');
        }

        if (! $this->canManageAgenda($agenda, $user)) {
            return redirect()->route('agenda_kelas.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit agenda ini.');
        }

        if ($isSiswaOfficer) {
            $siswa = $user->siswa;
            if (! $siswa || ! $siswa->kelas_id) {
                return redirect()->route('agenda_kelas.index')
                    ->with('error', 'Data siswa atau kelas tidak lengkap.');
            }

            $kelasModel = Kelas::find($siswa->kelas_id);
            if (! $kelasModel) {
                return redirect()->route('agenda_kelas.index')
                    ->with('error', 'Kelas Anda tidak ditemukan.');
            }

            $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
            $semester = DB::table('semester')->where('is_active', 1)->first();
            if (! $tahun || ! $semester) {
                return redirect()->route('agenda_kelas.index')
                    ->with('error', 'Tahun ajaran atau semester belum di-set aktif.');
            }

            $kelas = collect([$kelasModel]);
            $guru = $kelasModel->waliKelas;
            if (! $guru) {
                $guru = DB::table('guru')->where('id', $kelasModel->wali_kelas_id)->first();
            }

            $selectedKelasId = $agenda->kelas_id;
            $selectedGuruId = $agenda->guru_id;
            $selectedDate = $agenda->tanggal;
            $selectedJenisKegiatan = $agenda->jenis_kegiatan ?? 'kbm';
            $selectedJamBelajarId = $agenda->jam_belajar_id;
            $selectedHari = $this->getHariIndonesiaFromDate($selectedDate);

            $guruList = DB::table('jadwal_kbm')
                ->join('guru', 'jadwal_kbm.guru_id', '=', 'guru.id')
                ->where('jadwal_kbm.kelas_id', $kelasModel->id)
                ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id)
                ->where('jadwal_kbm.semester_id', $semester->id)
                ->select('guru.id', 'guru.nama')
                ->distinct()
                ->orderBy('guru.nama')
                ->get();

            $jadwalItems = DB::table('jadwal_kbm')
                ->join('jam_belajar', 'jadwal_kbm.jam_belajar_id', '=', 'jam_belajar.id')
                ->where('jadwal_kbm.kelas_id', $kelasModel->id)
                ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id)
                ->where('jadwal_kbm.semester_id', $semester->id)
                ->select(
                    'jadwal_kbm.guru_id',
                    'jadwal_kbm.kelas_id',
                    'jadwal_kbm.jam_belajar_id',
                    'jadwal_kbm.hari',
                    'jadwal_kbm.jam_ke',
                    'jam_belajar.urutan',
                    'jam_belajar.jam_mulai',
                    'jam_belajar.jam_selesai',
                    'jam_belajar.jenis'
                )
                ->orderBy('jadwal_kbm.kelas_id')
                ->orderBy('jadwal_kbm.jam_ke')
                ->get();

            $jadwalByKelas = [];
            foreach ($jadwalItems as $item) {
                $kelasId = (string) $item->kelas_id;

                if (!isset($jadwalByKelas[$kelasId])) {
                    $jadwalByKelas[$kelasId] = [];
                }

                $jadwalByKelas[$kelasId][] = [
                    'guru_id' => (int) ($item->guru_id ?? 0),
                    'jam_belajar_id' => (int) $item->jam_belajar_id,
                    'hari' => $item->hari,
                    'jam_ke' => (int) $item->jam_ke,
                    'urutan' => (int) ($item->urutan ?? $item->jam_ke),
                    'jam_mulai' => $item->jam_mulai,
                    'jam_selesai' => $item->jam_selesai,
                    'jenis' => $item->jenis,
                    'label' => $item->hari . ' - Jam Ke-' . $item->jam_ke . ' (' . $item->jam_mulai . ' - ' . $item->jam_selesai . ' | ' . $item->jenis . ')',
                ];
            }

            $jamBelajarList = DB::table('jam_belajar')->orderBy('urutan')->get();

            $initialJamOptions = [];
            if (!empty($selectedKelasId) && isset($jadwalByKelas[(string) $selectedKelasId])) {
                $initialJamOptions = collect($jadwalByKelas[(string) $selectedKelasId])
                    ->filter(function ($item) use ($selectedHari, $selectedGuruId) {
                        if (trim($item['hari']) !== trim($selectedHari)) {
                            return false;
                        }
                        if (! empty($selectedGuruId) && (string) $item['guru_id'] !== (string) $selectedGuruId) {
                            return false;
                        }
                        return true;
                    })
                    ->sortBy('jam_ke')
                    ->values()
                    ->all();
            }

            return view('agenda_kelas.create', compact(
                'kelas',
                'guru',
                'guruList',
                'selectedJenisKegiatan',
                'selectedKelasId',
                'selectedDate',
                'selectedHari',
                'selectedJamBelajarId',
                'selectedGuruId',
                'jadwalByKelas',
                'jamBelajarList',
                'initialJamOptions',
                'agenda'
            ));
        }

        $guru = $user->guru;
        if (! $guru) {
            return redirect()->route('agenda_kelas.index')
                ->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        $tahun = DB::table('tahun_ajaran')->where('is_active', 1)->first();
        $semester = DB::table('semester')->where('is_active', 1)->first();
        if (! $tahun || ! $semester) {
            return redirect()->route('agenda_kelas.index')
                ->with('error', 'Tahun ajaran atau semester belum di-set aktif.');
        }

        $kelas = DB::table('jadwal_kbm')
            ->join('kelas', 'jadwal_kbm.kelas_id', '=', 'kelas.id')
            ->where('jadwal_kbm.guru_id', $guru->id)
            ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id)
            ->where('jadwal_kbm.semester_id', $semester->id)
            ->select('kelas.id', 'kelas.nama_kelas')
            ->distinct()
            ->orderBy('kelas.nama_kelas')
            ->get();

        $selectedKelasId = old('kelas_id', $agenda->kelas_id ?? $request->get('kelas_id') ?? ($kelas->first()->id ?? null));
        $selectedGuruId = old('guru_id', $agenda->guru_id ?? $guru->id);
        $selectedDate = old('tanggal', $agenda->tanggal ?? now()->format('Y-m-d'));
        $selectedJenisKegiatan = old('jenis_kegiatan', $agenda->jenis_kegiatan ?? 'kbm');
        $selectedJamBelajarId = old('jam_belajar_id', $agenda->jam_belajar_id ?? null);
        $selectedHari = $this->getHariIndonesiaFromDate($selectedDate);

        $jadwalItems = DB::table('jadwal_kbm')
            ->join('jam_belajar', 'jadwal_kbm.jam_belajar_id', '=', 'jam_belajar.id')
            ->where('jadwal_kbm.guru_id', $guru->id)
            ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id)
            ->where('jadwal_kbm.semester_id', $semester->id)
            ->select(
                'jadwal_kbm.guru_id',
                'jadwal_kbm.kelas_id',
                'jadwal_kbm.jam_belajar_id',
                'jadwal_kbm.hari',
                'jadwal_kbm.jam_ke',
                'jam_belajar.urutan',
                'jam_belajar.jam_mulai',
                'jam_belajar.jam_selesai',
                'jam_belajar.jenis'
            )
            ->orderBy('jadwal_kbm.kelas_id')
            ->orderBy('jadwal_kbm.jam_ke')
            ->get();

        $jadwalByKelas = [];
        foreach ($jadwalItems as $item) {
            $kelasId = (string) $item->kelas_id;

            if (!isset($jadwalByKelas[$kelasId])) {
                $jadwalByKelas[$kelasId] = [];
            }

            $jadwalByKelas[$kelasId][] = [
                'guru_id' => (int) ($item->guru_id ?? 0),
                'jam_belajar_id' => (int) $item->jam_belajar_id,
                'hari' => $item->hari,
                'jam_ke' => (int) $item->jam_ke,
                'urutan' => (int) ($item->urutan ?? $item->jam_ke),
                'jam_mulai' => $item->jam_mulai,
                'jam_selesai' => $item->jam_selesai,
                'jenis' => $item->jenis,
                'label' => $item->hari . ' - Jam Ke-' . $item->jam_ke . ' (' . $item->jam_mulai . ' - ' . $item->jam_selesai . ' | ' . $item->jenis . ')',
            ];
        }

        $jamBelajarList = DB::table('jam_belajar')->orderBy('urutan')->get();

        $initialJamOptions = [];
        if (!empty($selectedKelasId) && isset($jadwalByKelas[(string) $selectedKelasId])) {
            $initialJamOptions = collect($jadwalByKelas[(string) $selectedKelasId])
                ->filter(function ($item) use ($selectedHari, $selectedGuruId) {
                    if (trim($item['hari']) !== trim($selectedHari)) {
                        return false;
                    }
                    if (! empty($selectedGuruId) && (string) $item['guru_id'] !== (string) $selectedGuruId) {
                        return false;
                    }
                    return true;
                })
                ->sortBy('jam_ke')
                ->values()
                ->all();
        }

        $guruList = collect([$guru]);

        return view('agenda_kelas.create', compact(
            'kelas',
            'guru',
            'guruList',
            'selectedJenisKegiatan',
            'selectedKelasId',
            'selectedDate',
            'selectedHari',
            'selectedJamBelajarId',
            'selectedGuruId',
            'jadwalByKelas',
            'jamBelajarList',
            'initialJamOptions',
            'agenda'
        ));

        return view('agenda_kelas.show', compact('agenda', 'kelas', 'jamBelajar', 'guru'));
    }

    public function update(Request $request, $id)
    {
        $agenda = AgendaKelas::findOrFail($id);
        $user = auth()->user();
        $isSiswaOfficer = $user->hasRole('Siswa') && $user->hasClassPosition();

        if ($user->hasRole('Siswa') && ! $isSiswaOfficer) {
            return back()->with('error', 'Akses ditolak. Hanya siswa dengan jabatan kelas yang dapat mengupdate agenda kelas.');
        }

        if (! $this->canManageAgenda($agenda, $user)) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengupdate agenda ini.');
        }

        $data = $request->validate([
            'kelas_id' => 'required|integer',
            'guru_id' => 'required|integer',
            'jam_belajar_id' => 'required|integer',
            'tanggal' => 'required|date',
            'kegiatan' => 'nullable|string',
            'tujuan_pembelajaran' => 'nullable|string',
            'strategi_pembelajaran' => 'nullable|string',
            'media_pembelajaran' => 'nullable|string',
            'sumber_belajar' => 'nullable|string',
            'penilaian' => 'nullable|string',
            'catatan_tambahan' => 'nullable|string',
        ]);

        if ($isSiswaOfficer) {
            $siswa = $user->siswa;
            if (! $siswa || empty($siswa->kelas_id) || $data['kelas_id'] != $siswa->kelas_id) {
                return back()->withErrors('Anda hanya dapat mengupdate agenda untuk kelas Anda.');
            }
        }

        $guru = $user->guru;
        if (! $isSiswaOfficer) {
            if (! $guru) {
                return back()->with('error', 'Aksi ini hanya dapat dilakukan oleh akun guru.');
            }

            $hariAgenda = $this->getHariIndonesiaFromDate($data['tanggal']);
            $hasSchedule = DB::table('jadwal_kbm')
                ->where('guru_id', $guru->id)
                ->where('kelas_id', $data['kelas_id'])
                ->where('jam_belajar_id', $data['jam_belajar_id'])
                ->where('hari', $hariAgenda)
                ->where('tahun_ajaran_id', $agenda->tahun_ajaran_id)
                ->where('semester_id', $agenda->semester_id)
                ->exists();

            if (!$hasSchedule) {
                return back()->withErrors('Anda tidak memiliki jadwal mengajar untuk kelas dan jam KBM yang dipilih.');
            }

            if ($data['guru_id'] != $guru->id) {
                return back()->withErrors('Guru yang dipilih tidak sesuai.');
            }
        }

        if ($isSiswaOfficer && empty($data['guru_id'])) {
            $kelasModel = Kelas::find($user->siswa->kelas_id);
            if ($kelasModel && $kelasModel->wali_kelas_id) {
                $data['guru_id'] = $kelasModel->wali_kelas_id;
            }
        }

        if ($this->isPastDate($agenda->tanggal) && ! $this->canEditPastAgenda($user)) {
            return back()->with('error', 'Akses ditolak. Agenda tanggal lampau hanya dapat diubah oleh Admin, Wali Kelas, atau Guru BK.');
        }

        if ($this->isPastDate($data['tanggal']) && ! $this->canEditPastAgenda($user)) {
            return back()->with('error', 'Akses ditolak. Agenda tanggal lampau hanya dapat diubah oleh Admin, Wali Kelas, atau Guru BK.');
        }

        if ($this->hasDuplicateAgendaKelas($data, $agenda->id)) {
            return back()->withInput()->withErrors('Agenda kelas untuk guru, kelas, jam, tanggal, dan semester yang sama sudah ada.');
        }

        $agenda->update($data);
        $this->agendaKelasStorageService->syncAgendaGuru($agenda);

        return redirect()->route('agenda_kelas.index')->with('success', 'Agenda kelas berhasil diperbarui');
    }

    public function destroy($id)
    {
        $agenda = AgendaKelas::findOrFail($id);
        $user = auth()->user();
        $isSiswaOfficer = $user->hasRole('Siswa') && $user->hasClassPosition();

        if ($user->hasRole('Siswa') && ! $isSiswaOfficer) {
            return back()->with('error', 'Akses ditolak. Hanya siswa dengan jabatan kelas yang dapat menghapus agenda kelas.');
        }

        if ($this->isPastDate($agenda->tanggal) && ! $this->canEditPastAgenda($user)) {
            return back()->with('error', 'Akses ditolak. Agenda tanggal lampau hanya dapat dihapus oleh Admin, Wali Kelas, atau Guru BK.');
        }

        if (! $this->canManageAgenda($agenda, $user)) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus agenda ini.');
        }

        $this->agendaKelasStorageService->cleanupAgendaGuru($agenda);
        $agenda->delete();

        return redirect()->route('agenda_kelas.index')->with('success', 'Agenda kelas berhasil dihapus');
    }

    private function hasDuplicateAgendaKelas(array $data, int $excludeAgendaId = null): bool
    {
        $query = AgendaKelas::where('guru_id', $data['guru_id'])
            ->where('kelas_id', $data['kelas_id'])
            ->where('jam_belajar_id', $data['jam_belajar_id'])
            ->where('tanggal', $data['tanggal'])
            ->where('tahun_ajaran_id', $data['tahun_ajaran_id'])
            ->where('semester_id', $data['semester_id']);

        if ($excludeAgendaId) {
            $query->where('id', '<>', $excludeAgendaId);
        }

        return $query->exists();
    }

    private function canManageAgenda(AgendaKelas $agenda, $user): bool
    {
        if ($user->hasRole('Siswa') && $user->hasClassPosition()) {
            $siswa = $user->siswa;
            return $siswa && ! empty($siswa->kelas_id) && (int) $agenda->kelas_id === (int) $siswa->kelas_id;
        }

        if ($user->guru) {
            return (int) $agenda->guru_id === (int) $user->guru->id;
        }

        return false;
    }

    private function canEditPastAgenda($user): bool
    {
        return $user && (
            $user->hasAnyRole(['Admin', 'Kepala Sekolah']) ||
            $user->hasRole('Wali Kelas') ||
            $user->hasRole('Guru BK')
        );
    }

    private function isPastDate($date): bool
    {
        return Carbon::parse($date)->startOfDay()->lt(Carbon::today());
    }

    public function preview(Request $request)
    {
        $kelasId = $request->get('kelas_id');
        $guruId = $request->get('guru_id'); // Get guru_id dari query parameter
        $user = auth()->user();
        $guruLogin = $user->guru;
        
        // Tentukan guru_id yang akan digunakan
        // Prioritas: guru yang login -> guru_id dari parameter
        $activeGuruId = $guruLogin ? $guruLogin->id : $guruId;
        
        if (!$activeGuruId) {
            abort(403, 'Guru tidak ditemukan. Silakan pilih guru terlebih dahulu.');
        }
        
        $tahunAjaran = DB::table('tahun_ajaran')->where('is_active',1)->first();
        $semester = DB::table('semester')->where('is_active',1)->first();

        if (!$tahunAjaran || !$semester) {
            abort(404, 'Tahun ajaran atau semester tidak ditemukan');
        }

        // Get data guru
        $guru = DB::table('guru')->find($activeGuruId);
        
        if (!$guru) {
            abort(404, 'Data guru tidak ditemukan');
        }

        // Get all agenda untuk kelas dan guru ini
        $agendas = AgendaKelas::with(['kelas', 'guru', 'jamBelajar'])
            ->where('kelas_id', $kelasId)
            ->where('guru_id', $activeGuruId)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->where('semester_id', $semester->id)
            ->orderBy('tanggal', 'desc')
            ->get();

        $kelas = DB::table('kelas')->find($kelasId);

        if (!$kelas) {
            abort(404, 'Kelas tidak ditemukan');
        }

        // Get wali kelas dari tabel guru dengan wali_kelas_id dari kelas
        $waliKelas = DB::table('guru')
            ->where('id', $kelas->wali_kelas_id)
            ->first();
        
        // Get kepala sekolah dari tabel kepala_sekolah dengan status Aktif
        $kepalaSekolah = DB::table('kepala_sekolah')
            ->where('status', 'Aktif')
            ->orderBy('created_at', 'desc')
            ->first();
        
        // Jika tidak ada kepala sekolah aktif, ambil yang terbaru
        if (!$kepalaSekolah) {
            $kepalaSekolah = DB::table('kepala_sekolah')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        // Get data sekolah
        $sekolah = DB::table('sekolah')->first();

        $pdf = \PDF::loadView('agenda_kelas.preview_pdf', compact('agendas', 'kelas', 'guru', 'tahunAjaran', 'semester', 'waliKelas', 'kepalaSekolah', 'sekolah'));
        $pdf->setPaper('a4', 'portrait');
        
        return $pdf->stream('Preview-Agenda-' . str_replace(' ', '-', $kelas->nama_kelas) . '.pdf');
    }

    /**
     * Sync agenda kelas ke agenda guru
     * Ketika guru membuat/mengubah agenda kelas, otomatis terupdate di agenda guru
     */
    private function getNearestScheduleDate($currentDate, array $availableDays)
    {
        $dayOrder = [
            'Minggu' => 0,
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            'Sabtu' => 6,
        ];

        $current = Carbon::parse($currentDate);
        $currentIndex = $current->dayOfWeek;
        $bestDate = null;
        $bestDiff = null;

        foreach ($availableDays as $hari) {
            if (! isset($dayOrder[$hari])) {
                continue;
            }
            $targetIndex = $dayOrder[$hari];
            $diff = ($targetIndex - $currentIndex + 7) % 7;
            if ($diff === 0) {
                $diff = 7;
            }
            if ($bestDiff === null || $diff < $bestDiff) {
                $bestDiff = $diff;
                $bestDate = $current->copy()->addDays($diff);
            }
        }

        return $bestDate ? $bestDate->format('Y-m-d') : $currentDate;
    }

    private function getHariIndonesiaFromDate($date)
    {
        $hariIndonesia = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];

        $hariEnglish = Carbon::parse($date)->format('l');

        return $hariIndonesia[$hariEnglish] ?? $hariEnglish;
    }

    /**
     * Remove agenda guru jika semua agenda kelas untuk jam tersebut sudah dihapus
     */
}
