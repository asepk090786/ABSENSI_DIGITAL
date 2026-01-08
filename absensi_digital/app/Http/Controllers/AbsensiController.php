<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    public function index()
    {
        $items = DB::table('absensi')->orderBy('tanggal','desc')->get();
        return view('absensi.index', compact('items'));
    }
}
