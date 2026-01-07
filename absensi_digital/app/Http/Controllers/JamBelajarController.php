<?php

namespace App\Http\Controllers;

use App\Models\JamBelajar;
use Illuminate\Http\Request;

class JamBelajarController extends Controller
{
    public function index()
    {
        $items = JamBelajar::orderBy('hari')->orderBy('jam_mulai')->get();
        return view('jam_belajar.index', compact('items'));
    }

    public function create()
    {
        return view('jam_belajar.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hari' => 'required|string',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'jenis' => 'required|string',
        ]);

        JamBelajar::create($data);

        return redirect()->route('jam_belajar.index')->with('success','Jam belajar ditambah');
    }

    public function edit(JamBelajar $jamBelajar)
    {
        return view('jam_belajar.edit', ['item' => $jamBelajar]);
    }

    public function update(Request $request, JamBelajar $jamBelajar)
    {
        $data = $request->validate([
            'hari' => 'required|string',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'jenis' => 'required|string',
        ]);

        $jamBelajar->update($data);

        return redirect()->route('jam_belajar.index')->with('success','Jam belajar diperbarui');
    }

    public function destroy(JamBelajar $jamBelajar)
    {
        $jamBelajar->delete();
        return redirect()->route('jam_belajar.index')->with('success','Jam belajar dihapus');
    }
}
