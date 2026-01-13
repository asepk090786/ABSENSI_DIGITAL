<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class UpdateController extends Controller
{
    public function index()
    {
        return view('maintenance.update');
    }

    public function run(Request $request)
    {
        return redirect()
            ->route('maintenance.update.index')
            ->with('error', 'Fitur update GitHub dimatikan karena program masih dalam tahap pengembangan.');
    }
}
