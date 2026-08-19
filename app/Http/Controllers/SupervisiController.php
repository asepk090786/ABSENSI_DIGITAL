<?php

namespace App\Http\Controllers;

use App\Models\Supervisi;
use App\Models\JadwalKbm;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\Semester;
use App\Models\SupervisionInstrument;
use App\Models\SupervisionIndicator;
use App\Models\PostConference;
use App\Models\ActionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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

        $total = $items->count();
        $terjadwal = $items->where('status', 'Terjadwal')->count();
        $berlangsung = $items->where('status', 'Berlangsung')->count();
        $selesai = $items->where('status', 'Selesai')->count();
        $butuhTindakLanjut = $items->filter(fn ($item) => in_array($item->status, ['Selesai', 'Berlangsung'], true))->count();

        return view('akademik.supervisi.dashboard', compact('items', 'total', 'terjadwal', 'berlangsung', 'selesai', 'butuhTindakLanjut'));
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

    public function dashboard()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $items = Supervisi::with(['guru.user', 'mataPelajaran', 'kelas'])
            ->orderByDesc('tanggal')
            ->orderBy('jam_ke')
            ->get();

        $total = $items->count();
        $terjadwal = $items->where('status', 'Terjadwal')->count();
        $berlangsung = $items->where('status', 'Berlangsung')->count();
        $selesai = $items->where('status', 'Selesai')->count();
        $butuhTindakLanjut = $items->filter(fn ($item) => in_array($item->status, ['Selesai', 'Berlangsung'], true))->count();

        return view('akademik.supervisi.dashboard', compact('items', 'total', 'terjadwal', 'berlangsung', 'selesai', 'butuhTindakLanjut'));
    }

    protected function supervisiQueryFilteredByStatus(array $statuses = []): \Illuminate\Database\Eloquent\Builder
    {
        $query = Supervisi::with(['guru.user', 'mataPelajaran', 'kelas']);

        if (! empty($statuses) && Schema::hasColumn('supervisi', 'status')) {
            $query->whereIn('status', $statuses);
        }

        return $query->orderByDesc('tanggal');
    }

    public function prasupervisi()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $items = $this->supervisiQueryFilteredByStatus(['Terjadwal', 'Berlangsung'])
            ->get();

        return view('akademik.supervisi.prasupervisi', compact('items'));
    }

    public function pelaksanaan()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $items = $this->supervisiQueryFilteredByStatus(['Berlangsung', 'Terjadwal'])
            ->get();

        return view('akademik.supervisi.pelaksanaan', compact('items'));
    }

    public function pascasupervisi()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $items = $this->supervisiQueryFilteredByStatus(['Selesai', 'Berlangsung'])
            ->get();

        return view('akademik.supervisi.pascasupervisi', compact('items'));
    }

    public function tindakLanjut()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $items = $this->supervisiQueryFilteredByStatus(['Selesai', 'Berlangsung'])
            ->get();

        return view('akademik.supervisi.tindak_lanjut', compact('items'));
    }

    public function monitoring()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $items = $this->supervisiQueryFilteredByStatus(['Selesai', 'Berlangsung'])
            ->get();

        return view('akademik.supervisi.monitoring', compact('items'));
    }

    public function laporan()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $items = Supervisi::with(['guru.user', 'mataPelajaran', 'kelas'])
            ->orderByDesc('tanggal')
            ->get();

        return view('akademik.supervisi.laporan', compact('items'));
    }

    public function instrumenIndex()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $items = SupervisionInstrument::orderBy('nama')->get();

        return view('akademik.supervisi.instrumen_index', compact('items'));
    }

    public function instrumenCreate()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $tipeInstrumen = ['checklist', 'skala', 'deskriptif'];

        return view('akademik.supervisi.instrumen_create', compact('tipeInstrumen'));
    }

    public function instrumenStore(Request $request)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:supervision_instruments,nama',
            'deskripsi' => 'nullable|string',
            'kategori' => 'nullable|string|max:255',
            'tipe' => 'required|in:checklist,skala,deskriptif',
        ]);

        SupervisionInstrument::create($validated);

        return redirect()->route('supervisi.instrumen.index')->with('success', 'Instrumen berhasil ditambahkan.');
    }

    public function instrumenEdit(SupervisionInstrument $instrumen)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $tipeInstrumen = ['checklist', 'skala', 'deskriptif'];

        return view('akademik.supervisi.instrumen_edit', compact('instrumen', 'tipeInstrumen'));
    }

    public function instrumenUpdate(Request $request, SupervisionInstrument $instrumen)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'nama' => 'required|string|max:255|unique:supervision_instruments,nama,' . $instrumen->id,
            'deskripsi' => 'nullable|string',
            'kategori' => 'nullable|string|max:255',
            'tipe' => 'required|in:checklist,skala,deskriptif',
            'is_active' => 'nullable|boolean',
        ]);

        $instrumen->update($validated);

        return redirect()->route('supervisi.instrumen.index')->with('success', 'Instrumen berhasil diperbarui.');
    }

    public function instrumenDelete(SupervisionInstrument $instrumen)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $instrumen->delete();

        return redirect()->route('supervisi.instrumen.index')->with('success', 'Instrumen berhasil dihapus.');
    }

    public function indikatorIndex()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $items = SupervisionIndicator::with('instrument')->orderBy('urutan')->get();

        return view('akademik.supervisi.indikator_index', compact('items'));
    }

    public function indikatorCreate()
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $instruments = SupervisionInstrument::where('is_active', true)->orderBy('nama')->get();
        $kategoris = ['Aktivitas Guru', 'Aktivitas Murid', 'Lingkungan Belajar'];

        return view('akademik.supervisi.indikator_create', compact('instruments', 'kategoris'));
    }

    public function indikatorStore(Request $request)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'instrument_id' => 'required|exists:supervision_instruments,id',
            'kategori' => 'required|string',
            'indikator' => 'required|string',
            'deskripsi' => 'nullable|string',
            'bobot' => 'nullable|integer|min:1',
            'urutan' => 'nullable|integer|min:0',
        ]);

        SupervisionIndicator::create($validated);

        return redirect()->route('supervisi.indikator.index')->with('success', 'Indikator berhasil ditambahkan.');
    }

    public function indikatorEdit(SupervisionIndicator $indikator)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $instruments = SupervisionInstrument::where('is_active', true)->orderBy('nama')->get();
        $kategoris = ['Aktivitas Guru', 'Aktivitas Murid', 'Lingkungan Belajar'];

        return view('akademik.supervisi.indikator_edit', compact('indikator', 'instruments', 'kategoris'));
    }

    public function indikatorUpdate(Request $request, SupervisionIndicator $indikator)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'instrument_id' => 'required|exists:supervision_instruments,id',
            'kategori' => 'required|string',
            'indikator' => 'required|string',
            'deskripsi' => 'nullable|string',
            'bobot' => 'nullable|integer|min:1',
            'urutan' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $indikator->update($validated);

        return redirect()->route('supervisi.indikator.index')->with('success', 'Indikator berhasil diperbarui.');
    }

    public function indikatorDelete(SupervisionIndicator $indikator)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $indikator->delete();

        return redirect()->route('supervisi.indikator.index')->with('success', 'Indikator berhasil dihapus.');
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
        $endDate = $cursor->copy()->addDays(365);

        while ($cursor->lte($endDate)) {
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

    // ===== PRASUPERVISI / PRE-CONFERENCE =====
    public function prasupervisiEdit(Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $supervisi->load(['guru.user', 'mataPelajaran', 'kelas', 'supervisor']);
        $fokusOptions = ['Strategi pembelajaran aktif', 'Asesmen formatif', 'Interaksi guru-murid', 'Pengelolaan kelas', 'Pembelajaran kolaboratif', 'Penggunaan media/teknologi'];

        return view('akademik.supervisi.prasupervisi_form', compact('supervisi', 'fokusOptions'));
    }

    public function prasupervisiUpdate(Request $request, Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'tujuan' => 'required|string|max:500',
            'fokus' => 'required|string',
            'supervisor_id' => 'required|exists:guru,id',
            'status' => 'required|in:Draft,Terjadwal,Berlangsung,Selesai,Dibatalkan',
        ]);

        $supervisi->update($validated);

        return redirect()->route('supervisi.prasupervisi')->with('success', 'Prasupervisi berhasil disimpan.');
    }

    // ===== OBSERVASI =====
    public function observasiShow(Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $supervisi->load(['guru.user', 'mataPelajaran', 'kelas', 'observationItems.indicator', 'evidences']);
        $instruments = SupervisionInstrument::where('is_active', true)->with('indicators')->get();

        return view('akademik.supervisi.observasi_form', compact('supervisi', 'instruments'));
    }

    public function observasiStore(Request $request, Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'catatan_objektif' => 'required|string',
            'observation_items' => 'nullable|array',
            'observation_items.*.indicator_id' => 'nullable|exists:supervision_indicators,id',
            'observation_items.*.skor' => 'nullable|integer|min:1|max:5',
            'observation_items.*.catatan' => 'nullable|string',
            'evidences' => 'nullable|array',
            'evidences.*.jenis' => 'nullable|in:foto,video,dokumen',
            'evidences.*.file' => 'nullable|file|max:10240',
            'evidences.*.keterangan' => 'nullable|string',
        ]);

        // Save catatan objektif
        $supervisi->update([
            'catatan_objektif' => $validated['catatan_objektif'],
            'status' => 'Berlangsung',
        ]);

        // Save observation items
        if (!empty($validated['observation_items'])) {
            foreach ($validated['observation_items'] as $item) {
                if (!empty($item['indicator_id'])) {
                    \App\Models\ObservationItem::updateOrCreate(
                        ['supervisi_id' => $supervisi->id, 'indicator_id' => $item['indicator_id']],
                        ['skor' => $item['skor'] ?? null, 'catatan' => $item['catatan'] ?? null]
                    );
                }
            }
        }

        // Save evidences with file upload
        if (!empty($validated['evidences'])) {
            foreach ($validated['evidences'] as $idx => $evidence) {
                if (!empty($request->file("evidences.$idx.file"))) {
                    $file = $request->file("evidences.$idx.file");
                    $path = $file->store('supervisi/evidences', 'public');

                    \App\Models\ObservationEvidence::create([
                        'supervisi_id' => $supervisi->id,
                        'jenis' => $evidence['jenis'] ?? 'dokumen',
                        'file_path' => $path,
                        'nama_file' => $file->getClientOriginalName(),
                        'keterangan' => $evidence['keterangan'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('supervisi.pelaksanaan')->with('success', 'Observasi berhasil disimpan.');
    }

    // ===== POST-CONFERENCE =====
    public function postConferenceShow(Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $supervisi->load(['guru.user', 'postConference']);
        $postConference = $supervisi->postConference ?? new \App\Models\PostConference();

        return view('akademik.supervisi.post_conference_form', compact('supervisi', 'postConference'));
    }

    public function postConferenceStore(Request $request, Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'refleksi_guru' => 'required|string',
            'refleksi_supervisor' => 'required|string',
            'tanggal_pelaksanaan' => 'nullable|date_format:Y-m-d H:i',
            'umpan_balik' => 'required|string',
            'kekuatan' => 'required|string',
            'area_pengembangan' => 'required|string',
        ]);

        // Update supervisi status & refleksi
        $supervisi->update([
            'refleksi_guru' => $validated['refleksi_guru'],
            'refleksi_supervisor' => $validated['refleksi_supervisor'],
            'umpan_balik' => $validated['umpan_balik'],
            'status' => 'Selesai',
        ]);

        // Create or update post-conference
        $postConference = \App\Models\PostConference::updateOrCreate(
            ['supervisi_id' => $supervisi->id],
            [
                'refleksi_guru' => $validated['refleksi_guru'],
                'refleksi_supervisor' => $validated['refleksi_supervisor'],
                'tanggal_pelaksanaan' => $validated['tanggal_pelaksanaan'] ?? now(),
            ]
        );

        // Create or update feedback
        \App\Models\Feedback::updateOrCreate(
            ['post_conference_id' => $postConference->id],
            [
                'kekuatan' => $validated['kekuatan'],
                'area_pengembangan' => $validated['area_pengembangan'],
                'umpan_balik' => $validated['umpan_balik'],
            ]
        );

        return redirect()->route('supervisi.pascasupervisi')->with('success', 'Post-conference & feedback berhasil disimpan.');
    }

    // ===== ACTION PLAN =====
    public function actionPlanShow(Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $supervisi->load(['guru.user', 'postConference.actionPlans']);
        $postConference = $supervisi->postConference;
        $actionPlans = $postConference?->actionPlans ?? collect([]);
        $guruList = Guru::orderBy('nama')->get();

        return view('akademik.supervisi.action_plan_form', compact('supervisi', 'actionPlans', 'guruList'));
    }

    public function actionPlanStore(Request $request, Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $postConference = $supervisi->postConference;
        if (!$postConference) {
            return back()->with('error', 'Post-conference harus dibuat terlebih dahulu.');
        }

        $validated = $request->validate([
            'tujuan' => 'required|string',
            'aktivitas' => 'required|string',
            'rekomendasi' => 'nullable|string',
            'penanggung_jawab_id' => 'required|exists:guru,id',
            'target_selesai' => 'required|date',
            'status' => 'nullable|in:Belum Mulai,Berjalan,Selesai,Ditunda,Dibatalkan',
        ]);

        \App\Models\ActionPlan::create([
            'post_conference_id' => $postConference->id,
            'tujuan' => $validated['tujuan'],
            'aktivitas' => $validated['aktivitas'],
            'rekomendasi' => $validated['rekomendasi'] ?? null,
            'penanggung_jawab_id' => $validated['penanggung_jawab_id'],
            'target_selesai' => $validated['target_selesai'],
            'status' => $validated['status'] ?? 'Belum Mulai',
        ]);

        return redirect()->route('supervisi.tindak-lanjut')->with('success', 'Rencana tindak lanjut berhasil dibuat.');
    }

    public function actionPlanEdit(\App\Models\ActionPlan $actionPlan)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $guruList = Guru::orderBy('nama')->get();
        $supervisi = $actionPlan->postConference->supervisi;

        return view('akademik.supervisi.action_plan_edit', compact('actionPlan', 'supervisi', 'guruList'));
    }

    public function actionPlanUpdate(Request $request, \App\Models\ActionPlan $actionPlan)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'tujuan' => 'required|string',
            'aktivitas' => 'required|string',
            'rekomendasi' => 'nullable|string',
            'penanggung_jawab_id' => 'required|exists:guru,id',
            'target_selesai' => 'required|date',
            'status' => 'nullable|in:Belum Mulai,Berjalan,Selesai,Ditunda,Dibatalkan',
        ]);

        $actionPlan->update($validated);

        return redirect()->route('supervisi.tindak-lanjut')->with('success', 'Rencana tindak lanjut berhasil diperbarui.');
    }

    // ===== ACTION PLAN MONITORING =====
    public function monitoringStore(Request $request, \App\Models\ActionPlan $actionPlan)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $validated = $request->validate([
            'tanggal_monitoring' => 'required|date',
            'progress_persen' => 'required|integer|min:0|max:100',
            'catatan' => 'required|string',
            'bukti' => 'nullable|file|max:10240',
        ]);

        $monitoring = \App\Models\ActionPlanMonitoring::create([
            'action_plan_id' => $actionPlan->id,
            'tanggal_monitoring' => $validated['tanggal_monitoring'],
            'progress_persen' => $validated['progress_persen'],
            'catatan' => $validated['catatan'],
        ]);

        // Handle file upload
        if ($request->hasFile('bukti')) {
            $file = $request->file('bukti');
            $path = $file->store('supervisi/monitoring', 'public');
            $monitoring->update(['bukti' => $path]);
        }

        // Auto-update action plan status jika progress 100%
        if ($validated['progress_persen'] >= 100) {
            $actionPlan->update(['status' => 'Selesai']);
        }

        return back()->with('success', 'Monitoring berhasil ditambahkan.');
    }

    // ===== EXPORT =====
    public function exportPdf(Supervisi $supervisi)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $supervisi->load(['guru.user', 'mataPelajaran', 'kelas', 'supervisor', 'observationItems.indicator', 'postConference.feedback', 'postConference.actionPlans.monitorings', 'postConference.actionPlans.penanggungJawab']);

        $pdf = Pdf::loadView('akademik.supervisi.laporan_pdf', compact('supervisi'));
        $filename = 'Supervisi_' . str_replace(' ', '_', $supervisi->guru->user->name ?? $supervisi->guru->nama) . '_' . $supervisi->tanggal->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        if ($redirect = $this->authorizeAccess()) {
            return $redirect;
        }

        $dari = $request->get('dari_tanggal', now()->startOfYear());
        $sampai = $request->get('sampai_tanggal', now());
        $guru = $request->get('guru');
        $status = $request->get('status');

        $supervisions = Supervisi::with('guru.user', 'mataPelajaran', 'kelas', 'postConference.actionPlans')
            ->whereDate('tanggal', '>=', $dari)
            ->whereDate('tanggal', '<=', $sampai)
            ->when($guru, fn($q) => $q->whereHas('guru.user', fn($sq) => $sq->where('name', 'like', '%'.$guru.'%')))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderBy('tanggal', 'desc')
            ->get();

        // Create Excel file using array of data
        $data = [['No.', 'Guru', 'Mapel', 'Kelas', 'Tanggal', 'Fokus', 'Status', 'Post-Conf', 'Tindak Lanjut']];

        foreach ($supervisions as $idx => $supervisi) {
            $data[] = [
                $idx + 1,
                $supervisi->guru->user->name ?? $supervisi->guru->nama,
                $supervisi->mataPelajaran->nama_mapel ?? '-',
                $supervisi->kelas->nama_kelas ?? '-',
                $supervisi->tanggal->format('d-m-Y'),
                substr($supervisi->fokus, 0, 30),
                $supervisi->status,
                $supervisi->postConference ? 'Ya' : 'Tidak',
                $supervisi->postConference?->actionPlans->count() ?? 0,
            ];
        }

        // Create spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Write header
        foreach ($data[0] as $col => $value) {
            $sheet->setCellValueByColumnAndRow($col + 1, 1, $value);
            $sheet->getStyleByColumnAndRow($col + 1, 1)->getFont()->setBold(true);
            $sheet->getStyleByColumnAndRow($col + 1, 1)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('CCE5FF');
        }

        // Write data rows
        foreach ($data as $row_idx => $row) {
            if ($row_idx === 0) continue; // Skip header
            foreach ($row as $col => $value) {
                $sheet->setCellValueByColumnAndRow($col + 1, $row_idx + 1, $value);
            }
        }

        // Auto size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Save to file
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'Laporan_Supervisi_' . now()->format('Y-m-d_His') . '.xlsx';
        $path = storage_path('app/public/exports/' . $filename);

        if (!file_exists(storage_path('app/public/exports'))) {
            mkdir(storage_path('app/public/exports'), 0755, true);
        }

        $writer->save($path);

        return response()->download($path)->deleteFileAfterSend(true);
    }
}
