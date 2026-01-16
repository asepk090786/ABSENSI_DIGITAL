<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NilaiController extends Controller
{
    public function index()
    {
        $items = DB::table('nilai_harian')
            ->join('siswa', 'nilai_harian.siswa_id', '=', 'siswa.id')
            ->join('mata_pelajaran', 'nilai_harian.mapel_id', '=', 'mata_pelajaran.id')
            ->leftJoin('kelas', 'nilai_harian.kelas_id', '=', 'kelas.id')
            ->leftJoin('komponen_nilai', 'nilai_harian.komponen_id', '=', 'komponen_nilai.id')
            ->select(
                'nilai_harian.*',
                'siswa.nama as nama_siswa',
                'mata_pelajaran.nama_mapel',
                'kelas.nama_kelas',
                'komponen_nilai.nama_komponen'
            )
            ->orderBy('nilai_harian.created_at','desc')
            ->get();
        return view('nilai.index', compact('items'));
    }
}
