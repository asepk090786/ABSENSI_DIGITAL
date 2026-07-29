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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpWord\IOFactory as PhpWordIOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Html as PhpWordHtml;
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
        $jenisList = \App\Models\JenisKegiatan::orderBy('nama')->get();
        $kegiatanList = \App\Models\Kegiatan::orderBy('nama_kegiatan')->get();
        return view('pengembangan.create', compact('gurus','siswas','jenisList','kegiatanList'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'nama_kegiatan'=>'required|string',
            'tema_kegiatan'=>'nullable|string',
            'jenis_kegiatan'=>'nullable|exists:jenis_kegiatan,kode',
            'kegiatan_id'=>'nullable|exists:kegiatan,id',
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

        // if kegiatan_id provided, fetch its nama_kegiatan
        $namaKegiatan = $data['nama_kegiatan'] ?? null;
        if ($r->filled('kegiatan_id')) {
            $namaKegiatan = \App\Models\Kegiatan::where('id', $r->input('kegiatan_id'))->value('nama_kegiatan');
        }

        $p = Pengembangan::create([
            'nama_kegiatan' => $namaKegiatan,
            'tema_kegiatan' => $data['tema_kegiatan'] ?? null,
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
        $templates = \DB::table('pengembangan_sertifikat_templates')->orderBy('nama')->get();
        // resolve participant names
        $participants = $item->peserta->map(function($p){
            $name = $p->peserta_type === 'guru' ? \DB::table('guru')->where('id',$p->peserta_id)->value('nama') : \DB::table('siswa')->where('id',$p->peserta_id)->value('nama');
            return [
                'id'=>$p->id,
                'type'=>$p->peserta_type,
                'peserta_id'=>$p->peserta_id,
                'name'=>$name,
            ];
        })->all();

        // existing certificates for this pengembangan
        $certs = PengembanganSertifikat::where('pengembangan_id', $id)->get();
        $certMap = [];
        foreach ($certs as $c) {
            $key = $c->peserta_type . '_' . $c->peserta_id;
            $certMap[$key] = $c; // store model so we can get id/file_path
        }

        return view('pengembangan.show', compact('item','templates','participants','certMap'));
    }

    public function edit($id)
    {
        $item = Pengembangan::with('peserta')->findOrFail($id);
        $gurus = \DB::table('guru')->select('id','nama')->get();
        $siswas = \DB::table('siswa')->select('id','nama')->get();
        $jenisList = \App\Models\JenisKegiatan::orderBy('nama')->get();
        $kegiatanList = \App\Models\Kegiatan::orderBy('nama_kegiatan')->get();
        $templates = \DB::table('pengembangan_sertifikat_templates')->orderBy('nama')->get();
        return view('pengembangan.edit', compact('item','gurus','siswas','jenisList','kegiatanList','templates'));
    }

    public function update(Request $r, $id)
    {
        $item = Pengembangan::with('peserta')->findOrFail($id);

        $data = $r->validate([
            'nama_kegiatan'=>'required|string',
            'tema_kegiatan'=>'nullable|string',
            'jenis_kegiatan'=>'nullable|exists:jenis_kegiatan,kode',
            'kegiatan_id'=>'nullable|exists:kegiatan,id',
            'deskripsi'=>'nullable|string',
            'pemateri_guru_ids'=>'nullable|array',
            'pemateri_names'=>'nullable|string',
            'tanggal_mulai'=>'nullable|date',
            'tanggal_selesai'=>'nullable|date',
        ]);

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

        $namaKegiatan = $data['nama_kegiatan'] ?? null;
        if ($r->filled('kegiatan_id')) {
            $namaKegiatan = \App\Models\Kegiatan::where('id', $r->input('kegiatan_id'))->value('nama_kegiatan');
        }

        $item->update([
            'nama_kegiatan'=>$namaKegiatan,
            'tema_kegiatan'=>$data['tema_kegiatan'] ?? null,
            'jenis_kegiatan'=>$data['jenis_kegiatan'] ?? null,
            'deskripsi'=>$data['deskripsi'] ?? null,
            'pemateri'=>$pemateri,
            'tanggal_mulai'=>$data['tanggal_mulai'] ?? null,
            'tanggal_selesai'=>$data['tanggal_selesai'] ?? null,
        ]);

        // Replace participants
        \DB::table('pengembangan_peserta')->where('pengembangan_id', $item->id)->delete();
        foreach (($r->input('guru_ids',[]) ) as $gid) {
            PengembanganPeserta::create(['pengembangan_id'=>$item->id,'peserta_type'=>'guru','peserta_id'=>$gid]);
        }
        foreach (($r->input('siswa_ids',[]) ) as $sid) {
            PengembanganPeserta::create(['pengembangan_id'=>$item->id,'peserta_type'=>'siswa','peserta_id'=>$sid]);
        }

        return redirect()->route('pengembangan.show', $item->id)->with('success','Kegiatan diperbarui');
    }

    public function destroy($id)
    {
        $item = Pengembangan::findOrFail($id);
        $item->delete();
        return redirect()->route('pengembangan.index')->with('success','Kegiatan dihapus');
    }

    public function generateCertificates($id)
    {
        $item = Pengembangan::with('peserta')->findOrFail($id);
        $storagePath = 'public/certificates';
        Storage::makeDirectory($storagePath);

        $selected = request()->input('participant_ids', []);
        $templateId = request()->input('template_id');
        $template = $templateId ? \DB::table('pengembangan_sertifikat_templates')->where('id', $templateId)->first() : null;
        $outputFormat = $template?->output_format ?? 'pdf';

        $toGenerate = $item->peserta->filter(function($p) use ($selected){
            if (empty($selected)) return true;
            return in_array($p->id, $selected);
        });

        foreach ($toGenerate as $p) {
            $barcode = (string) Str::uuid();
            $name = ($p->peserta_type === 'guru') ? \DB::table('guru')->where('id',$p->peserta_id)->value('nama') : \DB::table('siswa')->where('id',$p->peserta_id)->value('nama');
            $renderedHtml = $this->renderTemplateContent($template, $item, $name, $barcode);

            $pageSize = $template?->page_size ?? 'A4';
            $pageOrientation = $template?->page_orientation ?? 'portrait';
            $fileName = "pengembangan_{$item->id}_{$p->peserta_type}_{$p->peserta_id}_".time().".".$this->resolveExtension($outputFormat);
            $filePath = $storagePath.'/'.$fileName;
            $this->writeCertificateFile($filePath, $renderedHtml, $outputFormat, $pageSize, $pageOrientation);

            PengembanganSertifikat::create([
                'pengembangan_id'=>$item->id,
                'peserta_type'=>$p->peserta_type,
                'peserta_id'=>$p->peserta_id,
                'file_path'=>str_replace('public/','storage/',$filePath),
                'barcode'=>$barcode,
                'template_id'=>$templateId ?: null,
            ]);
        }

        return redirect()->route('pengembangan.show', $id)->with('success','Sertifikat dibuat untuk peserta');
    }

    protected function renderTemplateContent($template, $item, $name, $barcode)
    {
        $html = $template && !empty($template->template_html)
            ? $template->template_html
            : view('pengembangan.certificate_template', ['name'=>$name,'kegiatan'=>$item,'barcode'=>$barcode])->render();

        $replacements = [
            '{{name}}' => e($name),
            '{{kegiatan->nama_kegiatan}}' => e($item->nama_kegiatan ?? ''),
            '{{kegiatan->tema_kegiatan}}' => e($item->tema_kegiatan ?? ''),
            '{{barcode}}' => $barcode,
        ];

        foreach ($replacements as $placeholder => $value) {
            $html = str_replace($placeholder, $value, $html);
        }

        return $html;
    }

    protected function resolveExtension($format)
    {
        return match ($format) {
            'docx' => 'docx',
            'xlsx' => 'xlsx',
            'jpeg' => 'jpeg',
            default => 'pdf',
        };
    }

    protected function writeCertificateFile($filePath, $html, $format, $pageSize = 'A4', $pageOrientation = 'portrait')
    {
        $pageSize = strtolower($pageSize);
        if ($pageSize === 'letter') {
            $paper = 'letter';
        } else {
            $paper = 'a4';
        }

        $orientation = $pageOrientation === 'landscape' ? 'landscape' : 'portrait';

        if ($format === 'docx') {
            $phpWord = new PhpWord();
            $section = $phpWord->addSection();
            PhpWordHtml::addHtml($section, $html);
            $writer = PhpWordIOFactory::createWriter($phpWord, 'Word2007');
            $tmpPath = storage_path('app/temp_certificate.docx');
            $writer->save($tmpPath);
            Storage::put($filePath, file_get_contents($tmpPath));
            @unlink($tmpPath);
            return;
        }

        if ($format === 'xlsx') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', strip_tags($html));
            $writer = new Xlsx($spreadsheet);
            $tmpPath = storage_path('app/temp_certificate.xlsx');
            $writer->save($tmpPath);
            Storage::put($filePath, file_get_contents($tmpPath));
            @unlink($tmpPath);
            return;
        }

        if ($format === 'jpeg') {
            if ($paper === 'letter') {
                $width = $orientation === 'landscape' ? 1500 : 1200;
                $height = $orientation === 'landscape' ? 1200 : 1500;
            } else {
                $width = $orientation === 'landscape' ? 1600 : 1200;
                $height = $orientation === 'landscape' ? 1200 : 1600;
            }
            $image = imagecreatetruecolor($width, $height);
            $white = imagecolorallocate($image, 255, 255, 255);
            $black = imagecolorallocate($image, 20, 20, 20);
            $gray = imagecolorallocate($image, 180, 180, 180);
            imagefill($image, 0, 0, $white);
            imagerectangle($image, 20, 20, $width - 21, $height - 21, $gray);

            $lines = preg_split('/\r\n|\r|\n/', strip_tags($html));
            $y = 80;
            foreach ($lines as $line) {
                if ($y > 700) break;
                $text = trim($line);
                if ($text === '') continue;
                imagestring($image, 4, 60, $y, $text, $black);
                $y += 28;
            }

            $tmpPath = storage_path('app/temp_certificate.jpeg');
            imagejpeg($image, $tmpPath, 90);
            imagedestroy($image);
            Storage::put($filePath, file_get_contents($tmpPath));
            @unlink($tmpPath);
            return;
        }

        $pdf = PDF::loadHTML($html)->setPaper($paper, $orientation);
        Storage::put($filePath, $pdf->output());
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

    public function previewCertificate(Request $r, $id)
    {
        $participantRowId = $r->query('participant_id');
        $templateId = $r->query('template_id');
        if (! $participantRowId) abort(404);
        $p = PengembanganPeserta::find($participantRowId);
        if (! $p || $p->pengembangan_id != $id) abort(404);

        $item = Pengembangan::findOrFail($id);
        $name = $p->peserta_type === 'guru' ? \DB::table('guru')->where('id',$p->peserta_id)->value('nama') : \DB::table('siswa')->where('id',$p->peserta_id)->value('nama');
        $barcode = (string) Str::uuid();
        if ($templateId) {
            $tpl = \DB::table('pengembangan_sertifikat_templates')->where('id',$templateId)->value('template_html');
            if ($tpl) {
                $html = str_replace(['{{name}}','{{kegiatan->nama_kegiatan}}','{{kegiatan->tema_kegiatan}}','{{barcode}}'], [e($name), e($item->nama_kegiatan), e($item->tema_kegiatan ?? ''), $barcode], $tpl);
            } else {
                $html = view('pengembangan.certificate_template', ['name'=>$name,'kegiatan'=>$item,'barcode'=>$barcode])->render();
            }
        } else {
            $html = view('pengembangan.certificate_template', ['name'=>$name,'kegiatan'=>$item,'barcode'=>$barcode])->render();
        }
        $pdf = PDF::loadHTML($html)->setPaper('a4','landscape');
        return response($pdf->output(), 200)->header('Content-Type', 'application/pdf');
    }

    public function verify($code)
    {
        $cert = PengembanganSertifikat::where('barcode',$code)->first();
        if (!$cert) return view('pengembangan.verify', ['valid'=>false]);
        return view('pengembangan.verify', ['valid'=>true,'cert'=>$cert]);
    }
}
