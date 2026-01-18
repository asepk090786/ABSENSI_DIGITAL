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

    public function showTahunAjaran(TahunAjaran $tahunAjaran)
    {
        $semesters = $tahunAjaran->semesters()->get();
        return view('setting.tahun_ajaran_show', compact('tahunAjaran','semesters'));
    }

    public function editTahunAjaran(TahunAjaran $tahunAjaran)
    {
        return view('setting.tahun_ajaran_edit', compact('tahunAjaran'));
    }

    public function storeTahunAjaran(Request $request)
    {
        $data = $request->validate([
            'nama_tahun' => 'required|string|unique:tahun_ajaran,nama_tahun',
        ]);

        TahunAjaran::create($data);
        return redirect()->route('setting.tahun_ajaran')->with('success', 'Tahun ajaran ditambahkan');
    }

    public function updateTahunAjaran(Request $request, TahunAjaran $tahunAjaran)
    {
        $data = $request->validate([
            'nama_tahun' => 'required|string|unique:tahun_ajaran,nama_tahun,' . $tahunAjaran->id,
        ]);

        $tahunAjaran->update($data);
        return redirect()->route('setting.tahun_ajaran')->with('success', 'Tahun ajaran diperbarui');
    }

    public function activateTahunAjaran(TahunAjaran $tahunAjaran)
    {
        DB::table('tahun_ajaran')->update(['is_active' => 0]);
        $tahunAjaran->update(['is_active' => 1]);

        return back()->with('success', 'Tahun ajaran ' . $tahunAjaran->nama_tahun . ' diaktifkan');
    }

    public function deactivateTahunAjaran(TahunAjaran $tahunAjaran)
    {
        $tahunAjaran->update(['is_active' => 0]);
        return back()->with('success', 'Tahun ajaran dinonaktifkan');
    }

    public function destroyTahunAjaran(TahunAjaran $tahunAjaran)
    {
        try {
            $tahunAjaran->delete();
            return redirect()->route('setting.tahun_ajaran')->with('success', 'Tahun ajaran dihapus');
        } catch (\Throwable $e) {
            return back()->withErrors('Tidak dapat menghapus: masih ada data terkait.');
        }
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
            'nama_semester' => 'required|string|in:Semester 1 (Ganjil),Semester 2 (Genap)',
        ]);

        $exists = Semester::where('tahun_ajaran_id', $data['tahun_ajaran_id'])
            ->where('nama_semester', $data['nama_semester'])
            ->exists();
        if ($exists) {
            return back()->withErrors('Semester sudah ada untuk tahun ajaran ini');
        }

        Semester::create($data);
        return redirect()->route('setting.semester')->with('success', 'Semester ditambahkan');
    }

    public function activateSemester(Semester $semester)
    {
        DB::table('semester')->where('tahun_ajaran_id', $semester->tahun_ajaran_id)->update(['is_active' => 0]);
        $semester->update(['is_active' => 1]);

        return back()->with('success', 'Semester ' . $semester->nama_semester . ' diaktifkan');
    }

    public function showSemester(Semester $semester)
    {
        return view('setting.semester_show', compact('semester'));
    }

    public function editSemester(Semester $semester)
    {
        $active_tahun = TahunAjaran::find($semester->tahun_ajaran_id);
        return view('setting.semester_edit', compact('semester','active_tahun'));
    }

    public function updateSemester(Request $request, Semester $semester)
    {
        $data = $request->validate([
            'nama_semester' => 'required|string|in:Semester 1 (Ganjil),Semester 2 (Genap)',
        ]);

        $exists = Semester::where('tahun_ajaran_id', $semester->tahun_ajaran_id)
            ->where('nama_semester', $data['nama_semester'])
            ->where('id', '!=', $semester->id)
            ->exists();
        if ($exists) {
            return back()->withErrors('Semester sudah ada untuk tahun ajaran ini');
        }

        $semester->update($data);
        return redirect()->route('setting.semester')->with('success','Semester diperbarui');
    }

    public function deactivateSemester(Semester $semester)
    {
        $semester->update(['is_active' => 0]);
        return back()->with('success','Semester dinonaktifkan');
    }

    public function destroySemester(Semester $semester)
    {
        try {
            $semester->delete();
            return redirect()->route('setting.semester')->with('success','Semester dihapus');
        } catch (\Throwable $e) {
            return back()->withErrors('Tidak dapat menghapus: masih ada data terkait.');
        }
    }

    public function header()
    {
        $sekolah = \App\Models\Sekolah::first();
        return view('setting.header', compact('sekolah'));
    }

    public function updateHeader(Request $request)
    {
        \Log::info('updateHeader called');
        \Log::info('Request data:', $request->all());
        \Log::info('Has logo_header_kiri file:', ['has' => $request->hasFile('logo_header_kiri')]);
        \Log::info('Has logo file:', ['has' => $request->hasFile('logo')]);
        
        $validated = $request->validate([
            'header_html' => 'nullable|string',
            'logo_header_kiri' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // Header text lines (HTML from Summernote)
            'header_line1' => 'nullable|string',
            'header_line1_spacing' => 'nullable|numeric|min:0.1|max:5',
            'header_line2' => 'nullable|string',
            'header_line2_spacing' => 'nullable|numeric|min:0.1|max:5',
            'header_line3' => 'nullable|string',
            'header_line3_spacing' => 'nullable|numeric|min:0.1|max:5',
            'header_line4' => 'nullable|string',
            'header_line4_spacing' => 'nullable|numeric|min:0.1|max:5',
        ]);

        try {
            \Log::info('Validation passed');
            $sekolah = \App\Models\Sekolah::first();
            
            if (!$sekolah) {
                $sekolah = new \App\Models\Sekolah();
            }

            $sekolah->header_html = $validated['header_html'] ?? null;

            // Save header text lines as HTML from Summernote
            $sekolah->header_line1 = $validated['header_line1'] ?? null;
            $sekolah->header_line1_spacing = $validated['header_line1_spacing'] ?? 1.0;
            $sekolah->header_line2 = $validated['header_line2'] ?? null;
            $sekolah->header_line2_spacing = $validated['header_line2_spacing'] ?? 1.0;
            $sekolah->header_line3 = $validated['header_line3'] ?? null;
            $sekolah->header_line3_spacing = $validated['header_line3_spacing'] ?? 1.0;
            $sekolah->header_line4 = $validated['header_line4'] ?? null;
            $sekolah->header_line4_spacing = $validated['header_line4_spacing'] ?? 1.0;

            // Handle logo_header_kiri
            if ($request->hasFile('logo_header_kiri')) {
                \Log::info('Processing logo_header_kiri upload');
                if ($sekolah->logo_header_kiri && \Illuminate\Support\Facades\Storage::disk('public')->exists($sekolah->logo_header_kiri)) {
                    \Log::info('Deleting old logo_header_kiri: ' . $sekolah->logo_header_kiri);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($sekolah->logo_header_kiri);
                }
                try {
                    $file = $request->file('logo_header_kiri');
                    $path = $file->store('logos', 'public');
                    \Log::info('Logo kiri stored at: ' . $path);
                    $sekolah->logo_header_kiri = $path;
                } catch (\Exception $ex) {
                    \Log::error('Error storing logo_header_kiri: ' . $ex->getMessage());
                    throw $ex;
                }
            }

            // Handle logo (school logo)
            if ($request->hasFile('logo')) {
                \Log::info('Processing logo upload');
                if ($sekolah->logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($sekolah->logo)) {
                    \Log::info('Deleting old logo: ' . $sekolah->logo);
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($sekolah->logo);
                }
                try {
                    $file = $request->file('logo');
                    $path = $file->store('logos', 'public');
                    \Log::info('Logo stored at: ' . $path);
                    $sekolah->logo = $path;
                } catch (\Exception $ex) {
                    \Log::error('Error storing logo: ' . $ex->getMessage());
                    throw $ex;
                }
            }

            \Log::info('Saving sekolah record', ['logo' => $sekolah->logo, 'logo_header_kiri' => $sekolah->logo_header_kiri]);
            $sekolah->save();
            \Log::info('Sekolah saved successfully');

            return redirect()->route('setting.header')
                ->with('success', 'Header berhasil disimpan');
        } catch (\Exception $e) {
            \Log::error('Error updating header: ' . $e->getMessage());
            return back()
                ->with('error', 'Gagal menyimpan pengaturan: ' . $e->getMessage());
        }
    }}