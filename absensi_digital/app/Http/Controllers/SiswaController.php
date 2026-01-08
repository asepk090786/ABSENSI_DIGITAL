<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    public function index()
    {
        $items = DB::table('siswa')->orderBy('nama')->get();
        return view('siswa.index', compact('items'));
    }
}
