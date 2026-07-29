<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ekstrakurikuler;
use App\Models\EkskulPembina;
use App\Models\EkskulAnggota;
use App\Models\EkskulJadwal;
use App\Models\EkskulAgenda;
use App\Models\EkskulAbsensi;
use App\Models\EkskulAbsensiPembina;
use App\Models\EkskulBuktiKegiatan;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Support\Facades\Storage;

class EkskulController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('Admin')) {
            $items = Ekstrakurikuler::with(['guru', 'pembina.guru'])
                ->withCount(['anggota as anggota_diterima_count' => function ($q) {
                    $q->where('status_pendaftaran', 'diterima');
                }])
                ->withCount('jadwal')
                ->orderBy('nama')
                ->get();
        } elseif ($user->hasRole('Siswa') && $user->siswa) {
            $ekskulIds = EkskulAnggota::where('siswa_id', $user->siswa->id)
                ->where('status_pendaftaran', 'diterima')
                ->pluck('ekstrakurikuler_id');
            $items = Ekstrakurikuler::with(['guru', 'pembina.guru'])
                ->whereIn('id', $ekskulIds)
                ->withCount(['anggota as anggota_diterima_count' => function ($q) {
                    $q->where('status_pendaftaran', 'diterima');
                }])
                ->withCount('jadwal')
                ->orderBy('nama')
                ->get();
        } else {
            $guruId = $user->guru?->id;
            $ekskulIds = EkskulPembina::where('guru_id', $guruId)->pluck('ekstrakurikuler_id');
            $items = Ekstrakurikuler::with(['guru', 'pembina.guru'])
                ->where(function ($q) use ($guruId, $ekskulIds) {
                    $q->whereIn('id', $ekskulIds)->orWhere('guru_id', $guruId);
                })
                ->withCount(['anggota as anggota_diterima_count' => function ($q) {
                    $q->where('status_pendaftaran', 'diterima');
                }])
                ->withCount('jadwal')
                ->orderBy('nama')
                ->get();
        }

        return view('ekskul.index', compact('items'));
    }

    public function create()
    {
        $this->authorizeAdmin();
        $guruList = Guru::orderBy('nama')->get();
        return view('ekskul.form', compact('guruList'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'nama'      => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'lokasi'    => 'nullable|string|max:200',
            'kuota_max' => 'nullable|integer|min:1',
            'guru_id'   => 'nullable|exists:guru,id',
        ]);

        $validated['status'] = 'aktif';
        $ekskul = Ekstrakurikuler::create($validated);

        if (!empty($validated['guru_id'])) {
            EkskulPembina::firstOrCreate([
                'ekstrakurikuler_id' => $ekskul->id,
                'guru_id'            => $validated['guru_id'],
            ], [
                'jabatan' => 'Kepala Pembina',
                'status'  => 'aktif',
            ]);
        }

        return redirect()->route('ekskul.index')->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $this->authorizeAdmin();
        $data = Ekstrakurikuler::findOrFail($id);
        $guruList = Guru::orderBy('nama')->get();
        return view('ekskul.form', compact('data', 'guruList'));
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $validated = $request->validate([
            'nama'      => 'required|string|max:150',
            'deskripsi' => 'nullable|string',
            'lokasi'    => 'nullable|string|max:200',
            'kuota_max' => 'nullable|integer|min:1',
            'guru_id'   => 'nullable|exists:guru,id',
        ]);
        $ekskul->update($validated);
        return redirect()->route('ekskul.edit', $id)->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->authorizeAdmin();
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $ekskul->delete();
        return redirect()->route('ekskul.index')->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }

    public function manageAnggota($id)
    {
        $ekskul = Ekstrakurikuler::with(['anggota.siswa.kelas', 'pembina'])->findOrFail($id);
        $this->authorizePembinaOrAdmin($ekskul);
        $anggota = $ekskul->anggota()->with(['siswa.kelas'])->orderBy('tanggal_daftar', 'desc')->get();
        return view('ekskul.anggota', compact('ekskul', 'anggota'));
    }

    public function updateStatusAnggota(Request $request, $id)
    {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $this->authorizePembinaOrAdmin($ekskul);
        $validated = $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'status'   => 'required|in:diterima,ditolak',
        ]);
        $anggota = EkskulAnggota::where('ekstrakurikuler_id', $id)
            ->where('siswa_id', $validated['siswa_id'])
            ->firstOrFail();
        $anggota->update(['status_pendaftaran' => $validated['status']]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Status anggota berhasil diperbarui.']);
        }
        return redirect()->route('ekskul.anggota', $id)->with('success', 'Status anggota berhasil diperbarui.');
    }

    public function daftar($id)
    {
        $user = auth()->user();
        if (!$user->hasRole('Siswa') || !$user->siswa) {
            return redirect()->back()->with('error', 'Hanya siswa yang dapat mendaftar.');
        }
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $siswaId = $user->siswa->id;

        $existing = EkskulAnggota::where('ekstrakurikuler_id', $id)
            ->where('siswa_id', $siswaId)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah terdaftar di ekstrakurikuler ini.');
        }
        $anggotaCount = EkskulAnggota::where('ekstrakurikuler_id', $id)
            ->where('status_pendaftaran', 'diterima')->count();
        if ($ekskul->kuota_max && $anggotaCount >= $ekskul->kuota_max) {
            return redirect()->back()->with('error', 'Maaf, kuota ekstrakurikuler sudah penuh.');
        }
        EkskulAnggota::create([
            'ekstrakurikuler_id' => $id,
            'siswa_id'           => $siswaId,
            'status_pendaftaran' => 'pending',
            'tanggal_daftar'     => now()->toDateString(),
        ]);
        return redirect()->back()->with('success', 'Pendaftaran berhasil. Silakan tunggu konfirmasi dari pembina.');
    }

    public function jadwal($id)
    {
        $ekskul = Ekstrakurikuler::with('jadwal')->findOrFail($id);
        $jadwal = $ekskul->jadwal()->orderByRaw("FIELD(hari, 'Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu')")->get();
        return view('ekskul.jadwal', compact('ekskul', 'jadwal'));
    }

    public function storeJadwal(Request $request, $id)
    {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $validated = $request->validate([
            'hari'        => 'required|string|max:20',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'lokasi'      => 'nullable|string|max:200',
        ]);
        $validated['ekstrakurikuler_id'] = $id;
        EkskulJadwal::create($validated);
        return redirect()->route('ekskul.jadwal', $id)->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function deleteJadwal($id, $jadwalId)
    {
        $jadwal = EkskulJadwal::where('ekstrakurikuler_id', $id)->findOrFail($jadwalId);
        $jadwal->delete();
        return redirect()->route('ekskul.jadwal', $id)->with('success', 'Jadwal berhasil dihapus.');
    }

    public function agenda($id)
    {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $agenda = $ekskul->agenda()->with('dibuatOleh')->orderBy('tanggal', 'desc')->get();
        return view('ekskul.agenda', compact('ekskul', 'agenda'));
    }

    public function storeAgenda(Request $request, $id)
    {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $validated = $request->validate([
            'judul'       => 'required|string|max:200',
            'deskripsi'   => 'nullable|string',
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required',
            'jam_selesai' => 'required',
            'lokasi'      => 'nullable|string|max:200',
            'jenis'       => 'required|in:rutin,khusus',
            'materi'      => 'nullable|string',
        ]);
        $validated['ekstrakurikuler_id'] = $id;
        $validated['status'] = 'direncanakan';
        $validated['dibuat_oleh'] = auth()->user()->guru?->id;
        EkskulAgenda::create($validated);
        return redirect()->route('ekskul.agenda', $id)->with('success', 'Agenda berhasil ditambahkan.');
    }

    public function absensi($id, $agendaId = null)
    {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $siswa = EkskulAnggota::where('ekstrakurikuler_id', $id)
            ->where('status_pendaftaran', 'diterima')
            ->with('siswa.kelas')
            ->orderBy('tanggal_daftar')
            ->get();
        $agenda = null;
        $existingAbsensi = collect();
        if ($agendaId) {
            $agenda = EkskulAgenda::findOrFail($agendaId);
            $existingAbsensi = EkskulAbsensi::where('ekstrakurikuler_id', $id)
                ->where('ekskul_agenda_id', $agendaId)
                ->get()
                ->keyBy('siswa_id');
        }
        $agendaList = $ekskul->agenda()->orderBy('tanggal', 'desc')->get();
        return view('ekskul.absensi', compact('ekskul', 'siswa', 'agenda', 'agendaList', 'agendaId', 'existingAbsensi'));
    }

    public function storeAbsensi(Request $request, $id)
    {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $validated = $request->validate([
            'tanggal'              => 'required|date',
            'ekskul_agenda_id'     => 'nullable|exists:ekskul_agenda,id',
            'absensi'              => 'required|array',
            'absensi.*.siswa_id'   => 'required|exists:siswa,id',
            'absensi.*.status'     => 'required|in:hadir,izin,sakit,alpha,tanpa_keterangan',
            'absensi.*.keterangan' => 'nullable|string',
        ]);
        $guruId = auth()->user()->guru?->id;
        foreach ($validated['absensi'] as $item) {
            EkskulAbsensi::updateOrCreate(
                ['ekstrakurikuler_id' => $id, 'siswa_id' => $item['siswa_id'], 'tanggal' => $validated['tanggal']],
                ['ekskul_agenda_id' => $validated['ekskul_agenda_id'] ?? null, 'status' => $item['status'], 'keterangan' => $item['keterangan'] ?? null, 'dibukukan_oleh' => $guruId]
            );
        }
        return redirect()->route('ekskul.absensi', [$id, 'agenda' => $validated['ekskul_agenda_id'] ?? ''])
            ->with('success', 'Absensi berhasil disimpan.');
    }

    public function absensiPembina($id)
    {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $guruId = auth()->user()->guru?->id;
        $today = now()->toDateString();
        $checkinToday = EkskulAbsensiPembina::where('ekstrakurikuler_id', $id)
            ->where('guru_id', $guruId)->where('tanggal', $today)->first();
        $riwayat = EkskulAbsensiPembina::where('ekstrakurikuler_id', $id)
            ->where('guru_id', $guruId)
            ->orderBy('tanggal', 'desc')->orderBy('jam_checkin', 'desc')->limit(10)->get();
        return view('ekskul.absensi_pembina', compact('ekskul', 'checkinToday', 'riwayat'));
    }

    public function storeAbsensiPembina(Request $request, $id)
    {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $guruId = auth()->user()->guru?->id;
        if (!$guruId) {
            return redirect()->back()->with('error', 'Akun Anda tidak terhubung dengan data guru.');
        }
        $validated = $request->validate([
            'foto'      => 'nullable|image|max:2048',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('ekskul/absensi-pembina', 'public');
        }
        EkskulAbsensiPembina::create([
            'ekstrakurikuler_id' => $id,
            'guru_id'            => $guruId,
            'tanggal'            => now()->toDateString(),
            'jam_checkin'        => now()->format('H:i:s'),
            'foto'               => $fotoPath,
            'latitude'           => $validated['latitude'] ?? null,
            'longitude'          => $validated['longitude'] ?? null,
        ]);
        return redirect()->route('ekskul.absensi_pembina', $id)->with('success', 'Check-in berhasil.');
    }

    public function buktiKegiatan($id)
    {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $bukti = $ekskul->buktiKegiatan()->with(['diuploadOleh', 'ekskulAgenda'])->orderBy('created_at', 'desc')->get();
        return view('ekskul.bukti', compact('ekskul', 'bukti'));
    }

    public function storeBuktiKegiatan(Request $request, $id)
    {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $validated = $request->validate([
            'judul'            => 'required|string|max:200',
            'deskripsi'        => 'nullable|string',
            'file'             => 'required|file|max:10240',
            'ekskul_agenda_id' => 'nullable|exists:ekskul_agenda,id',
        ]);
        $file = $request->file('file');
        $filePath = $file->store('ekskul/bukti', 'public');
        $fileType = $file->getClientOriginalExtension();
        EkskulBuktiKegiatan::create([
            'ekstrakurikuler_id' => $id,
            'ekskul_agenda_id'   => $validated['ekskul_agenda_id'] ?? null,
            'judul'              => $validated['judul'],
            'deskripsi'          => $validated['deskripsi'] ?? null,
            'file_path'          => $filePath,
            'file_type'          => $fileType,
            'diupload_oleh'      => auth()->user()->guru?->id,
        ]);
        return redirect()->route('ekskul.bukti', $id)->with('success', 'Bukti kegiatan berhasil diupload.');
    }

    public function rekap($id)
    {
        $ekskul = Ekstrakurikuler::findOrFail($id);
        $tanggalMulai = request('tanggal_mulai', now()->startOfMonth()->toDateString());
        $tanggalSelesai = request('tanggal_selesai', now()->toDateString());
        $siswa = EkskulAnggota::where('ekstrakurikuler_id', $id)
            ->where('status_pendaftaran', 'diterima')->with('siswa.kelas')->get();
        $rekap = [];
        foreach ($siswa as $anggota) {
            $absensi = EkskulAbsensi::where('ekstrakurikuler_id', $id)
                ->where('siswa_id', $anggota->siswa_id)
                ->whereBetween('tanggal', [$tanggalMulai, $tanggalSelesai])->get();
            $totalHadir = $absensi->where('status', 'hadir')->count();
            $totalIzin = $absensi->where('status', 'izin')->count();
            $totalSakit = $absensi->where('status', 'sakit')->count();
            $totalAlpha = $absensi->where('status', 'alpha')->count();
            $totalPertemuan = $absensi->count();
            $persentase = $totalPertemuan > 0 ? round(($totalHadir / $totalPertemuan) * 100, 2) : 0;
            $rekap[] = (object) [
                'siswa'           => $anggota->siswa,
                'total_hadir'     => $totalHadir,
                'total_izin'      => $totalIzin,
                'total_sakit'     => $totalSakit,
                'total_alpha'     => $totalAlpha,
                'total_pertemuan' => $totalPertemuan,
                'persentase'      => $persentase,
            ];
        }
        return view('ekskul.rekap', compact('ekskul', 'rekap', 'tanggalMulai', 'tanggalSelesai'));
    }

    private function authorizeAdmin()
    {
        if (!auth()->user()->hasRole('Admin')) {
            abort(403, 'Hanya Admin yang dapat melakukan aksi ini.');
        }
    }

    private function authorizePembinaOrAdmin(Ekstrakurikuler $ekskul)
    {
        $user = auth()->user();
        if ($user->hasRole('Admin')) return;
        $guruId = $user->guru?->id;
        if (!$guruId) abort(403);
        $isPembina = EkskulPembina::where('ekstrakurikuler_id', $ekskul->id)
            ->where('guru_id', $guruId)->exists();
        $isMainPembina = $ekskul->guru_id === $guruId;
        if (!$isPembina && !$isMainPembina) {
            abort(403, 'Anda bukan pembina dari ekstrakurikuler ini.');
        }
    }
}
