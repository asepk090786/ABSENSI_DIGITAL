<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NilaiController extends Controller
{
    public function index()
    {
        $items = DB::table('nilai')->orderBy('created_at','desc')->get();
        return view('nilai.index', compact('items'));
    }
}
