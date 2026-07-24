<?php

namespace App\Http\Controllers;

use App\Models\Pengembangan;
use App\Models\PengembanganPeserta;
use App\Models\PengembanganSertifikat;
use App\Models\PengembanganSertifikat as Sertifikat;
use App\Models\Pengembangan as Peng;
use App\Models\PengembanganSertifikat as PengSert;
use App\Models\PengembanganPeserta as Peserta;
use App\Models\Pengembangan as PengembanganModel;
use App\Models\PengembanganSertifikat as PengembanganSertifikatModel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PDF;
use Storage;

class PengembanganController extends Controller
{
    public function index()
    {
        $items = Pengembangan::orderBy('tanggal_mulai','desc')->paginate(20);
        return view('pengembangan.index', compact('items'));
    }

    public function create()
    {
        $gurus = \DB::table('guru')->select('id','nama')->get();
        $siswas = \DB::table('siswa')->select('id','nama')->get();
        return view('pengembangan.create', compact('gurus','siswas'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nama_kegiatan'=>'required|string',
            'jenis_kegiatan'=>'nullable|string',
            'deskripsi'=>'nullable|string',
            'pemateri_guru_ids'=>'nullable|array',
            'pemateri_names'=>'nullable|string',
            'tanggal_mulai'=>'nullable|date',
            'tanggal_selesai'=>'nullable|date',
        ]);

        // Build pemateri array from selected guru ids and extra names
        $pemateri = [];
        $guruIds = $r->input('pemateri_guru_ids', []);
        if (!empty($guruIds)) {
            $rows = \DB::table('guru')->whereIn('id', $guruIds)->pluck('nama')->all();
            $pemateri = array_merge($pemateri, $rows);
        }
        $extra = $r->input('pemateri_names');
        if ($extra) {
            $parts = array_filter(array_map('trim', explode(',', $extra)));
            $pemateri = array_merge($pemateri, $parts);
        }

        $p = Pengembangan::create([
            'nama_kegiatan' => $data['nama_kegiatan'],
            'jenis_kegiatan' => $data['jenis_kegiatan'] ?? null,
            'deskripsi' => $data['deskripsi'] ?? null,
            'pemateri' => $pemateri,
            'tanggal_mulai' => $data['tanggal_mulai'] ?? null,
            'tanggal_selesai' => $data['tanggal_selesai'] ?? null,
        ]);

        // participants arrays: guru_ids[], siswa_ids[]
        foreach (($r->input('guru_ids',[]) ) as $gid) {
            PengembanganPeserta::create(['pengembangan_id'=>$p->id,'peserta_type'=>'guru','peserta_id'=>$gid]);
        }
        foreach (($r->input('siswa_ids',[]) ) as $sid) {
            PengembanganPeserta::create(['pengembangan_id'=>$p->id,'peserta_type'=>'siswa','peserta_id'=>$sid]);
        }

        return redirect()->route('pengembangan.index')->with('success','Kegiatan dibuat');
    }

    public function show($id)
    {
        $item = Pengembangan::with('peserta')->findOrFail($id);
        return view('pengembangan.show', compact('item'));
    }

    public function generateCertificates($id)
    {
        $item = Pengembangan::with('peserta')->findOrFail($id);
        $storagePath = 'public/certificates';
        Storage::makeDirectory($storagePath);

        foreach ($item->peserta as $p) {
            // Only generate for guru type for now
            $barcode = (string) Str::uuid();
            $name = ($p->peserta_type === 'guru') ? \DB::table('guru')->where('id',$p->peserta_id)->value('nama') : \DB::table('siswa')->where('id',$p->peserta_id)->value('nama');
            $html = view('pengembangan.certificate_template', ['name'=>$name,'kegiatan'=>$item,'barcode'=>$barcode])->render();
            $pdf = PDF::loadHTML($html)->setPaper('a4','landscape');
            $fileName = "pengembangan_{$item->id}_{$p->peserta_type}_{$p->peserta_id}_".time().".pdf";
            $filePath = $storagePath.'/'.$fileName;
            Storage::put($filePath, $pdf->output());

            PengembanganSertifikat::create([
                'pengembangan_id'=>$item->id,
                'peserta_type'=>$p->peserta_type,
                'peserta_id'=>$p->peserta_id,
                'file_path'=>str_replace('public/','storage/',$filePath),
                'barcode'=>$barcode,
            ]);
        }

        return redirect()->route('pengembangan.show', $id)->with('success','Sertifikat dibuat untuk peserta');
    }

    public function myCertificates()
    {
        $user = auth()->user();
        $certs = [];
        if ($user->guru_id) {
            $certs = PengembanganSertifikat::where('peserta_type','guru')->where('peserta_id',$user->guru_id)->get();
        }
        return view('pengembangan.certificates', compact('certs'));
    }

    public function downloadCertificate($id)
    {
        $cert = PengembanganSertifikat::findOrFail($id);
        $path = storage_path('app/'.str_replace('storage/','',$cert->file_path));
        if (!file_exists($path)) abort(404);
        return response()->download($path);
    }

    public function verify($code)
    {
        $cert = PengembanganSertifikat::where('barcode',$code)->first();
        if (!$cert) return view('pengembangan.verify', ['valid'=>false]);
        return view('pengembangan.verify', ['valid'=>true,'cert'=>$cert]);
    }
}
