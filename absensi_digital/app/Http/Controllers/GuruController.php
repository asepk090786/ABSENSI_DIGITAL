<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuruController extends Controller
{
    public function index()
    {
        $items = DB::table('guru')->orderBy('nama')->get();
        return view('guru.index', compact('items'));
    }
}
