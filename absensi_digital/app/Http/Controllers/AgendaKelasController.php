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
            return view('agenda_kelas.index', compact('items'))
                ->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $items = AgendaKelas::where('tahun_ajaran_id', $tahun->id)
            ->where('semester_id', $semester->id)
            ->orderBy('tanggal','desc')
            ->get();
        return view('agenda_kelas.index', compact('items'));
    }

    public function create()
    {
        $jam = DB::table('jam_belajar')->get();
        $kelas = DB::table('kelas')->get();
        $guru = DB::table('guru')->get();
        return view('agenda_kelas.create', compact('jam','kelas','guru'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => 'required|integer',
            'guru_id' => 'required|integer',
            'jam_belajar_id' => 'required|integer',
            'tanggal' => 'required|date',
            'kegiatan' => 'nullable|string',
        ]);

        $tahun = DB::table('tahun_ajaran')->where('is_active',1)->first();
        $semester = DB::table('semester')->where('is_active',1)->first();

        if (! $tahun || ! $semester) {
            return back()->withErrors('Tahun ajaran atau semester belum di-set aktif.');
        }

        $data['tahun_ajaran_id'] = $tahun->id;
        $data['semester_id'] = $semester->id;

        AgendaKelas::create($data);

        return redirect()->route('agenda_kelas.index')->with('success','Agenda kelas ditambahkan');
    }
}
