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
    public function index()
    {
        $items = JamBelajar::orderByDay()->get();
        $groupedByDay = $items->groupBy('hari');
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        return view('jam_belajar.index', compact('groupedByDay', 'days'));
    }

    public function create()
    {
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        return view('jam_belajar.create', compact('days'));
    }

    public function store(Request $request)
    {
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
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        return view('jam_belajar.edit', ['item' => $jamBelajar, 'days' => $days]);
    }

    public function update(Request $request, JamBelajar $jamBelajar)
    {
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
        $jamBelajar->delete();
        return redirect()->route('jam_belajar.index')->with('success','Jam belajar dihapus');
    }

    public function destroyAll()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        JamBelajar::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        return redirect()->route('jam_belajar.index')->with('success','Semua pengaturan jam KBM telah dihapus');
    }

    public function export()
    {
        return Excel::download(new JamBelajarExport, 'Jam_KBM_' . date('Y-m-d') . '.xlsx');
    }

    public function templateDownload()
    {
        return Excel::download(new JamBelajarTemplateExport, 'Template_Jam_KBM.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $import = new JamBelajarImport();
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
}

