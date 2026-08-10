<?php

namespace App\Http\Controllers;

use App\Models\Supervisi;
use App\Models\JadwalKbm;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\Semester;
use Illuminate\Http\Request;
use Carbon\Carbon;

class SupervisiController extends Controller
{
    protected array $allowedRoles = ['Admin', 'Kepala Sekolah', 'Pengawas Pembina'];

    protected function authorizeAccess()
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole($this->allowedRoles)) {
            return redirect()->route('home')->with('error', 'Akses ditolak.');
        }

        return null;
    }

    protected function getActiveTahunSemester(): array
    {
        return [
            TahunAjaran::where('is_active', true)->first(),
            Semester::where('is_active', true)->first(),
        ];
    }

    protected function mapEnglishDayToIndo(string $englishDay): string
    {
        return match ($englishDay) {
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
            default => $englishDay,
        };
    }

    protected function getActiveJadwalQuery()
    {
        [$tahunAjaranAktif, $semesterAktif] = $this->getActiveTahunSemester();

        $query = JadwalKbm::query();
        if ($tahunAjaranAktif) {
            $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
        }
        if ($semesterAktif) {
            $query->where('semester_id', $semesterAktif->id);
        }

        return $query;
    }

    public function index()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $items = Supervisi::with(['guru.user', 'mataPelajaran', 'kelas'])
            ->orderByDesc('tanggal')
            ->orderBy('jam_ke')
            ->get();

        return view('akademik.supervisi.index', compact('items'));
    }

    public function create()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $guruList = Guru::orderBy('nama')->get();

        return view('akademik.supervisi.create', compact('guruList'));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'tanggal' => 'required|date',
            'jadwal_kbm_id' => 'required|exists:jadwal_kbm,id',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $jadwal = JadwalKbm::with(['mataPelajaran', 'kelas'])->findOrFail($validated['jadwal_kbm_id']);

        if ($jadwal->guru_id != $validated['guru_id']) {
            return back()->withInput()->withErrors(['jadwal_kbm_id' => 'Jadwal KBM yang dipilih tidak cocok dengan guru.']);
        }

        $hariIndo = $this->mapEnglishDayToIndo(Carbon::parse($validated['tanggal'])->format('l'));
        if ($jadwal->hari !== $hariIndo) {
            return back()->withInput()->withErrors(['tanggal' => 'Tanggal supervisi harus sesuai dengan hari guru memiliki jadwal KBM.']);
        }

        Supervisi::create([
            'guru_id' => $validated['guru_id'],
            'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
            'kelas_id' => $jadwal->kelas_id,
            'jadwal_kbm_id' => $jadwal->id,
            'tanggal' => $validated['tanggal'],
            'jam_ke' => $jadwal->jam_ke,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return redirect()->route('akademik.supervisi')->with('success', 'Jadwal supervisi berhasil ditambahkan.');
    }

    public function show(Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $supervisi->load(['guru.user', 'mataPelajaran', 'kelas', 'jadwalKbm.jamBelajar']);

        return view('akademik.supervisi.show', compact('supervisi'));
    }

    public function edit(Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $guruList = Guru::orderBy('nama')->get();
        $supervisi->load(['jadwalKbm.jamBelajar']);

        $scheduleOptions = JadwalKbm::with(['kelas', 'mataPelajaran', 'jamBelajar'])
            ->where('guru_id', $supervisi->guru_id)
            ->where('hari', $supervisi->jadwalKbm?->hari ?? '')
            ->where(function ($query) {
                [$tahunAjaranAktif, $semesterAktif] = $this->getActiveTahunSemester();
                if ($tahunAjaranAktif) {
                    $query->where('tahun_ajaran_id', $tahunAjaranAktif->id);
                }
                if ($semesterAktif) {
                    $query->where('semester_id', $semesterAktif->id);
                }
            })
            ->orderBy('jam_ke')
            ->get();

        return view('akademik.supervisi.edit', compact('guruList', 'supervisi', 'scheduleOptions'));
    }

    public function update(Request $request, Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'guru_id' => 'required|exists:guru,id',
            'tanggal' => 'required|date',
            'jadwal_kbm_id' => 'required|exists:jadwal_kbm,id',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $jadwal = JadwalKbm::with(['mataPelajaran', 'kelas'])->findOrFail($validated['jadwal_kbm_id']);

        if ($jadwal->guru_id != $validated['guru_id']) {
            return back()->withInput()->withErrors(['jadwal_kbm_id' => 'Jadwal KBM yang dipilih tidak cocok dengan guru.']);
        }

        $hariIndo = $this->mapEnglishDayToIndo(Carbon::parse($validated['tanggal'])->format('l'));
        if ($jadwal->hari !== $hariIndo) {
            return back()->withInput()->withErrors(['tanggal' => 'Tanggal supervisi harus sesuai dengan hari guru memiliki jadwal KBM.']);
        }

        $supervisi->update([
            'guru_id' => $validated['guru_id'],
            'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
            'kelas_id' => $jadwal->kelas_id,
            'jadwal_kbm_id' => $jadwal->id,
            'tanggal' => $validated['tanggal'],
            'jam_ke' => $jadwal->jam_ke,
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        return redirect()->route('akademik.supervisi')->with('success', 'Jadwal supervisi berhasil diperbarui.');
    }

    public function destroy(Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $supervisi->delete();

        return redirect()->route('akademik.supervisi')->with('success', 'Jadwal supervisi berhasil dihapus.');
    }

    public function getAvailableDates($guruId)
    {
        if ($redirect = $this->authorizeAccess()) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $jadwalRows = $this->getActiveJadwalQuery()
            ->where('guru_id', $guruId)
            ->pluck('hari')
            ->unique()
            ->toArray();

        if (empty($jadwalRows)) {
            return response()->json(['dates' => [], 'hari' => []]);
        }

        $dayMap = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu',
        ];

        $dates = [];
        $cursor = Carbon::today();

        while (count($dates) < 30) {
            $hari = $dayMap[$cursor->format('l')] ?? null;
            if ($hari && in_array($hari, $jadwalRows, true)) {
                $dates[] = $cursor->toDateString();
            }
            $cursor->addDay();
        }

        return response()->json(['dates' => $dates, 'hari' => array_values($jadwalRows)]);
    }

    public function getJadwalOptions($guruId, $tanggal)
    {
        if ($redirect = $this->authorizeAccess()) {
            return response()->json(['error' => 'Akses ditolak.'], 403);
        }

        $carbon = Carbon::parse($tanggal);
        $hariIndo = $this->mapEnglishDayToIndo($carbon->format('l'));

        $jadwalOptions = $this->getActiveJadwalQuery()
            ->where('guru_id', $guruId)
            ->where('hari', $hariIndo)
            ->with(['kelas', 'mataPelajaran', 'jamBelajar'])
            ->orderBy('jam_ke')
            ->get()
            ->map(function (JadwalKbm $jadwal) {
                return [
                    'id' => $jadwal->id,
                    'kelas_id' => $jadwal->kelas_id,
                    'kelas_nama' => $jadwal->kelas->nama_kelas ?? '-',
                    'mata_pelajaran_id' => $jadwal->mata_pelajaran_id,
                    'mata_pelajaran' => $jadwal->mataPelajaran->nama_mapel ?? '-',
                    'jam_ke' => $jadwal->jam_ke,
                    'jam_mulai' => $jadwal->jamBelajar->jam_mulai ?? null,
                    'jam_selesai' => $jadwal->jamBelajar->jam_selesai ?? null,
                ];
            });

        return response()->json($jadwalOptions);
    }
}
