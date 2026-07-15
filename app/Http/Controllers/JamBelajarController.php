<?php

namespace App\Http\Controllers;

use App\Models\JamBelajar;
use App\Exports\JamBelajarExport;
use App\Exports\JamBelajarTemplateExport;
use App\Imports\JamBelajarImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class JamBelajarController extends Controller
{
    protected function authorizeJamBelajarManagement()
    {
        $user = auth()->user();
        if ($user && $user->hasAnyRole(['Siswa','Guru','Guru Mapel','Guru Kelas','Wali Kelas','Guru BK','Guru Piket'])) {
            return redirect()->route('home')->with('error', 'Akses ditolak. Hanya pengelola pusat yang dapat mengatur jam belajar.');
        }

        return null;
    }

    public function index()
    {
        $items = JamBelajar::orderByDay()->get();
        $groupedByDay = $items->groupBy('hari');
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $kegiatanList = \App\Models\Kegiatan::orderBy('nama_kegiatan')->get();
        return view('jam_belajar.index', compact('groupedByDay', 'days', 'kegiatanList'));
    }

    public function create()
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $kegiatanList = \App\Models\Kegiatan::orderBy('nama_kegiatan')->get();
        return view('jam_belajar.create', compact('days', 'kegiatanList'));
    }

    public function store(Request $request)
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        $data = $request->validate([
            'hari' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'urutan' => 'required|integer|min:1',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'jenis' => 'required|string',
        ]);

        // Check if session already exists for this day and order
        $exists = JamBelajar::where('hari', $data['hari'])
            ->where('urutan', $data['urutan'])
            ->exists();
        if ($exists) {
            return back()->withErrors("Sesi ke-{$data['urutan']} sudah ada untuk hari {$data['hari']}");
        }

        JamBelajar::create($data);

        return redirect()->route('jam_belajar.index')->with('success','Jam belajar ditambah');
    }

    public function edit(JamBelajar $jamBelajar)
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $kegiatanList = \App\Models\Kegiatan::orderBy('nama_kegiatan')->get();
        return view('jam_belajar.edit', ['item' => $jamBelajar, 'days' => $days, 'kegiatanList' => $kegiatanList]);
    }

    public function update(Request $request, JamBelajar $jamBelajar)
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        $data = $request->validate([
            'hari' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'urutan' => 'required|integer|min:1',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'jenis' => 'required|string',
        ]);

        // Check if another session with same day/order exists
        $exists = JamBelajar::where('hari', $data['hari'])
            ->where('urutan', $data['urutan'])
            ->where('id', '!=', $jamBelajar->id)
            ->exists();
        if ($exists) {
            return back()->withErrors("Sesi ke-{$data['urutan']} sudah ada untuk hari {$data['hari']}");
        }

        $jamBelajar->update($data);

        return redirect()->route('jam_belajar.index')->with('success','Jam belajar diperbarui');
    }

    public function destroy(JamBelajar $jamBelajar)
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        $jamBelajar->delete();
        return redirect()->route('jam_belajar.index')->with('success','Jam belajar dihapus');
    }

    public function destroyAll()
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        JamBelajar::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        return redirect()->route('jam_belajar.index')->with('success','Semua pengaturan jam KBM telah dihapus');
    }

    public function insertSlot(Request $request)
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        $data = $request->validate([
            'hari' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'urutan' => 'required|integer|min:1',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'jenis' => 'required|string',
        ]);

        DB::transaction(function() use ($data) {
            // Geser urutan jam belajar yang sama atau lebih besar (desc supaya tidak bentrok)
            $toShiftJam = JamBelajar::where('hari', $data['hari'])
                ->where('urutan', '>=', $data['urutan'])
                ->orderBy('urutan', 'desc')
                ->lockForUpdate()
                ->get();

            foreach ($toShiftJam as $item) {
                $item->update(['urutan' => $item->urutan + 1]);
            }

            // Geser jadwal KBM yang sudah ada di jam_ke yang sama/higher (desc per jam_ke untuk hindari duplikat)
            $toShiftJadwal = DB::table('jadwal_kbm')
                ->where('hari', $data['hari'])
                ->where('jam_ke', '>=', $data['urutan'])
                ->orderBy('jam_ke', 'desc')
                ->lockForUpdate()
                ->get();

            foreach ($toShiftJadwal as $row) {
                DB::table('jadwal_kbm')->where('id', $row->id)->update(['jam_ke' => $row->jam_ke + 1]);
            }

            // Tambah slot baru di posisi yang disisipkan
            JamBelajar::create($data);
        });

        return redirect()->route('jam_belajar.index')
            ->with('success', "Slot baru disisipkan di jam ke-{$data['urutan']} untuk {$data['hari']}. Jadwal KBM digeser otomatis.");
    }

    public function export()
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        return Excel::download(new JamBelajarExport, 'Jam_KBM_' . date('Y-m-d') . '.xlsx');
    }

    public function templateDownload()
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        return Excel::download(new JamBelajarTemplateExport, 'Template_Jam_KBM.xlsx');
    }

    public function import(Request $request)
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $updateMode = $request->input('replace') == '1';
            $import = new \App\Imports\JamBelajarImport($updateMode);
            Excel::import($import, $request->file('file'));

            $errors = $import->getErrors();
            $successCount = $import->getSuccessCount();

            if (!empty($errors)) {
                return back()->with('import_errors', $errors)->with('successCount', $successCount)->with('warning', 'Ada kesalahan saat mengimport');
            }

            return back()->with('success', "Berhasil mengimport $successCount jam KBM");
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal mengimport file: ' . $e->getMessage()]);
        }
    }

    /**
     * Copy jam dari satu hari ke hari lain
     */
    public function copyPattern(Request $request)
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        $request->validate([
            'from_day' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'to_days' => 'required|array|min:1',
            'to_days.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'replace' => 'boolean' // true = replace existing, false = skip if exists
        ]);

        $fromDay = $request->input('from_day');
        $toDays = $request->input('to_days');
        $replace = $request->input('replace', false);

        // Get jam from source day
        $sourceJams = JamBelajar::where('hari', $fromDay)
            ->orderBy('urutan')
            ->get();

        if ($sourceJams->isEmpty()) {
            return back()->with('error', "Tidak ada jam belajar untuk hari $fromDay");
        }

        DB::transaction(function() use ($sourceJams, $toDays, $replace) {
            foreach ($toDays as $toDay) {
                if ($replace) {
                    // Delete existing jams for this day
                    JamBelajar::where('hari', $toDay)->delete();
                }

                // Copy jams from source day
                foreach ($sourceJams as $sourceJam) {
                    $exists = JamBelajar::where('hari', $toDay)
                        ->where('urutan', $sourceJam->urutan)
                        ->exists();

                    if (!$exists) {
                        JamBelajar::create([
                            'hari' => $toDay,
                            'urutan' => $sourceJam->urutan,
                            'jam_mulai' => $sourceJam->jam_mulai,
                            'jam_selesai' => $sourceJam->jam_selesai,
                            'jenis' => $sourceJam->jenis,
                        ]);
                    }
                }
            }
        });

        $daysList = implode(', ', $toDays);
        return back()->with('success', "Pola dari $fromDay berhasil di-copy ke: $daysList");
    }

    /**
     * Save jam pattern sebagai template
     */
    public function saveAsPattern(Request $request)
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        $request->validate([
            'source_day' => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'nama_pola' => 'required|string|unique:jam_belajar_pola,nama_pola|max:100',
            'deskripsi' => 'nullable|string|max:500',
        ]);

        $sourceDay = $request->input('source_day');
        $namaPola = $request->input('nama_pola');
        $deskripsi = $request->input('deskripsi');

        // Get jam data from source day
        $jamData = JamBelajar::where('hari', $sourceDay)
            ->orderBy('urutan')
            ->get()
            ->map(function($jam) {
                return [
                    'urutan' => $jam->urutan,
                    'jam_mulai' => $jam->jam_mulai,
                    'jam_selesai' => $jam->jam_selesai,
                    'jenis' => $jam->jenis,
                ];
            })
            ->toArray();

        if (empty($jamData)) {
            return back()->with('error', "Tidak ada jam belajar untuk hari $sourceDay");
        }

        \App\Models\JamBelajarPola::create([
            'nama_pola' => $namaPola,
            'deskripsi' => $deskripsi,
            'jam_data' => $jamData,
            'is_active' => true,
        ]);

        return back()->with('success', "Pola '$namaPola' berhasil disimpan");
    }

    /**
     * Apply saved pattern ke hari tertentu
     */
    public function applyPattern(Request $request)
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        $request->validate([
            'pola_id' => 'required|exists:jam_belajar_pola,id',
            'to_days' => 'required|array|min:1',
            'to_days.*' => 'string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'replace' => 'boolean'
        ]);

        $pola = \App\Models\JamBelajarPola::find($request->input('pola_id'));
        $toDays = $request->input('to_days');
        $replace = $request->input('replace', false);
        $jamData = $pola->jam_data;

        DB::transaction(function() use ($jamData, $toDays, $replace) {
            foreach ($toDays as $toDay) {
                if ($replace) {
                    JamBelajar::where('hari', $toDay)->delete();
                }

                foreach ($jamData as $jam) {
                    $exists = JamBelajar::where('hari', $toDay)
                        ->where('urutan', $jam['urutan'])
                        ->exists();

                    if (!$exists) {
                        JamBelajar::create([
                            'hari' => $toDay,
                            'urutan' => $jam['urutan'],
                            'jam_mulai' => $jam['jam_mulai'],
                            'jam_selesai' => $jam['jam_selesai'],
                            'jenis' => $jam['jenis'],
                        ]);
                    }
                }
            }
        });

        $daysList = implode(', ', $toDays);
        return back()->with('success', "Pola '{$pola->nama_pola}' berhasil diterapkan ke: $daysList");
    }

    /**
     * Show patterns management page
     */
    public function patterns()
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        $polas = \App\Models\JamBelajarPola::orderBy('nama_pola')->get();
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        
        return view('jam_belajar.patterns', compact('polas', 'days'));
    }

    /**
     * Delete pattern
     */
    public function deletePattern(\App\Models\JamBelajarPola $pola)
    {
        if ($redirect = $this->authorizeJamBelajarManagement()) {
            return $redirect;
        }

        $namaPola = $pola->nama_pola;
        $pola->delete();

        return back()->with('success', "Pola '$namaPola' berhasil dihapus");
    }
}

