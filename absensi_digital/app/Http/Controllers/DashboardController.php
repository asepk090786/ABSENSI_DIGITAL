<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Be defensive: tests may run on a DB without all tables migrated
        $guru = \Illuminate\Support\Facades\Schema::hasTable('guru') ? DB::table('guru')->count() : 0;
        $siswa = \Illuminate\Support\Facades\Schema::hasTable('siswa') ? DB::table('siswa')->count() : 0;
        $absensi = \Illuminate\Support\Facades\Schema::hasTable('absensi') ? DB::table('absensi')->count() : 0;

        return view('home', compact('guru','siswa','absensi'));
    }
}
