<?php

namespace App\Http\Controllers;

use App\Models\TahunAjaran;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function index()
    {
        $tahuns = TahunAjaran::all();
        $active_tahun = TahunAjaran::where('is_active', 1)->first();
        $active_semester = $active_tahun ? Semester::where('tahun_ajaran_id', $active_tahun->id)->where('is_active', 1)->first() : null;

        return view('setting.index', compact('tahuns', 'active_tahun', 'active_semester'));
    }

    public function tahunAjaran()
    {
        $tahuns = TahunAjaran::all();
        return view('setting.tahun_ajaran', compact('tahuns'));
    }

    public function createTahunAjaran()
    {
        return view('setting.tahun_ajaran_create');
    }

    public function storeTahunAjaran(Request $request)
    {
        $data = $request->validate([
            'nama_tahun' => 'required|string|unique:tahun_ajaran,nama_tahun',
        ]);

        TahunAjaran::create($data);
        return redirect()->route('setting.tahun_ajaran')->with('success', 'Tahun ajaran ditambahkan');
    }

    public function activateTahunAjaran(TahunAjaran $tahunAjaran)
    {
        DB::table('tahun_ajaran')->update(['is_active' => 0]);
        $tahunAjaran->update(['is_active' => 1]);

        return back()->with('success', 'Tahun ajaran ' . $tahunAjaran->nama_tahun . ' diaktifkan');
    }

    public function semester()
    {
        $active_tahun = TahunAjaran::where('is_active', 1)->first();
        $semesters = $active_tahun ? Semester::where('tahun_ajaran_id', $active_tahun->id)->get() : [];

        return view('setting.semester', compact('semesters', 'active_tahun'));
    }

    public function createSemester()
    {
        $active_tahun = TahunAjaran::where('is_active', 1)->first();

        if (! $active_tahun) {
            return back()->withErrors('Pilih tahun ajaran aktif dulu');
        }

        return view('setting.semester_create', compact('active_tahun'));
    }

    public function storeSemester(Request $request)
    {
        $data = $request->validate([
            'tahun_ajaran_id' => 'required|integer|exists:tahun_ajaran,id',
            'nama_semester' => 'required|string',
        ]);

        Semester::create($data);
        return redirect()->route('setting.semester')->with('success', 'Semester ditambahkan');
    }

    public function activateSemester(Semester $semester)
    {
        DB::table('semester')->where('tahun_ajaran_id', $semester->tahun_ajaran_id)->update(['is_active' => 0]);
        $semester->update(['is_active' => 1]);

        return back()->with('success', 'Semester ' . $semester->nama_semester . ' diaktifkan');
    }
}
