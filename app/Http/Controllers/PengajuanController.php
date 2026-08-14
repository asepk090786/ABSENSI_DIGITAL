<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * Pengajuan Controller
 * 
 * Mengelola pengajuan administrasi PTK seperti cuti, izin, pengajuan kenaikan gaji, dll.
 * Fitur ini merupakan placeholder awal untuk workflow pengajuan administratif.
 */
class PengajuanController extends Controller
{
    public function index()
    {
        return view('pengajuan.index');
    }

    public function create()
    {
        return view('pengajuan.create');
    }

    public function store(Request $request)
    {
        // Placeholder untuk implementasi bisnis logic di masa depan
        return redirect()->route('pengajuan.index')
            ->with('success', 'Pengajuan berhasil disimpan');
    }

    public function show($id)
    {
        return view('pengajuan.show');
    }

    public function edit($id)
    {
        return view('pengajuan.edit');
    }

    public function update(Request $request, $id)
    {
        // Placeholder untuk implementasi bisnis logic di masa depan
        return redirect()->route('pengajuan.index')
            ->with('success', 'Pengajuan berhasil diperbarui');
    }

    public function destroy($id)
    {
        // Placeholder untuk implementasi bisnis logic di masa depan
        return redirect()->route('pengajuan.index')
            ->with('success', 'Pengajuan berhasil dihapus');
    }
}
