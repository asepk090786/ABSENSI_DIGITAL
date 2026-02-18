<?php

namespace App\Http\Controllers;

use App\Models\AgendaKelas;
use App\Models\AgendaGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AgendaKelasController extends Controller
{
    public function index(Request $request)
    {
        $tahun = DB::table('tahun_ajaran')->where('is_active',1)->first();
        $semester = DB::table('semester')->where('is_active',1)->first();

        if (! $tahun || ! $semester) {
            $items = collect();
            $kelasQuickAccess = collect();
            $guruQuickAccess = collect();
            $selectedGuru = null;
            return view('agenda_kelas.index', compact('items', 'kelasQuickAccess', 'guruQuickAccess', 'selectedGuru'))
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        // Get guru yang login
        $user = auth()->user();
        $guru = $user->guru;

        // Get guru_id dari query parameter (untuk filter)
        $filterGuruId = $request->get('guru_id');
        $selectedGuru = null;

        // Filter agenda
        $query = AgendaKelas::where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id);
        
        if ($guru) {
            // Jika ada guru yang login, hanya tampilkan agenda guru tersebut
            $query->where('guru_id', $guru->id);
        } elseif ($filterGuruId) {
            // Jika ada filter guru dari query parameter
            $query->where('guru_id', $filterGuruId);
        }
        
        $items = $query->orderBy('tanggal','desc')
            ->get();
        
        // Get daftar guru untuk quick access (hanya guru yang memiliki jadwal KBM)
        $guruQuickAccess = DB::table('jadwal_kbm')
            ->join('guru', 'jadwal_kbm.guru_id', '=', 'guru.id')
            ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id ?? 0)
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
                ->where('jadwal_kbm.tahun_ajaran_id', $tahun->id ?? 0)
                ->select('kelas.id', 'kelas.nama_kelas', 'wali.nama as wali_nama')
                ->distinct()
                ->orderBy('kelas.nama_kelas')
                ->get();
        } else {
            $kelasQuickAccess = collect();
        }
        
        return view('agenda_kelas.index', compact('items', 'kelasQuickAccess', 'guruQuickAccess', 'selectedGuru', 'filterGuruId'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $guru = $user->guru;
        
        if (!$guru) {
            return redirect()->route('agenda_kelas.index')
                ->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        // Get jam belajar
        $jam = DB::table('jam_belajar')->get();
        
        // Get kelas berdasarkan jadwal mengajar guru yang login
        $kelas = DB::table('jadwal_kbm')
            ->join('kelas', 'jadwal_kbm.kelas_id', '=', 'kelas.id')
            ->where('jadwal_kbm.guru_id', $guru->id)
            ->select('kelas.id', 'kelas.nama_kelas')
            ->distinct()
            ->get();
        
        // Get all guru for reference (if needed for other features)
        $guruList = DB::table('guru')->get();

        // Get kelas_id dari query parameter (dari quick access)
        $selectedKelasId = $request->get('kelas_id');
        $selectedJamData = null;
        $suggestedDate = null;

        if ($selectedKelasId) {
            // Get jadwal untuk kelas yang dipilih
            $selectedJamData = DB::table('jadwal_kbm')
                ->join('jam_belajar', 'jadwal_kbm.jam_belajar_id', '=', 'jam_belajar.id')
                ->where('jadwal_kbm.guru_id', $guru->id)
                ->where('jadwal_kbm.kelas_id', $selectedKelasId)
                ->select('jam_belajar.*', 'jadwal_kbm.jam_belajar_id')
                ->orderBy('jam_belajar.urutan')
                ->first();

            // Hitung tanggal yang sesuai dengan jadwal (hari yang sesuai dari jam belajar)
            if ($selectedJamData) {
                $todayDayName = \Carbon\Carbon::now()->format('l');
                $dayMapping = [
                    'Monday' => 'Senin',
                    'Tuesday' => 'Selasa',
                    'Wednesday' => 'Rabu',
                    'Thursday' => 'Kamis',
                    'Friday' => 'Jumat',
                    'Saturday' => 'Sabtu',
                    'Sunday' => 'Minggu'
                ];
                
                $targetDay = $selectedJamData->hari;
                $today = \Carbon\Carbon::now();
                $date = $today->copy();

                // Cari tanggal terdekat dengan hari yang sesuai
                for ($i = 0; $i < 7; $i++) {
                    if ($date->format('l') == array_search($targetDay, $dayMapping)) {
                        $suggestedDate = $date->format('Y-m-d');
                        break;
                    }
                    $date->addDay();
                }
            }
        }
        
        return view('agenda_kelas.create', compact('jam', 'kelas', 'guru', 'guruList', 'selectedKelasId', 'selectedJamData', 'suggestedDate'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $guru = $user->guru;
        
        if (!$guru) {
            return back()->with('error', 'Anda tidak terdaftar sebagai guru.');
        }

        $data = $request->validate([
            'agenda_id' => 'nullable|integer',
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
            'apply_to_all_jam' => 'nullable|boolean',
        ]);

        // Hapus agenda_id dari data jika ada (jika dari form show)
        $agendaId = $data['agenda_id'] ?? null;
        unset($data['agenda_id']);
        
        // Ambil flag apply_to_all_jam
        $applyToAllJam = $data['apply_to_all_jam'] ?? false;
        unset($data['apply_to_all_jam']);

        // Validasi guru hanya bisa input untuk kelas sesuai jadwal mengajarnya
        $hasSchedule = DB::table('jadwal_kbm')
            ->where('guru_id', $guru->id)
            ->where('kelas_id', $data['kelas_id'])
            ->where('jam_belajar_id', $data['jam_belajar_id'])
            ->exists();

        if (!$hasSchedule) {
            return back()->withErrors('Anda tidak memiliki jadwal mengajar untuk kelas dan jam KBM yang dipilih.');
        }

        // Validasi guru_id yang dikirim harus sama dengan guru yang login
        if ($data['guru_id'] != $guru->id) {
            return back()->withErrors('Guru yang dipilih tidak sesuai.');
        }

        $tahun = DB::table('tahun_ajaran')->where('is_active',1)->first();
        $semester = DB::table('semester')->where('is_active',1)->first();

        if (! $tahun || ! $semester) {
            return back()->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $data['tahun_ajaran_id'] = $tahun->id;
        $data['semester_id'] = $semester->id;

        if ($applyToAllJam) {
            // Cari semua jam KBM untuk kelas yang sama dengan guru yang login
            $allJamForKelas = DB::table('jadwal_kbm')
                ->where('guru_id', $guru->id)
                ->where('kelas_id', $data['kelas_id'])
                ->pluck('jam_belajar_id')
                ->toArray();

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
                    $this->syncAgendaGuru($existingAgenda);
                } else {
                    // Create new
                    $newAgenda = AgendaKelas::create($agendaData);
                    $this->syncAgendaGuru($newAgenda);
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
                $this->syncAgendaGuru($agenda);
                $message = 'Agenda kelas berhasil diperbarui';
            } else {
                // Create new agenda
                $agenda = AgendaKelas::create($data);
                $this->syncAgendaGuru($agenda);
                $message = 'Agenda kelas ditambahkan';
            }
        }

        return redirect()->route('agenda_kelas.index')->with('success', $message);
    }

    public function show($id)
    {
        $agenda = AgendaKelas::findOrFail($id);
        
        // Validasi bahwa guru hanya bisa akses agenda mereka sendiri
        $user = auth()->user();
        $guru = $user->guru;
        
        if ($agenda->guru_id != $guru->id) {
            return redirect()->route('agenda_kelas.index')
                ->with('error', 'Anda tidak memiliki akses untuk agenda ini.');
        }

        $kelas = DB::table('kelas')->find($agenda->kelas_id);
        $jamBelajar = DB::table('jam_belajar')->find($agenda->jam_belajar_id);

        return view('agenda_kelas.show', compact('agenda', 'kelas', 'jamBelajar', 'guru'));
    }

    public function edit($id)
    {
        $agenda = AgendaKelas::findOrFail($id);
        
        // Validasi bahwa guru hanya bisa edit agenda mereka sendiri
        $user = auth()->user();
        $guru = $user->guru;
        
        if ($agenda->guru_id != $guru->id) {
            return redirect()->route('agenda_kelas.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengedit agenda ini.');
        }

        $kelas = DB::table('kelas')->find($agenda->kelas_id);
        $jamBelajar = DB::table('jam_belajar')->find($agenda->jam_belajar_id);

        return view('agenda_kelas.show', compact('agenda', 'kelas', 'jamBelajar', 'guru'));
    }

    public function update(Request $request, $id)
    {
        $agenda = AgendaKelas::findOrFail($id);
        
        $user = auth()->user();
        $guru = $user->guru;
        
        if ($agenda->guru_id != $guru->id) {
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

        // Validasi guru hanya bisa update untuk kelas sesuai jadwal mengajarnya
        $hasSchedule = DB::table('jadwal_kbm')
            ->where('guru_id', $guru->id)
            ->where('kelas_id', $data['kelas_id'])
            ->where('jam_belajar_id', $data['jam_belajar_id'])
            ->exists();

        if (!$hasSchedule) {
            return back()->withErrors('Anda tidak memiliki jadwal mengajar untuk kelas dan jam KBM yang dipilih.');
        }

        if ($data['guru_id'] != $guru->id) {
            return back()->withErrors('Guru yang dipilih tidak sesuai.');
        }

        $agenda->update($data);
        
        // Sync changes to agenda guru
        $this->syncAgendaGuru($agenda);

        return redirect()->route('agenda_kelas.index')->with('success', 'Agenda kelas berhasil diperbarui');
    }

    public function destroy($id)
    {
        $agenda = AgendaKelas::findOrFail($id);
        
        $user = auth()->user();
        $guru = $user->guru;
        
        if ($agenda->guru_id != $guru->id) {
            return back()->with('error', 'Anda tidak memiliki akses untuk menghapus agenda ini.');
        }

        // Cleanup agenda guru if needed
        $this->cleanupAgendaGuru($agenda);
        
        $agenda->delete();

        return redirect()->route('agenda_kelas.index')->with('success', 'Agenda kelas berhasil dihapus');
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
    private function syncAgendaGuru(AgendaKelas $agendaKelas)
    {
        // Get kelas info untuk deskripsi
        $kelas = DB::table('kelas')->find($agendaKelas->kelas_id);
        
        // Buat ringkasan kegiatan: Kelas + Kegiatan
        $kegiatanRingkasan = $kelas ? $kelas->nama_kelas . ' - ' : '';
        $kegiatanRingkasan .= $agendaKelas->kegiatan ?? '';

        // Cari atau buat agenda guru dengan kriteria yang sama
        $agendaGuru = AgendaGuru::where('guru_id', $agendaKelas->guru_id)
            ->where('jam_belajar_id', $agendaKelas->jam_belajar_id)
            ->where('tanggal', $agendaKelas->tanggal)
            ->where('tahun_ajaran_id', $agendaKelas->tahun_ajaran_id)
            ->where('semester_id', $agendaKelas->semester_id)
            ->first();

        if ($agendaGuru) {
            // Update existing - append kelas baru jika belum ada
            if (strpos($agendaGuru->kegiatan, $kelas->nama_kelas) === false) {
                $agendaGuru->kegiatan = $agendaGuru->kegiatan . "\n" . $kegiatanRingkasan;
                $agendaGuru->save();
            }
        } else {
            // Create new agenda guru
            AgendaGuru::create([
                'guru_id' => $agendaKelas->guru_id,
                'jam_belajar_id' => $agendaKelas->jam_belajar_id,
                'tanggal' => $agendaKelas->tanggal,
                'kegiatan' => $kegiatanRingkasan,
                'tahun_ajaran_id' => $agendaKelas->tahun_ajaran_id,
                'semester_id' => $agendaKelas->semester_id,
            ]);
        }
    }

    /**
     * Remove agenda guru jika semua agenda kelas untuk jam tersebut sudah dihapus
     */
    private function cleanupAgendaGuru(AgendaKelas $deletedAgenda)
    {
        // Cek apakah masih ada agenda kelas lain untuk jam, tanggal, dan guru yang sama
        $otherAgendas = AgendaKelas::where('guru_id', $deletedAgenda->guru_id)
            ->where('jam_belajar_id', $deletedAgenda->jam_belajar_id)
            ->where('tanggal', $deletedAgenda->tanggal)
            ->where('tahun_ajaran_id', $deletedAgenda->tahun_ajaran_id)
            ->where('semester_id', $deletedAgenda->semester_id)
            ->count();

        // Jika tidak ada agenda kelas lain, hapus agenda guru
        if ($otherAgendas === 0) {
            AgendaGuru::where('guru_id', $deletedAgenda->guru_id)
                ->where('jam_belajar_id', $deletedAgenda->jam_belajar_id)
                ->where('tanggal', $deletedAgenda->tanggal)
                ->where('tahun_ajaran_id', $deletedAgenda->tahun_ajaran_id)
                ->where('semester_id', $deletedAgenda->semester_id)
                ->delete();
        }
    }
}
