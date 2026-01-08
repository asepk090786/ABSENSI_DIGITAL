<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\AbsensiKelas;

class AbsensiController extends Controller
{
    public function index()
    {
        $items = AbsensiKelas::with(['kelas', 'guru', 'jamBelajar', 'tahunAjaran', 'semester', 'absensiSiswa'])
            ->orderBy('tanggal', 'desc')
            ->get();
        return view('absensi.index', compact('items'));
    }
}
