<?php

namespace App\Http\Controllers;

use App\Models\MateriPembelajaran;
use App\Models\RencanaPembelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SiswaPembelajaranController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole('Siswa') || !$user->siswa) {
            abort(403, 'Akses ditolak. Hanya siswa yang dapat mengakses halaman ini.');
        }

        $siswa = $user->siswa;
        $kelasId = $siswa->kelas_id;

        $rencanaIds = RencanaPembelajaran::where('kelas_id', $kelasId)
            ->where('status', 'published')
            ->pluck('id');

        $query = MateriPembelajaran::whereIn('rencana_pembelajaran_id', $rencanaIds)
            ->where('status', 'published')
            ->with(['rencanaPembelajaran.mataPelajaran', 'rencanaPembelajaran.jadwalKbm'])
            ->orderBy('created_at', 'desc');

        $items = $query->paginate(15);

        return view('siswa.pembelajaran.materi', compact('items', 'siswa'));
    }

    public function show($id)
    {
        $user = Auth::user();

        if (!$user->hasRole('Siswa') || !$user->siswa) {
            abort(403, 'Akses ditolak. Hanya siswa yang dapat mengakses halaman ini.');
        }

        $siswa = $user->siswa;
        $kelasId = $siswa->kelas_id;

        $materi = MateriPembelajaran::where('id', $id)
            ->where('status', 'published')
            ->with(['rencanaPembelajaran.mataPelajaran', 'rencanaPembelajaran.jadwalKbm'])
            ->firstOrFail();

        if ($materi->rencanaPembelajaran->kelas_id !== $kelasId) {
            abort(403, 'Anda tidak memiliki akses ke materi ini.');
        }

        return view('siswa.pembelajaran.show', compact('materi', 'siswa'));
    }
}
