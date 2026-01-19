<?php

namespace App\Http\Controllers;

use App\Models\RencanaPembelajaran;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use Illuminate\Http\Request;

class RencanaPembelajaranController extends Controller
{
    /**
     * Display listing for a specific mata pelajaran & tingkat
     */
    public function index(Request $request)
    {
        $guru = auth()->user()->guru;
        $mataPelajaranId = $request->query('mata_pelajaran_id');
        $tingkat = $request->query('tingkat');

        $items = RencanaPembelajaran::where('guru_id', $guru->id)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->with(['mataPelajaran', 'kelas'])
            ->get();

        $mataPelajaran = MataPelajaran::find($mataPelajaranId);

        return view('rencana_pembelajaran.index', [
            'items' => $items,
            'mataPelajaran' => $mataPelajaran,
            'tingkat' => $tingkat,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $guru = auth()->user()->guru;
        $mataPelajaranId = $request->query('mata_pelajaran_id');
        $tingkat = $request->query('tingkat');

        $mataPelajaran = MataPelajaran::find($mataPelajaranId);
        
        // Get only classes that this guru teaches for this subject and level
        $kelas = \App\Models\JadwalKbm::where('guru_id', $guru->id)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->with('kelas')
            ->get()
            ->pluck('kelas')
            ->filter(function($k) use ($tingkat) {
                return $k->tingkat_kelas == $tingkat;
            })
            ->unique('id')
            ->sortBy('nama_kelas');

        return view('rencana_pembelajaran.create', [
            'mataPelajaran' => $mataPelajaran,
            'tingkat' => $tingkat,
            'kelas' => $kelas,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'metode' => 'nullable|string',
            'media' => 'nullable|string',
            'sumber' => 'nullable|string',
            'penilaian' => 'nullable|string',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'status' => 'required|in:draft,published',
        ]);

        $guru = auth()->user()->guru;
        $mataPelajaranId = $validated['mata_pelajaran_id'];
        $tingkat = Kelas::find($validated['kelas_ids'][0])->tingkat_kelas;

        // Create rencana pembelajaran for each selected class
        foreach ($validated['kelas_ids'] as $kelasId) {
            $data = $validated;
            $data['guru_id'] = $guru->id;
            $data['kelas_id'] = $kelasId;
            unset($data['kelas_ids']);
            
            RencanaPembelajaran::create($data);
        }

        return redirect()
            ->route('rencana_pembelajaran.index', [
                'mata_pelajaran_id' => $mataPelajaranId,
                'tingkat' => $tingkat,
            ])
            ->with('success', 'Rencana pembelajaran berhasil dibuat untuk ' . count($validated['kelas_ids']) . ' kelas.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RencanaPembelajaran $rencanaPembelajaran)
    {
        return view('rencana_pembelajaran.show', ['item' => $rencanaPembelajaran]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RencanaPembelajaran $rencanaPembelajaran)
    {
        $guru = auth()->user()->guru;
        
        // Get only classes that this guru teaches for this subject and level
        $kelas = \App\Models\JadwalKbm::where('guru_id', $guru->id)
            ->where('mata_pelajaran_id', $rencanaPembelajaran->mata_pelajaran_id)
            ->with('kelas')
            ->get()
            ->pluck('kelas')
            ->filter(function($k) use ($rencanaPembelajaran) {
                return $k->tingkat_kelas == $rencanaPembelajaran->kelas->tingkat_kelas;
            })
            ->unique('id')
            ->sortBy('nama_kelas');
        
        return view('rencana_pembelajaran.edit', [
            'item' => $rencanaPembelajaran,
            'kelas' => $kelas,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RencanaPembelajaran $rencanaPembelajaran)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'metode' => 'nullable|string',
            'media' => 'nullable|string',
            'sumber' => 'nullable|string',
            'penilaian' => 'nullable|string',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date',
            'status' => 'required|in:draft,published',
        ]);

        $rencanaPembelajaran->update($validated);

        return redirect()
            ->route('rencana_pembelajaran.index', [
                'mata_pelajaran_id' => $rencanaPembelajaran->mata_pelajaran_id,
                'tingkat' => $rencanaPembelajaran->kelas->tingkat_kelas,
            ])
            ->with('success', 'Rencana pembelajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RencanaPembelajaran $rencanaPembelajaran)
    {
        $mataPelajaranId = $rencanaPembelajaran->mata_pelajaran_id;
        $tingkat = $rencanaPembelajaran->kelas->tingkat_kelas;

        $rencanaPembelajaran->delete();

        return redirect()
            ->route('rencana_pembelajaran.index', [
                'mata_pelajaran_id' => $mataPelajaranId,
                'tingkat' => $tingkat,
            ])
            ->with('success', 'Rencana pembelajaran berhasil dihapus.');
    }

    /**
     * Download template Word untuk Rencana Pembelajaran
     */
    public function templateDownload()
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        // Set default font
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);
        
        $section = $phpWord->addSection();
        
        // Title
        $section->addText(
            'TEMPLATE RENCANA PEMBELAJARAN',
            ['bold' => true, 'size' => 14],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        
        $section->addText('');
        
        // Information section
        $section->addText('INFORMASI UMUM', ['bold' => true, 'size' => 12]);
        
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        
        $table->addRow();
        $table->addCell(2000)->addText('Judul', ['bold' => true]);
        $table->addCell(6000)->addText('[Masukkan judul rencana pembelajaran di sini]');
        
        $table->addRow();
        $table->addCell(2000)->addText('Mata Pelajaran', ['bold' => true]);
        $table->addCell(6000)->addText('[Nama mata pelajaran]');
        
        $table->addRow();
        $table->addCell(2000)->addText('Kelas', ['bold' => true]);
        $table->addCell(6000)->addText('[Nama kelas]');
        
        $table->addRow();
        $table->addCell(2000)->addText('Status', ['bold' => true]);
        $table->addCell(6000)->addText('[Draft / Published]');
        
        $section->addText('');
        
        // Content sections
        $section->addText('DESKRIPSI', ['bold' => true, 'size' => 12]);
        $section->addText('[Deskripsi singkat tentang rencana pembelajaran...]');
        $section->addText('');
        
        $section->addText('TUJUAN PEMBELAJARAN', ['bold' => true, 'size' => 12]);
        $section->addText('[Tuliskan tujuan pembelajaran yang ingin dicapai...]');
        $section->addText('');
        
        $section->addText('METODE PEMBELAJARAN', ['bold' => true, 'size' => 12]);
        $section->addText('[Jelaskan metode pembelajaran yang akan digunakan...]');
        $section->addText('');
        
        $section->addText('MEDIA PEMBELAJARAN', ['bold' => true, 'size' => 12]);
        $section->addText('[Sebutkan media yang akan digunakan dalam pembelajaran...]');
        $section->addText('');
        
        $section->addText('SUMBER BELAJAR', ['bold' => true, 'size' => 12]);
        $section->addText('[Referensi buku, link, atau sumber lain yang digunakan...]');
        $section->addText('');
        
        $section->addText('PENILAIAN', ['bold' => true, 'size' => 12]);
        $section->addText('[Jelaskan metode penilaian dan kriteria penilaian...]');
        
        // Generate filename
        $filename = 'Template_Rencana_Pembelajaran_' . date('Y-m-d') . '.docx';
        
        // Save and download
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save(storage_path('app/temp/' . $filename));
        
        return response()->download(storage_path('app/temp/' . $filename))->deleteFileAfterSend(true);
    }

    /**
     * Export rencana pembelajaran to Word document
     */
    public function export(RencanaPembelajaran $rencanaPembelajaran)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        
        // Set default font
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(11);
        
        $section = $phpWord->addSection();
        
        // Title
        $section->addText(
            'RENCANA PEMBELAJARAN',
            ['bold' => true, 'size' => 14],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        
        $section->addText(
            $rencanaPembelajaran->mataPelajaran->nama_mapel . ' - ' . $rencanaPembelajaran->kelas->nama_kelas,
            ['size' => 12],
            ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]
        );
        
        $section->addText('');
        
        // Information table
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        
        $rowStyle = ['bgColor' => 'D3D3D3'];
        $table->addRow();
        $table->addCell(2000)->addText('Judul', ['bold' => true]);
        $table->addCell(6000)->addText($rencanaPembelajaran->judul);
        
        $table->addRow();
        $table->addCell(2000)->addText('Mata Pelajaran', ['bold' => true]);
        $table->addCell(6000)->addText($rencanaPembelajaran->mataPelajaran->nama_mapel);
        
        $table->addRow();
        $table->addCell(2000)->addText('Kelas', ['bold' => true]);
        $table->addCell(6000)->addText($rencanaPembelajaran->kelas->nama_kelas);
        
        $table->addRow();
        $table->addCell(2000)->addText('Status', ['bold' => true]);
        $table->addCell(6000)->addText(ucfirst($rencanaPembelajaran->status));
        
        if ($rencanaPembelajaran->tanggal_mulai || $rencanaPembelajaran->tanggal_selesai) {
            $table->addRow();
            $table->addCell(2000)->addText('Periode', ['bold' => true]);
            $periode = '';
            if ($rencanaPembelajaran->tanggal_mulai) {
                $periode = $rencanaPembelajaran->tanggal_mulai->format('d/m/Y');
            }
            if ($rencanaPembelajaran->tanggal_selesai) {
                $periode .= ' - ' . $rencanaPembelajaran->tanggal_selesai->format('d/m/Y');
            }
            $table->addCell(6000)->addText($periode);
        }
        
        $section->addText('');
        
        // Details sections
        if ($rencanaPembelajaran->deskripsi) {
            $section->addText('Deskripsi', ['bold' => true, 'size' => 12]);
            $section->addText($rencanaPembelajaran->deskripsi);
            $section->addText('');
        }
        
        if ($rencanaPembelajaran->tujuan) {
            $section->addText('Tujuan Pembelajaran', ['bold' => true, 'size' => 12]);
            $section->addText($rencanaPembelajaran->tujuan);
            $section->addText('');
        }
        
        if ($rencanaPembelajaran->metode) {
            $section->addText('Metode Pembelajaran', ['bold' => true, 'size' => 12]);
            $section->addText($rencanaPembelajaran->metode);
            $section->addText('');
        }
        
        if ($rencanaPembelajaran->media) {
            $section->addText('Media Pembelajaran', ['bold' => true, 'size' => 12]);
            $section->addText($rencanaPembelajaran->media);
            $section->addText('');
        }
        
        if ($rencanaPembelajaran->sumber) {
            $section->addText('Sumber Belajar', ['bold' => true, 'size' => 12]);
            $section->addText($rencanaPembelajaran->sumber);
            $section->addText('');
        }
        
        if ($rencanaPembelajaran->penilaian) {
            $section->addText('Penilaian', ['bold' => true, 'size' => 12]);
            $section->addText($rencanaPembelajaran->penilaian);
        }
        
        // Generate filename
        $filename = 'RP_' . \Illuminate\Support\Str::slug($rencanaPembelajaran->judul) . '_' . time() . '.docx';
        
        // Save and download
        $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save(storage_path('app/temp/' . $filename));
        
        return response()->download(storage_path('app/temp/' . $filename))->deleteFileAfterSend(true);
    }

    /**
     * Show import form
     */
    public function importForm(Request $request)
    {
        $mataPelajaranId = $request->query('mata_pelajaran_id');
        $tingkat = $request->query('tingkat');

        $guru = auth()->user()->guru;
        $mataPelajaran = MataPelajaran::find($mataPelajaranId);
        
        $kelas = \App\Models\JadwalKbm::where('guru_id', $guru->id)
            ->where('mata_pelajaran_id', $mataPelajaranId)
            ->with('kelas')
            ->get()
            ->pluck('kelas')
            ->filter(function($k) use ($tingkat) {
                return $k->tingkat_kelas == $tingkat;
            })
            ->unique('id')
            ->sortBy('nama_kelas');

        return view('rencana_pembelajaran.import', [
            'mataPelajaran' => $mataPelajaran,
            'tingkat' => $tingkat,
            'kelas' => $kelas,
        ]);
    }

    /**
     * Process import from Word document
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:docx|max:5120', // 5MB max
            'kelas_ids' => 'required|array|min:1',
            'kelas_ids.*' => 'exists:kelas,id',
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
        ]);

        try {
            $file = $request->file('file');
            $tmpFile = $file->store('temp');
            $filePath = storage_path('app/' . $tmpFile);

            // Read Word document
            $phpWord = \PhpOffice\PhpWord\IOFactory::load($filePath);
            
            // Extract text from document
            $extractedText = '';
            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $extractedText .= $element->getText() . "\n";
                    } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                        foreach ($element->getRows() as $row) {
                            foreach ($row->getCells() as $cell) {
                                $extractedText .= $cell->getText() . " | ";
                            }
                            $extractedText .= "\n";
                        }
                    }
                }
            }

            // Parse extracted data - look for key patterns
            $data = $this->parseWordDocument($extractedText);

            if (empty($data['judul'])) {
                return back()->with('error', 'Tidak dapat menemukan judul dalam dokumen Word.');
            }

            // Create rencana pembelajaran for each selected class
            $guru = auth()->user()->guru;
            $mataPelajaranId = $request->input('mata_pelajaran_id');
            $tingkat = Kelas::find($request->input('kelas_ids')[0])->tingkat_kelas;

            foreach ($request->input('kelas_ids') as $kelasId) {
                $rencana = new RencanaPembelajaran();
                $rencana->guru_id = $guru->id;
                $rencana->mata_pelajaran_id = $mataPelajaranId;
                $rencana->kelas_id = $kelasId;
                $rencana->judul = $data['judul'];
                $rencana->deskripsi = $data['deskripsi'] ?? null;
                $rencana->tujuan = $data['tujuan'] ?? null;
                $rencana->metode = $data['metode'] ?? null;
                $rencana->media = $data['media'] ?? null;
                $rencana->sumber = $data['sumber'] ?? null;
                $rencana->penilaian = $data['penilaian'] ?? null;
                $rencana->status = 'draft';
                $rencana->save();
            }

            // Clean up temp file
            \Storage::delete($tmpFile);

            return redirect()
                ->route('rencana_pembelajaran.index', [
                    'mata_pelajaran_id' => $mataPelajaranId,
                    'tingkat' => $tingkat,
                ])
                ->with('success', 'Rencana pembelajaran berhasil diimport untuk ' . count($request->input('kelas_ids')) . ' kelas.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error saat membaca dokumen: ' . $e->getMessage());
        }
    }

    /**
     * Parse Word document content and extract data
     */
    private function parseWordDocument($text)
    {
        $data = [];

        // Extract judul (first non-empty line after title)
        $lines = array_filter(array_map('trim', explode("\n", $text)));
        foreach ($lines as $line) {
            if (strlen($line) > 10 && strpos($line, 'RENCANA PEMBELAJARAN') === false) {
                $data['judul'] = $line;
                break;
            }
        }

        // Extract sections by looking for bold headers
        $sections = ['deskripsi', 'tujuan', 'metode', 'media', 'sumber', 'penilaian'];
        foreach ($sections as $section) {
            $pattern = '/(?:' . ucfirst($section) . '|' . strtoupper($section) . ')[:\s]+([^(?:' . implode('|', array_diff($sections, [$section])) . ')]+)/i';
            if (preg_match($pattern, $text, $matches)) {
                $data[$section] = trim($matches[1]);
            }
        }

        return $data;
    }
}
