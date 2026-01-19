<?php

namespace App\Http\Controllers;

use App\Models\AgendaKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaKelasController extends Controller
{
    public function index()
    {
        $tahun = DB::table('tahun_ajaran')->where('is_active',1)->first();
        $semester = DB::table('semester')->where('is_active',1)->first();

        if (! $tahun || ! $semester) {
            $items = collect();
            $kelasQuickAccess = collect();
            return view('agenda_kelas.index', compact('items', 'kelasQuickAccess'))
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $items = AgendaKelas::where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id)
            ->orderBy('tanggal','desc')
            ->get();
        
        // Get kelas quick access untuk guru yang login
        $user = auth()->user();
        $guru = $user->guru;
        
        if ($guru) {
            $kelasQuickAccess = DB::table('jadwal_kbm')
                ->join('kelas', 'jadwal_kbm.kelas_id', '=', 'kelas.id')
                ->leftJoin('guru', 'kelas.wali_kelas_id', '=', 'guru.id')
                ->where('jadwal_kbm.guru_id', $guru->id)
                ->select('kelas.id', 'kelas.nama_kelas', 'guru.nama as wali_nama')
                ->distinct()
                ->get();
        } else {
            $kelasQuickAccess = collect();
        }
        
        return view('agenda_kelas.index', compact('items', 'kelasQuickAccess'));
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
        ]);

        // Hapus agenda_id dari data jika ada (jika dari form show)
        $agendaId = $data['agenda_id'] ?? null;
        unset($data['agenda_id']);

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

        if ($agendaId) {
            // Update existing agenda
            $agenda = AgendaKelas::findOrFail($agendaId);
            $agenda->update($data);
            $message = 'Agenda kelas berhasil diperbarui';
        } else {
            // Create new agenda
            AgendaKelas::create($data);
            $message = 'Agenda kelas ditambahkan';
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
}
