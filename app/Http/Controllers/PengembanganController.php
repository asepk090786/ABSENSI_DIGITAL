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
use App\Services\CertificateService;
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
            'pemateri_guru_ids.*'=>'integer|exists:guru,id',
            'pemateri_names'=>'nullable|string',
            'tanggal_mulai'=>'nullable|date',
            'tanggal_selesai'=>'nullable|date',
            'guru_ids'=>'nullable|array',
            'guru_ids.*'=>'integer|exists:guru,id',
            'siswa_ids'=>'nullable|array',
            'siswa_ids.*'=>'integer|exists:siswa,id',
            'external_participants'=>'nullable|array',
            'external_participants.*.name'=>'required_with:external_participants|string',
            'external_participants.*.instansi'=>'required_with:external_participants|string',
        ]);

        // Build pemateri array from selected guru ids and extra names
        $pemateri = [];
        $pemateriGuruIds = $r->input('pemateri_guru_ids', []);
        if (!empty($pemateriGuruIds)) {
            $rows = \DB::table('guru')->whereIn('id', $pemateriGuruIds)->pluck('nama')->all();
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

        $schoolName = \DB::table('sekolah')->value('nama_sekolah') ?? null;

        foreach (($r->input('guru_ids', [])) as $gid) {
            PengembanganPeserta::create([
                'pengembangan_id' => $p->id,
                'peserta_type' => 'guru',
                'peserta_id' => $gid,
                'peserta_name' => null,
                'instansi' => $schoolName,
            ]);
        }
        foreach (($r->input('siswa_ids', [])) as $sid) {
            PengembanganPeserta::create([
                'pengembangan_id' => $p->id,
                'peserta_type' => 'siswa',
                'peserta_id' => $sid,
                'peserta_name' => null,
                'instansi' => $schoolName,
            ]);
        }
        foreach (($r->input('external_participants', [])) as $external) {
            $name = trim($external['name'] ?? '');
            $instansi = trim($external['instansi'] ?? '');
            if ($name === '' && $instansi === '') {
                continue;
            }
            PengembanganPeserta::create([
                'pengembangan_id' => $p->id,
                'peserta_type' => 'external',
                'peserta_id' => null,
                'peserta_name' => $name ?: null,
                'instansi' => $instansi ?: null,
            ]);
        }

        return redirect()->route('pengembangan.index')->with('success','Kegiatan dibuat');
    }

    public function show($id)
    {
        $item = Pengembangan::with('peserta')->findOrFail($id);
        $templates = \DB::table('pengembangan_sertifikat_templates')->orderBy('nama')->get();
        // resolve participant names
        $participants = $item->peserta->map(function($p){
            if ($p->peserta_type === 'guru') {
                $name = \DB::table('guru')->where('id',$p->peserta_id)->value('nama');
            } elseif ($p->peserta_type === 'siswa') {
                $name = \DB::table('siswa')->where('id',$p->peserta_id)->value('nama');
            } else {
                $name = $p->peserta_name ?? 'Peserta Eksternal';
            }
            return [
                'id'=>$p->id,
                'type'=>$p->peserta_type,
                'peserta_id'=>$p->peserta_id,
                'name'=>$name,
                'instansi'=>$p->instansi,
            ];
        })->all();

        // existing certificates for this pengembangan (with resolved participant names)
        $certs = PengembanganSertifikat::where('pengembangan_id', $id)->get();
        $certificates = $certs->map(function($c){
            if ($c->peserta_type === 'guru') {
                $name = \DB::table('guru')->where('id',$c->peserta_id)->value('nama');
            } elseif ($c->peserta_type === 'siswa') {
                $name = \DB::table('siswa')->where('id',$c->peserta_id)->value('nama');
            } else {
                $name = $c->peserta_name ?? 'Peserta Eksternal';
            }
            $c->participant_name = $name;
            return $c;
        });
        $certMap = [];
        foreach ($certs as $c) {
            $key = $c->peserta_type . '_' . $c->peserta_id;
            $certMap[$key] = $c; // store model so we can get id/file_path
        }

        $defaultTemplateId = $item->default_template_id ?? null;
        $defaultNomorSertifikat = $item->default_nomor_sertifikat ?? null;

        return view('pengembangan.show', compact('item','templates','participants','certificates','certMap','defaultTemplateId','defaultNomorSertifikat'));
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
            'pemateri_guru_ids.*'=>'integer|exists:guru,id',
            'pemateri_names'=>'nullable|string',
            'tanggal_mulai'=>'nullable|date',
            'tanggal_selesai'=>'nullable|date',
            'guru_ids'=>'nullable|array',
            'guru_ids.*'=>'integer|exists:guru,id',
            'siswa_ids'=>'nullable|array',
            'siswa_ids.*'=>'integer|exists:siswa,id',
            'external_participants'=>'nullable|array',
            'external_participants.*.name'=>'required_with:external_participants|string',
            'external_participants.*.instansi'=>'required_with:external_participants|string',
        ]);

        $pemateri = [];
        $pemateriGuruIds = $r->input('pemateri_guru_ids', []);
        if (!empty($pemateriGuruIds)) {
            $rows = \DB::table('guru')->whereIn('id', $pemateriGuruIds)->pluck('nama')->all();
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
            'jenis_kegiatan'=>array_key_exists('jenis_kegiatan', $data) ? $data['jenis_kegiatan'] : $item->jenis_kegiatan,
            'deskripsi'=>$data['deskripsi'] ?? null,
            'pemateri'=>$pemateri,
            'tanggal_mulai'=>$data['tanggal_mulai'] ?? null,
            'tanggal_selesai'=>$data['tanggal_selesai'] ?? null,
        ]);

        // Replace participants
        \DB::table('pengembangan_peserta')->where('pengembangan_id', $item->id)->delete();
        $schoolName = \DB::table('sekolah')->value('nama_sekolah') ?? null;
        foreach (($r->input('guru_ids', [])) as $gid) {
            PengembanganPeserta::create([
                'pengembangan_id' => $item->id,
                'peserta_type' => 'guru',
                'peserta_id' => $gid,
                'peserta_name' => null,
                'instansi' => $schoolName,
            ]);
        }
        foreach (($r->input('siswa_ids', [])) as $sid) {
            PengembanganPeserta::create([
                'pengembangan_id' => $item->id,
                'peserta_type' => 'siswa',
                'peserta_id' => $sid,
                'peserta_name' => null,
                'instansi' => $schoolName,
            ]);
        }
        foreach (($r->input('external_participants', [])) as $external) {
            $name = trim($external['name'] ?? '');
            $instansi = trim($external['instansi'] ?? '');
            if ($name === '' && $instansi === '') {
                continue;
            }
            PengembanganPeserta::create([
                'pengembangan_id' => $item->id,
                'peserta_type' => 'external',
                'peserta_id' => null,
                'peserta_name' => $name ?: null,
                'instansi' => $instansi ?: null,
            ]);
        }

        return redirect()->route('pengembangan.show', $item->id)->with('success','Kegiatan diperbarui');
    }

    public function destroy($id)
    {
        $item = Pengembangan::findOrFail($id);
        $item->delete();
        return redirect()->route('pengembangan.index')->with('success','Kegiatan dihapus');
    }

    public function generateCertificates($id, CertificateService $certService)
    {
        $item = Pengembangan::with('peserta')->findOrFail($id);
        $storagePath = 'public/certificates';
        Storage::makeDirectory($storagePath);

        $data = request()->validate([
            'participant_ids' => 'nullable|array',
            'participant_ids.*' => 'integer|exists:pengembangan_peserta,id',
            'template_id' => 'nullable|integer|exists:pengembangan_sertifikat_templates,id',
            'nomor_surat' => 'nullable|string|max:255',
            'nomor_sertifikat' => 'nullable|string|max:255',
            'save_only' => 'sometimes|boolean',
            'save_certificate_defaults' => 'sometimes|boolean',
            'bukti_dukung_daftar_hadir' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:10240',
            'bukti_dukung_dokumentasi' => 'nullable|array',
            'bukti_dukung_dokumentasi.*' => 'file|mimes:pdf,jpeg,png,jpg|max:10240',
            'bukti_dukung_materi' => 'nullable|array',
            'bukti_dukung_materi.*' => 'file|mimes:pdf,jpeg,png,jpg,doc,docx|max:10240',
        ]);

        $selected = $data['participant_ids'] ?? [];
        $templateId = $data['template_id'] ?? null;
        $nomorSertifikat = $data['nomor_surat'] ?? ($data['nomor_sertifikat'] ?? null);
        $saveOnly = request()->boolean('save_only');
        $saveAsDefault = $saveOnly || request()->boolean('save_certificate_defaults');
        $template = $templateId ? \DB::table('pengembangan_sertifikat_templates')->where('id', $templateId)->first() : null;
        $outputFormat = $template?->output_format ?? 'pdf';

        $buktiDukung = [
            'daftar_hadir' => null,
            'dokumentasi' => [],
            'materi' => [],
        ];

        if (request()->hasFile('bukti_dukung_daftar_hadir')) {
            $file = request()->file('bukti_dukung_daftar_hadir');
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $buktiDukung['daftar_hadir'] = Storage::disk('public')->putFileAs('pengembangan/bukti_dukung', $file, $filename);
        }

        foreach (request()->file('bukti_dukung_dokumentasi', []) as $file) {
            if (!$file) continue;
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $buktiDukung['dokumentasi'][] = Storage::disk('public')->putFileAs('pengembangan/bukti_dukung', $file, $filename);
        }

        foreach (request()->file('bukti_dukung_materi', []) as $file) {
            if (!$file) continue;
            $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
            $buktiDukung['materi'][] = Storage::disk('public')->putFileAs('pengembangan/bukti_dukung', $file, $filename);
        }

        if (!$template || !$template->background_image) {
            // Use first available template with background
            $template = \DB::table('pengembangan_sertifikat_templates')
                ->whereNotNull('background_image')
                ->orderByDesc('id')
                ->first();
        }

        $selectedParticipantIds = $selected;
        $toGenerate = $item->peserta->filter(function($p) use ($selectedParticipantIds){
            if (empty($selectedParticipantIds)) return true;
            return in_array($p->id, $selectedParticipantIds);
        });

        $pemateriNames = is_array($item->pemateri) ? array_filter(array_map('trim', $item->pemateri)) : [];
        $existingParticipantNames = $item->peserta->map(function ($p) {
            if ($p->peserta_type === 'guru') {
                return \DB::table('guru')->where('id', $p->peserta_id)->value('nama');
            }
            if ($p->peserta_type === 'siswa') {
                return \DB::table('siswa')->where('id', $p->peserta_id)->value('nama');
            }
            return $p->peserta_name;
        })->filter()->unique()->values()->all();

        $pemateriRecipients = collect($pemateriNames)
            ->filter(fn ($name) => !in_array($name, $existingParticipantNames, true))
            ->map(function ($name) use ($item) {
                return (object) [
                    'pengembangan_id' => $item->id,
                    'peserta_type' => 'pemateri',
                    'peserta_id' => null,
                    'peserta_name' => $name,
                    'instansi' => null,
                ];
            });

        $toGenerate = $toGenerate->concat($pemateriRecipients);

        if ($saveAsDefault) {
            $item->update([
                'default_nomor_sertifikat' => $saveOnly || request()->filled('nomor_surat') || request()->filled('nomor_sertifikat') ? $nomorSertifikat : null,
                'default_template_id' => $saveOnly || request()->filled('template_id') ? $templateId : null,
            ]);
        }

        $hasNewEvidence = !empty($buktiDukung['daftar_hadir']) || !empty($buktiDukung['dokumentasi']) || !empty($buktiDukung['materi']);

        if ($saveOnly && $hasNewEvidence) {
            foreach ($toGenerate as $p) {
                $existingCerts = PengembanganSertifikat::where('pengembangan_id', $item->id)
                    ->where('peserta_type', $p->peserta_type)
                    ->when($p->peserta_id !== null, function ($query) use ($p) {
                        return $query->where('peserta_id', $p->peserta_id);
                    })
                    ->when($p->peserta_id === null, function ($query) use ($p) {
                        return $query->where('peserta_name', $p->peserta_name)->where('instansi', $p->instansi);
                    })
                    ->get();

                $existingCert = $existingCerts->first();
                if ($existingCerts->count() > 1) {
                    $existingCerts->slice(1)->each(function ($duplicate) {
                        if ($duplicate->file_path) {
                            $this->deleteStorageFile($duplicate->file_path);
                        }
                        $duplicate->delete();
                    });
                }

                $existingDokumentasi = $existingCert ? (array) $existingCert->bukti_dukung_dokumentasi : [];
                $existingMateri = $existingCert ? (array) $existingCert->bukti_dukung_materi : [];

                if ($existingCert) {
                    if (!empty($buktiDukung['daftar_hadir'])) {
                        $existingCert->bukti_dukung_daftar_hadir = $buktiDukung['daftar_hadir'];
                    }
                    if (!empty($buktiDukung['dokumentasi'])) {
                        $existingCert->bukti_dukung_dokumentasi = array_values(array_merge($existingDokumentasi, $buktiDukung['dokumentasi']));
                    }
                    if (!empty($buktiDukung['materi'])) {
                        $existingCert->bukti_dukung_materi = array_values(array_merge($existingMateri, $buktiDukung['materi']));
                    }
                    $existingCert->save();
                } else {
                    PengembanganSertifikat::create([
                        'pengembangan_id' => $item->id,
                        'peserta_type' => $p->peserta_type,
                        'peserta_id' => $p->peserta_id,
                        'peserta_name' => $p->peserta_name,
                        'instansi' => $p->instansi,
                        'file_path' => null,
                        'barcode' => (string) Str::uuid(),
                        'nomor_sertifikat' => $nomorSertifikat ?: null,
                        'template_id' => $templateId ?: null,
                        'bukti_dukung_daftar_hadir' => $buktiDukung['daftar_hadir'] ?? null,
                        'bukti_dukung_dokumentasi' => $buktiDukung['dokumentasi'] ?: null,
                        'bukti_dukung_materi' => $buktiDukung['materi'] ?: null,
                    'is_visible' => true,
                    ]);
                }
            }
        }

        if ($saveOnly) {
            return redirect()->route('pengembangan.show', $id)->with('success', 'Pengaturan sertifikat disimpan');
        }

        foreach ($toGenerate as $p) {
            $barcode = (string) Str::uuid();
            if ($p->peserta_type === 'guru') {
                $name = \DB::table('guru')->where('id',$p->peserta_id)->value('nama');
            } elseif ($p->peserta_type === 'siswa') {
                $name = \DB::table('siswa')->where('id',$p->peserta_id)->value('nama');
            } elseif ($p->peserta_type === 'pemateri') {
                $name = $p->peserta_name ?? 'Pemateri';
            } else {
                $name = $p->peserta_name ?? 'Peserta Eksternal';
            }
            $sebagai = $this->resolveParticipantRole($p, $item);

            if ($template && $template->background_image) {
                $filePath = $certService->generatePdf($template, $item, $name, $barcode, $nomorSertifikat, $sebagai);
            } else {
                // Fallback: DomPDF
                $html = view('pengembangan.certificate_template', ['name'=>$name,'kegiatan'=>$item,'barcode'=>$barcode,'nomor_surat'=>$nomorSertifikat,'sebagai'=>$sebagai])->render();
                $pageSize = strtolower($template?->page_size ?? 'a4');
                $orientation = ($template?->page_orientation ?? 'portrait') === 'landscape' ? 'landscape' : 'portrait';
                $pdf = PDF::loadHTML($html)->setPaper($pageSize, $orientation);
                $fileName = "pengembangan_{$item->id}_{$p->peserta_type}_{$p->peserta_id}_".time().".pdf";
                $filePath = 'certificates/'.$fileName;
                Storage::disk('public')->put($filePath, $pdf->output());
            }

            $existingCerts = PengembanganSertifikat::where('pengembangan_id', $item->id)
                ->where('peserta_type', $p->peserta_type)
                ->when($p->peserta_id !== null, function ($query) use ($p) {
                    return $query->where('peserta_id', $p->peserta_id);
                })
                ->when($p->peserta_id === null, function ($query) use ($p) {
                    return $query->where('peserta_name', $p->peserta_name)->where('instansi', $p->instansi);
                })
                ->get();

            $existingCert = $existingCerts->first();
            if ($existingCerts->count() > 1) {
                $existingCerts->slice(1)->each(function ($duplicate) {
                    if ($duplicate->file_path) {
                        $this->deleteStorageFile($duplicate->file_path);
                    }
                    $duplicate->delete();
                });
            }

            $existingDokumentasi = $existingCert ? (array) $existingCert->bukti_dukung_dokumentasi : [];
            $existingMateri = $existingCert ? (array) $existingCert->bukti_dukung_materi : [];
            $existingDaftarHadir = $existingCert ? $existingCert->bukti_dukung_daftar_hadir : null;

            $preservedDaftarHadir = $buktiDukung['daftar_hadir'] ?? $existingDaftarHadir;
            $preservedDokumentasi = !empty($buktiDukung['dokumentasi'])
                ? array_values(array_merge($existingDokumentasi, $buktiDukung['dokumentasi']))
                : $existingDokumentasi;
            $preservedMateri = !empty($buktiDukung['materi'])
                ? array_values(array_merge($existingMateri, $buktiDukung['materi']))
                : $existingMateri;

            if ($existingCert) {
                if ($existingCert->file_path) {
                    $this->deleteStorageFile($existingCert->file_path);
                }
                $existingCert->update([
                    'file_path' => $filePath,
                    'barcode' => $barcode,
                    'nomor_sertifikat' => $nomorSertifikat ?: null,
                    'template_id' => $templateId ?: null,
                    'bukti_dukung_daftar_hadir' => $preservedDaftarHadir,
                    'bukti_dukung_dokumentasi' => empty($preservedDokumentasi) ? null : $preservedDokumentasi,
                    'bukti_dukung_materi' => empty($preservedMateri) ? null : $preservedMateri,
                ]);
            } else {
                PengembanganSertifikat::create([
                    'pengembangan_id' => $item->id,
                    'peserta_type' => $p->peserta_type,
                    'peserta_id' => $p->peserta_id,
                    'peserta_name' => $p->peserta_name,
                    'instansi' => $p->instansi,
                    'file_path' => $filePath,
                    'barcode' => $barcode,
                    'nomor_sertifikat' => $nomorSertifikat ?: null,
                    'template_id' => $templateId ?: null,
                    'is_visible' => true,
                    'bukti_dukung_daftar_hadir' => $preservedDaftarHadir,
                    'bukti_dukung_dokumentasi' => empty($preservedDokumentasi) ? null : $preservedDokumentasi,
                    'bukti_dukung_materi' => empty($preservedMateri) ? null : $preservedMateri,
                ]);
            }
        }

        if ($saveOnly) {
            return redirect()->route('pengembangan.show', $id)->with('success', 'Pengaturan sertifikat disimpan');
        }

        foreach ($toGenerate as $p) {
            $barcode = (string) Str::uuid();
            if ($p->peserta_type === 'guru') {
                $name = \DB::table('guru')->where('id',$p->peserta_id)->value('nama');
            } elseif ($p->peserta_type === 'siswa') {
                $name = \DB::table('siswa')->where('id',$p->peserta_id)->value('nama');
            } elseif ($p->peserta_type === 'pemateri') {
                $name = $p->peserta_name ?? 'Pemateri';
            } else {
                $name = $p->peserta_name ?? 'Peserta Eksternal';
            }
            $sebagai = $this->resolveParticipantRole($p, $item);

            if ($template && $template->background_image) {
                $filePath = $certService->generatePdf($template, $item, $name, $barcode, $nomorSertifikat, $sebagai);
            } else {
                // Fallback: DomPDF
                $html = view('pengembangan.certificate_template', ['name'=>$name,'kegiatan'=>$item,'barcode'=>$barcode,'nomor_surat'=>$nomorSertifikat,'sebagai'=>$sebagai])->render();
                $pageSize = strtolower($template?->page_size ?? 'a4');
                $orientation = ($template?->page_orientation ?? 'portrait') === 'landscape' ? 'landscape' : 'portrait';
                $pdf = PDF::loadHTML($html)->setPaper($pageSize, $orientation);
                $fileName = "pengembangan_{$item->id}_{$p->peserta_type}_{$p->peserta_id}_".time().".pdf";
                $filePath = 'certificates/'.$fileName;
                Storage::disk('public')->put($filePath, $pdf->output());
            }

            $existingCerts = PengembanganSertifikat::where('pengembangan_id', $item->id)
                ->where('peserta_type', $p->peserta_type)
                ->when($p->peserta_id !== null, function ($query) use ($p) {
                    return $query->where('peserta_id', $p->peserta_id);
                })
                ->when($p->peserta_id === null, function ($query) use ($p) {
                    return $query->where('peserta_name', $p->peserta_name)->where('instansi', $p->instansi);
                })
                ->get();

            $existingCert = $existingCerts->first();
            if ($existingCerts->count() > 1) {
                $existingCerts->slice(1)->each(function ($duplicate) {
                    if ($duplicate->file_path) {
                        $this->deleteStorageFile($duplicate->file_path);
                    }
                    $duplicate->delete();
                });
            }

            $existingDokumentasi = $existingCert ? (array) $existingCert->bukti_dukung_dokumentasi : [];
            $existingMateri = $existingCert ? (array) $existingCert->bukti_dukung_materi : [];
            $existingDaftarHadir = $existingCert ? $existingCert->bukti_dukung_daftar_hadir : null;

            $preservedDaftarHadir = $buktiDukung['daftar_hadir'] ?? $existingDaftarHadir;
            $preservedDokumentasi = !empty($buktiDukung['dokumentasi'])
                ? array_values(array_merge($existingDokumentasi, $buktiDukung['dokumentasi']))
                : $existingDokumentasi;
            $preservedMateri = !empty($buktiDukung['materi'])
                ? array_values(array_merge($existingMateri, $buktiDukung['materi']))
                : $existingMateri;

            if ($existingCert) {
                if ($existingCert->file_path) {
                    $this->deleteStorageFile($existingCert->file_path);
                }
                $existingCert->update([
                    'file_path' => $filePath,
                    'barcode' => $barcode,
                    'nomor_sertifikat' => $nomorSertifikat ?: null,
                    'template_id' => $templateId ?: null,
                    'bukti_dukung_daftar_hadir' => $preservedDaftarHadir,
                    'bukti_dukung_dokumentasi' => empty($preservedDokumentasi) ? null : $preservedDokumentasi,
                    'bukti_dukung_materi' => empty($preservedMateri) ? null : $preservedMateri,
                ]);
            } else {
                PengembanganSertifikat::create([
                    'pengembangan_id' => $item->id,
                    'peserta_type' => $p->peserta_type,
                    'peserta_id' => $p->peserta_id,
                    'peserta_name' => $p->peserta_name,
                    'instansi' => $p->instansi,
                    'file_path' => $filePath,
                    'barcode' => $barcode,
                    'nomor_sertifikat' => $nomorSertifikat ?: null,
                    'template_id' => $templateId ?: null,
                    'is_visible' => true,
                    'bukti_dukung_daftar_hadir' => $preservedDaftarHadir,
                    'bukti_dukung_dokumentasi' => empty($preservedDokumentasi) ? null : $preservedDokumentasi,
                    'bukti_dukung_materi' => empty($preservedMateri) ? null : $preservedMateri,
                ]);
            }
        }

        return redirect()->route('pengembangan.show', $id)->with('success','Sertifikat dibuat untuk peserta');
    }

    protected function renderTemplateContent($template, $item, $name, $barcode, ?string $sebagai = null)
    {
        // Convert storage-relative image paths to absolute paths for DomPDF
        if ($template && !empty($template->background_image)) {
            $storagePath = storage_path('app/public/' . $template->background_image);
            if (file_exists($storagePath)) {
                $html = str_replace(
                    'storage/' . $template->background_image,
                    $storagePath,
                    $html
                );
            }
            $publicUrl = asset('storage/' . $template->background_image);
            $html = str_replace($publicUrl, $storagePath, $html);
        }

        // Convert any storage/... URLs to absolute paths
        $html = preg_replace_callback(
            '/(?:url\()?[\'"]?(?:https?:\/\/[^\/]+)?\/storage\/([^\'"\)\s]+)[\'"]?\)?/i',
            function ($m) {
                $path = storage_path('app/public/' . $m[1]);
                if (file_exists($path)) {
                    return $path;
                }
                return $m[0];
            },
            $html
        );

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

        $query = PengembanganSertifikat::with('pengembangan');

        if ($user->guru_id) {
            $guruName = \DB::table('guru')->where('id', $user->guru_id)->value('nama');
            $query->where(function ($q) use ($user, $guruName) {
                $q->where(function ($sub) use ($user) {
                    $sub->where('peserta_type', 'guru')->where('peserta_id', $user->guru_id);
                });
                if (!empty($guruName)) {
                    $q->orWhere(function ($sub) use ($guruName) {
                        $sub->where('peserta_type', 'pemateri')->where('peserta_name', $guruName);
                    });
                }
            });
        } elseif ($user->siswa_id) {
            $query->where('peserta_type', 'siswa')->where('peserta_id', $user->siswa_id);
        } else {
            $query->whereRaw('0 = 1');
        }

        $certs = $query->orderByDesc('created_at')->get();
        return view('pengembangan.certificates', compact('certs'));
    }

    public function viewCertificate($id)
    {
        $cert = PengembanganSertifikat::findOrFail($id);
        if (!$cert->is_visible) {
            abort(403, 'Sertifikat belum tersedia untuk peserta.');
        }
        $path = $this->resolveCertificateStoragePath($cert->file_path);
        if (!$path || !file_exists($path)) {
            abort(404);
        }
        return response()->file($path);
    }

    public function toggleCertificateVisibility($id)
    {
        $cert = PengembanganSertifikat::findOrFail($id);
        $cert->is_visible = ! $cert->is_visible;
        $cert->save();

        return redirect()->back()->with('success', 'Visibilitas sertifikat diperbarui.');
    }

    public function toggleAllCertificatesVisibility($id)
    {
        $item = Pengembangan::findOrFail($id);
        $hasCertificates = PengembanganSertifikat::where('pengembangan_id', $item->id)->exists();

        if (!$hasCertificates) {
            return redirect()->back()->with('error', 'Tidak ada sertifikat untuk diperbarui.');
        }

        $anyVisible = PengembanganSertifikat::where('pengembangan_id', $item->id)->where('is_visible', true)->exists();
        PengembanganSertifikat::where('pengembangan_id', $item->id)
            ->update(['is_visible' => ! $anyVisible]);

        return redirect()->back()->with('success', $anyVisible ? 'Semua sertifikat disembunyikan.' : 'Semua sertifikat ditampilkan.');
    }

    public function downloadCertificate($id)
    {
        $cert = PengembanganSertifikat::findOrFail($id);
        if (!$cert->is_visible) {
            abort(403, 'Sertifikat belum tersedia untuk peserta.');
        }
        $path = $this->resolveCertificateStoragePath($cert->file_path);
        if (!$path) abort(404);
        return response()->download($path);
    }

    public function destroyCertificate($id)
    {
        $cert = PengembanganSertifikat::findOrFail($id);
        $pengembanganId = $cert->pengembangan_id;

        // Hapus file fisik
        $filePath = $this->resolveCertificateStoragePath($cert->file_path);
        if ($filePath && file_exists($filePath)) {
            @unlink($filePath);
        }

        $cert->delete();

        return redirect()->route('pengembangan.show', $pengembanganId)
            ->with('success', 'Sertifikat berhasil dihapus.');
    }

    public function destroyCertificateEvidence($id, $type, $index = null)
    {
        $cert = PengembanganSertifikat::findOrFail($id);
        $allowedTypes = ['daftar_hadir', 'dokumentasi', 'materi'];

        if (!in_array($type, $allowedTypes, true)) {
            abort(404);
        }

        if ($type === 'daftar_hadir') {
            if ($cert->bukti_dukung_daftar_hadir) {
                $this->deleteStorageFile($cert->bukti_dukung_daftar_hadir);
                $cert->bukti_dukung_daftar_hadir = null;
                $cert->save();
            }
        } else {
            $field = 'bukti_dukung_' . $type;
            $files = (array) $cert->{$field};
            if (!is_numeric($index) || !isset($files[$index])) {
                abort(404);
            }
            $path = $files[$index];
            $this->deleteStorageFile($path);
            array_splice($files, $index, 1);
            $cert->{$field} = empty($files) ? null : array_values($files);
            $cert->save();
        }

        return redirect()->route('pengembangan.show', $cert->pengembangan_id)
            ->with('success', 'Bukti dukung berhasil dihapus.');
    }

    private function deleteStorageFile(?string $path)
    {
        if (!$path) {
            return;
        }

        $normalized = str_replace('\\', '/', $path);
        $normalized = preg_replace('#^/?storage/#', '', $normalized);
        $normalized = preg_replace('#^/?public/#', '', $normalized);
        $normalized = ltrim($normalized, '/');

        if (Storage::disk('public')->exists($normalized)) {
            Storage::disk('public')->delete($normalized);
        }
    }

    public function bulkDestroyCertificates(Request $r)
    {
        $ids = $r->input('certificate_ids', []);
        $id = $r->input('pengembangan_id');
        
        if (empty($ids)) {
            return back()->with('error', 'Tidak ada sertifikat yang dipilih.');
        }
        
        if (!$id) {
            return back()->with('error', 'ID kegiatan tidak ditemukan.');
        }

        $certs = PengembanganSertifikat::whereIn('id', $ids)->where('pengembangan_id', $id)->get();

        if ($certs->isEmpty()) {
            return back()->with('error', 'Sertifikat tidak ditemukan.');
        }

        $deleted = 0;
        foreach ($certs as $cert) {
            // Hapus file fisik
            $filePath = $this->resolveCertificateStoragePath($cert->file_path);
            if ($filePath && file_exists($filePath)) {
                @unlink($filePath);
            }
            $cert->delete();
            $deleted++;
        }

        return redirect()->route('pengembangan.show', $id)
            ->with('success', $deleted . ' sertifikat berhasil dihapus.');
    }

    protected function resolveCertificateStoragePath(?string $filePath): ?string
    {
        if (!$filePath) {
            return null;
        }

        $normalized = str_replace('\\', '/', $filePath);
        $normalized = preg_replace('#^/?storage/#', '', $normalized);
        $normalized = preg_replace('#^/?public/#', '', $normalized);
        $normalized = ltrim($normalized, '/');

        $candidates = [];
        if ($normalized !== '') {
            $candidates[] = Storage::disk('public')->path($normalized);
            $candidates[] = storage_path('app/public/' . $normalized);
            $candidates[] = storage_path('app/' . $normalized);
        }

        foreach ($candidates as $candidate) {
            if ($candidate && file_exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    public function previewCertificate(Request $r, $id, CertificateService $certService)
    {
        $participantRowId = $r->query('participant_id');
        $templateId = $r->query('template_id');
        $nomorSertifikat = $r->query('nomor_surat', $r->query('nomor_sertifikat'));
        if (! $participantRowId) abort(404);
        $p = PengembanganPeserta::find($participantRowId);
        if (! $p || $p->pengembangan_id != $id) abort(404);

        $item = Pengembangan::findOrFail($id);
        if ($p->peserta_type === 'guru') {
            $name = \DB::table('guru')->where('id',$p->peserta_id)->value('nama');
        } elseif ($p->peserta_type === 'siswa') {
            $name = \DB::table('siswa')->where('id',$p->peserta_id)->value('nama');
        } else {
            $name = $p->peserta_name ?? 'Peserta Eksternal';
        }
        $sebagai = $this->resolveParticipantRole($p, $item);
        $barcode = (string) Str::uuid();

        if ($templateId) {
            $template = \DB::table('pengembangan_sertifikat_templates')->where('id',$templateId)->first();
            if ($template && $template->background_image) {
                return $certService->streamPreview($template, $item, $name, $barcode, $nomorSertifikat, $sebagai);
            }
        }

        // Fallback: use default template or first available
        $template = \DB::table('pengembangan_sertifikat_templates')
            ->whereNotNull('background_image')
            ->orderByDesc('id')
            ->first();

        if ($template) {
            return $certService->streamPreview($template, $item, $name, $barcode, $nomorSertifikat, $sebagai);
        }

        // Last fallback: DomPDF from HTML
        $html = view('pengembangan.certificate_template', ['name'=>$name,'kegiatan'=>$item,'barcode'=>$barcode,'nomor_surat'=>$nomorSertifikat,'sebagai'=>$sebagai])->render();
        $pdf = PDF::loadHTML($html)->setPaper('a4','landscape');
        return response($pdf->output(), 200)->header('Content-Type', 'application/pdf');
    }

    public function verify($code)
    {
        $cert = PengembanganSertifikat::where('barcode',$code)->first();
        if (!$cert) return view('pengembangan.verify', ['valid'=>false]);
        $item = Pengembangan::find($cert->pengembangan_id);

        // Mark as verified on first scan
        if (!$cert->verified_at) {
            $cert->verified_at = now();
            $cert->save();
        }

        // Resolve participant name
        $participantName = null;
        if ($cert->peserta_type === 'guru') {
            $participantName = \DB::table('guru')->where('id', $cert->peserta_id)->value('nama');
        } elseif ($cert->peserta_type === 'siswa') {
            $participantName = \DB::table('siswa')->where('id', $cert->peserta_id)->value('nama');
        } else {
            $participantName = $cert->peserta_name ?? null;
        }

        $pemateri = is_array($item->pemateri) ? $item->pemateri : [];
        $participantRole = in_array($participantName, $pemateri, true) ? 'Pemateri' : 'Peserta';

        return view('pengembangan.verify', [
            'valid' => true,
            'cert' => $cert,
            'item' => $item,
            'participant_name' => $participantName,
            'participant_role' => $participantRole,
        ]);
    }

    protected function resolveParticipantRole($participant, Pengembangan $item): string
    {
        if ($participant->peserta_type === 'guru') {
            $name = \DB::table('guru')->where('id', $participant->peserta_id)->value('nama');
        } elseif ($participant->peserta_type === 'siswa') {
            $name = \DB::table('siswa')->where('id', $participant->peserta_id)->value('nama');
        } else {
            $name = $participant->peserta_name ?? null;
        }

        $pemateri = is_array($item->pemateri) ? $item->pemateri : [];
        return in_array($name, $pemateri, true) ? 'Pemateri' : 'Peserta';
    }
}
