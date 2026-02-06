<?php

namespace App\Http\Controllers;

use App\Models\Ekstrakurikuler;
use App\Models\Guru;
use Illuminate\Http\Request;

class EkstrakurikulerController extends Controller
{
    public function index()
    {
        $items = Ekstrakurikuler::with('pembina')
            ->orderBy('nama_ekskul')
            ->get();

        return view('ekstrakurikuler.index', compact('items'));
    }

    public function create()
    {
        $pembina = Guru::whereHas('user', function ($query) {
            $query->whereHas('roles', function ($q) {
                $q->where('role_name', 'Pembina');
            })->orWhereHas('role', function ($q) {
                $q->where('role_name', 'Pembina');
            });
        })->orderBy('nama')->get();

        return view('ekstrakurikuler.create', compact('pembina'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_ekskul' => 'required|string|max:255|unique:ekstrakurikuler,nama_ekskul',
            'deskripsi' => 'nullable|string',
            'pembina_id' => 'required|exists:guru,id',
        ]);

        Ekstrakurikuler::create($validated);

        return redirect()->route('ekstrakurikuler.index')->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }
}
